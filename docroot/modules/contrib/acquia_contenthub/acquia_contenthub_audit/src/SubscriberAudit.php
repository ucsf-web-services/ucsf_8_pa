<?php

namespace Drupal\acquia_contenthub_audit;

use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Drupal\acquia_contenthub\ImportEntityManager;
use Drupal\acquia_contenthub_audit\Event\AuditPreEntityDeleteEvent;
use Drupal\acquia_contenthub_subscriber\SubscriberCommon;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Audits Subscribers.
 *
 * @package Drupal\acquia_contenthub_audit
 */
class SubscriberAudit {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The Database Connection Service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The common subscriber actions.
   *
   * @var \Drupal\acquia_contenthub_subscriber\SubscriberCommon
   */
  protected $common;

  /**
   * The Content Hub Client.
   *
   * @var \Acquia\ContentHubClient\ContentHub
   */
  protected $client;

  /**
   * The Content Hub Filters.
   *
   * @var \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface[]
   */
  protected $filters;

  /**
   * The Import Entity Manager Service.
   *
   * @var \Drupal\acquia_contenthub\ImportEntityManager
   */
  protected $importEntityManager;

  /**
   * The Content Hub Entities Tracking.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  protected $contentHubEntitiesTracking;

  /**
   * Public constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The Entity Repository.
   * @param \Drupal\Core\Database\Connection $database
   *   The Database Connection Service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The Event Dispatcher.
   * @param \Drupal\acquia_contenthub_subscriber\SubscriberCommon $common
   *   The SubscriberCommon Service.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The Client Manager Service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\acquia_contenthub\ImportEntityManager $import_entity_manager
   *   The Import Entity Manager.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $contenthub_entities_tracking
   *   The Content Hub Entities Tracking Service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityRepositoryInterface $entity_repository, Connection $database, EventDispatcherInterface $dispatcher, SubscriberCommon $common, ClientManagerInterface $client_manager, EntityTypeManagerInterface $entity_type_manager, ImportEntityManager $import_entity_manager, ContentHubEntitiesTracking $contenthub_entities_tracking) {
    $this->configFactory = $config_factory;
    $this->entityRepository = $entity_repository;
    $this->database = $database;
    $this->dispatcher = $dispatcher;
    $this->common = $common;
    $this->client = $client_manager->getConnection([]);
    $this->filters = $entity_type_manager->getStorage('contenthub_filter')->loadMultiple();
    $this->importEntityManager = $import_entity_manager;
    $this->contentHubEntitiesTracking = $contenthub_entities_tracking;
  }

  /**
   * Finds entities to import.
   */
  public function findEntitiesToImport() {
    $entities = $this->common->executeAllFilters();
    $map = $this->common->getFilterMap();
    unset($entities['total']);
    $new_entities = [];
    if ($entities) {
      $origin = $this->configFactory->get('acquia_contenthub.admin_settings')->get('origin');
      $uuids = array_keys($entities);
      $query = $this->database
        ->select('acquia_contenthub_entities_tracking', 't')
        ->fields('t');
      $query->condition('t.entity_uuid', $uuids, 'IN');
      $query->condition('t.status_import', ContentHubEntitiesTracking::IS_DEPENDENT, '<>');
      $query->condition('t.status_import', ContentHubEntitiesTracking::HAS_LOCAL_CHANGE, '<>');
      $query->condition('t.status_import', '', '<>');
      $query->condition('t.origin', $origin, '<>');
      $results = $query->execute();
      $existing_uuids = [];
      foreach ($results as $item) {
        $existing_uuids[$item->entity_uuid] = $item;
      }
      $new_uuids = array_diff($uuids, array_keys($existing_uuids));
      if ($new_uuids) {
        foreach ($new_uuids as $uuid) {
          $new_entities[$uuid] = [
            'entity' => $entities[$uuid],
            'filter' => $map[$uuid],
          ];
        }
      }
    }
    return $new_entities;
  }

  /**
   * Find entities to delete.
   *
   * @param array $entity_type_ids
   *   An array of entity type ids we want to search and delete.
   *
   * @return array
   *   A double array keyed by 'delete' and 'missing' entities.
   */
  public function findEntitiesToDelete(array $entity_type_ids = []) {
    $entities = $this->common->executeAllFilters();
    unset($entities['total']);
    $uuids = array_keys($entities);

    $query = $this->database
      ->select('acquia_contenthub_entities_tracking', 't')
      ->fields('t');
    if (!empty($uuids)) {
      $query->condition('t.entity_uuid', $uuids, 'NOT IN');
    }
    $query->condition('t.status_import', ContentHubEntitiesTracking::IS_DEPENDENT, '<>');
    $query->condition('t.status_import', '', '<>');
    // Make the entity types configurable.
    if (!empty($entity_type_ids)) {
      $query->condition('t.entity_type', $entity_type_ids, 'IN');
    }
    $results = $query->execute();

    // Build a list of uuids which filters did not match.
    $unmatched_uuids = [];
    foreach ($results as $item) {
      $unmatched_uuids[$item->entity_uuid] = $item;
    }
    // Retrieve our list of missing items to see if any are in the hub.
    $entities_in_hub = $this->client->readEntities(array_keys($unmatched_uuids));
    // Build the list of things missing from ContentHub.
    $missing_uuids = array_diff_key($unmatched_uuids, $entities_in_hub);
    $unmatched_uuids = array_diff_key($unmatched_uuids, $missing_uuids);
    return [
      'delete' => $unmatched_uuids,
      'missing' => $missing_uuids,
    ];
  }

  /**
   * Generate Manifest file.
   *
   * @param string $file_path
   *   The path to Manifest file.
   * @param array $entity_type_ids
   *   An array of entity type ids we want to search and delete.
   */
  public function generateManifest(string $file_path, array $entity_type_ids = []) {
    $entities = $this->findEntitiesToImport();
    $origin = $this->configFactory->get('acquia_contenthub.admin_settings')->get('origin');
    if (file_exists($file_path)) {
      $manifest = include $file_path;
    }
    else {
      $manifest = [];
    }
    if ($entities) {
      foreach ($entities as $uuid => $entity) {
        if ($origin != $entity['entity']->getOrigin()) {
          $manifest[$origin]['import'][$entity['entity']->getOrigin()][$uuid] = [
            'type' => $entity['entity']->getType(),
            'filter' => $entity['filter'],
          ];
        }
      }
    }
    $entities = $this->findEntitiesToDelete($entity_type_ids);
    foreach ($entities['delete'] as $uuid => $entity) {
      $local_entity = $this->entityRepository->loadEntityByUuid($entity->entity_type, $uuid);
      if ($local_entity) {
        $manifest[$origin]['delete'][$entity->entity_type][$uuid] = [
          'id' => $local_entity->id(),
          'label' => $local_entity->label(),
          'url' => $local_entity->url('canonical', ['absolute' => TRUE]),
        ];
      }
      else {
        $manifest[$origin]['delete_tracking'][$entity->entity_type][$uuid] = [
          'type' => $entity->entity_type,
          'id' => $entity->entity_id,
        ];
      }
    }
    foreach ($entities['missing'] as $uuid => $item) {
      $manifest[$origin]['missing'][$item->origin][$item->entity_type][$item->entity_uuid] = [
        'type' => $item->entity_type,
        'id' => $item->entity_id,
      ];
    }
    if (file_put_contents($file_path, "<?php\n\nreturn " . var_export($manifest, TRUE) . ";")) {
      print sprintf("The manifest file was successfully written to %s.\n", $file_path);
    }
  }

  /**
   * Executes a Manifest file.
   *
   * @param string $file_path
   *   The path to Manifest file.
   * @param string $output_file_path
   *   Resulting Manifest file after execution.
   *
   * @throws \Exception
   */
  public function executeManifest(string $file_path, string $output_file_path) {
    $manifest = include $file_path;
    $origin = $this->configFactory->get('acquia_contenthub.admin_settings')->get('origin');

    if (empty($manifest[$origin])) {
      print sprintf("There are no entities found in the manifest for this site's origin: %s\n", $origin);
      return;
    }

    // Missing entities.
    if (!empty($manifest[$origin]['missing'])) {
      print sprintf("There are entities found in the site that do not exist in Content Hub. Review those entities and delete them from the Manifest file before proceeding. Below a list of those entities:\n");
      foreach ($manifest[$origin]['missing'] as $entity_origin => $entities_list) {
        foreach ($entities_list as $type => $entities) {
          foreach ($entities as $uuid => $entity) {
            print sprintf("- [%s, %s, %s] origin: %s\n", $entity['type'], $entity['id'], $uuid, $entity_origin);
          }
        }
      }
      return;
    }

    // Deleting entities.
    $deleted_entities = 0;
    if (is_array($manifest[$origin]['delete'])) {
      foreach ($manifest[$origin]['delete'] as $type => $entities) {
        foreach ($entities as $uuid => $entity) {
          $this->deleteEntity($uuid, $type);
          $deleted_entities++;
          unset($manifest[$origin]['delete'][$type][$uuid]);
        }
        if (empty($manifest[$origin]['delete'][$type])) {
          unset($manifest[$origin]['delete'][$type]);
        }
      }
      if (empty($manifest[$origin]['delete'])) {
        unset($manifest[$origin]['delete']);
      }
    }

    // Deleting tracking records.
    $deleted_tracking_record = 0;
    if (is_array($manifest[$origin]['delete_tracking'])) {
      foreach ($manifest[$origin]['delete_tracking'] as $type => $entities) {
        foreach ($entities as $uuid => $entity) {
          $this->deleteTrackingEntity($uuid, $type);
          $deleted_tracking_record++;
          unset($manifest[$origin]['delete_tracking'][$type][$uuid]);
        }
        if (empty($manifest[$origin]['delete_tracking'][$type])) {
          unset($manifest[$origin]['delete_tracking'][$type]);
        }
      }
      if (empty($manifest[$origin]['delete_tracking'])) {
        unset($manifest[$origin]['delete_tracking']);
      }
    }

    // Importing entities.
    $imported_entities = 0;
    foreach ($manifest[$origin]['import'] as $entity_origin => $entities) {
      foreach ($entities as $uuid => $entity) {
        if ($this->importEntity($uuid, $entity['filter'])) {
          $imported_entities++;
          unset($manifest[$origin]['import'][$entity_origin][$uuid]);
        }
      }
      if (empty($manifest[$origin]['import'][$entity_origin])) {
        unset($manifest[$origin]['import'][$entity_origin]);
      }
    }
    if (empty($manifest[$origin]['import'])) {
      unset($manifest[$origin]['import']);
    }
    if (empty($manifest[$origin])) {
      unset($manifest[$origin]);
    }
    if (file_put_contents($output_file_path, "<?php\n\nreturn " . var_export($manifest, TRUE) . ";")) {
      print sprintf("The resulting manifest file after execution was successfully written to %s.\n", $output_file_path);
      print sprintf("Deleted Entities: %s\n", $deleted_entities);
      print sprintf("Deleted Wrongly Tracking Records: %s\n", $deleted_tracking_record);
      print sprintf("Imported Entities: %s\n", $imported_entities);
    }
  }

  /**
   * Import Entities.
   */
  public function importEntities() {
    $entities = $this->findEntitiesToImport();
    foreach ($entities as $uuid => $entity) {
      $this->importEntity($uuid, $entity['filter']);
    }
  }

  /**
   * Imports a single entity.
   *
   * @param string $uuid
   *   The Entity UUID.
   * @param string $filter_id
   *   The Filter ID.
   *
   * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
   *   The json response or FALSE.
   *
   * @throws \Exception
   */
  protected function importEntity($uuid, $filter_id) {
    if (empty($this->filters[$filter_id])) {
      print sprintf("There was no filter provided to import entity with UUID = %s .\n", $uuid);
      return FALSE;
    }
    $contenthub_filter = $this->filters[$filter_id];
    // Determine the author UUID for the nodes to be created.
    // Assign the appropriate author for this filter (User UUID).
    $uid = $contenthub_filter->author;
    $user = User::load($uid);
    // Get the Status from the Filter Information.
    $status = $contenthub_filter->getPublishStatus();
    // Re-importing or re-queuing entities matching the filter that were not
    // previously imported.
    return $this->importEntityManager->import($uuid, TRUE, $user->uuid(), $status ? $status : NULL);
  }

  /**
   * Delete Entities.
   *
   * @param array $entity_type_ids
   *   An array of entity type ids we want to search and delete.
   */
  public function deleteEntities(array $entity_type_ids = []) {
    $entities = $this->findEntitiesToDelete($entity_type_ids);
    foreach ($entities['delete'] as $uuid => $entity) {
      $this->deleteEntity($uuid, $entity->entity_type);
    }
  }

  /**
   * Deletes a single entity.
   *
   * @param string $uuid
   *   The entity UUID.
   * @param string $entity_type
   *   The entity type.
   */
  protected function deleteEntity($uuid, $entity_type) {
    $local_entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
    if ($local_entity) {
      $event = new AuditPreEntityDeleteEvent($local_entity);
      $this->dispatcher->dispatch(AcquiaContentHubAuditEvents::PRE_ENTITY_DELETE, $event);
      $local_entity->delete();
    }
    else {
      $this->deleteTrackingEntity($uuid, $entity_type);
    }
  }

  /**
   * Deletes an entity from the tracking table.
   *
   * @param string $uuid
   *   The entity UUID.
   * @param string $entity_type
   *   The entity type.
   */
  protected function deleteTrackingEntity($uuid, $entity_type) {
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity_type, $uuid);
    if (!$imported_entity) {
      return;
    }
    $imported_entity->delete();
  }

}
