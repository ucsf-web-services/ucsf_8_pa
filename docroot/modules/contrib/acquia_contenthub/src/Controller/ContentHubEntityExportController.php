<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\acquia_contenthub\ContentHubInternalRequest;
use Drupal\Core\Controller\ControllerBase;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\acquia_contenthub\EntityManager;

/**
 * Controller for Content Hub Export Entities using bulk upload.
 */
class ContentHubEntityExportController extends ControllerBase {

  protected $format = 'acquia_contenthub_cdf';

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager
   */
  protected $clientManager;

  /**
   * Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * Content Hub Entities Tracking.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  protected $contentHubEntitiesTracking;

  /**
   * The Entity CDF Normalizer.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer
   */
  protected $entityCdfNormalizer;

  /**
   * Content Hub Export Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  protected $exportQueueController;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The account switcher service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubInternalRequest
   */
  protected $internalRequest;

  /**
   * The flag to check whether the export queue is active or not.
   *
   * @var bool
   */
  protected $exportQueueEnabled;

  /**
   * Public Constructor.
   *
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The Content Hub Entity Manager.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $contenthub_entities_tracking
   *   The table where all entities are tracked.
   * @param \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer $acquia_contenthub_normalizer
   *   The Content Hub Normalizer.
   * @param \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController $export_queue_controller
   *   The Content Hub Export Queue Controller.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\acquia_contenthub\ContentHubInternalRequest $internal_request
   *   The Content Hub Internal Request Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ClientManagerInterface $client_manager, EntityManager $entity_manager, ContentHubEntitiesTracking $contenthub_entities_tracking, ContentEntityCdfNormalizer $acquia_contenthub_normalizer, ContentHubExportQueueController $export_queue_controller, EntityRepositoryInterface $entity_repository, ContentHubInternalRequest $internal_request, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->clientManager = $client_manager;
    $this->entityManager = $entity_manager;
    $this->contentHubEntitiesTracking = $contenthub_entities_tracking;
    $this->entityCdfNormalizer = $acquia_contenthub_normalizer;
    $this->exportQueueController = $export_queue_controller;
    $this->entityRepository = $entity_repository;
    $this->internalRequest = $internal_request;
    $entity_config = $config_factory->get('acquia_contenthub.entity_config');
    $this->exportQueueEnabled = (bool) $entity_config->get('export_with_queue');
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Implements the static interface create method.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acquia_contenthub.client_manager'),
      $container->get('acquia_contenthub.entity_manager'),
      $container->get('acquia_contenthub.acquia_contenthub_entities_tracking'),
      $container->get('acquia_contenthub.normalizer.entity.acquia_contenthub_cdf'),
      $container->get('acquia_contenthub.acquia_contenthub_export_queue'),
      $container->get('entity.repository'),
      $container->get('acquia_contenthub.internal_request'),
      $container->get('config.factory'),
      $container->get('logger.factory')
    );
  }

  /**
   * Export entities to Content Hub (using the queue if enabled).
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $candidate_entities
   *   An array of entities (uuid, entity object) to be exported to Content Hub.
   *
   * @return bool
   *   TRUE if we are using the export queue, FALSE otherwise.
   */
  public function exportEntities(array $candidate_entities) {
    if ($this->exportQueueEnabled) {
      // These entities that reacted to a hook should always be re-exported,
      // then mark them as "queued" in the tracking table so they do not get
      // confused with exported entities (even if a previous version of the
      // entity has been previously exported).
      // This is fine because they will change back to exported status in the
      // tracking table after the queue runs, but we want to make sure that
      // these entities are taken again when collecting dependencies.
      // Dependencies are not exported if they have an entry in the tracking
      // table that says that they have been previously "exported".
      foreach ($candidate_entities as $candidate_entity) {
        $this->queueExportedEntity($candidate_entity);
      }
    }

    // Verify that the collected entities are not already included within the
    // the references of other collected entities.
    $uuids = array_keys($candidate_entities);
    foreach ($candidate_entities as $candidate_entity) {
      // @TODO skip taxonomy terms because their parents will not be added to
      // the queue here and won't get processed in the normalizer (LAT-1345).
      if ($candidate_entity->getEntityTypeId() === 'taxonomy_term') {
        continue;
      }

      // Get referenced entities.
      $referenced_entities = $this->entityCdfNormalizer->getReferencedFields($candidate_entity);

      // If entities are included as references then delete them from the
      // collected entities array. They will be processed as dependencies.
      foreach ($referenced_entities as $referenced_entity) {
        if (in_array($referenced_entity->uuid(), $uuids)) {
          unset($candidate_entities[$referenced_entity->uuid()]);
        }
      }
    }

    // Process the collected entities now that the list has been optimized.
    if ($this->exportQueueEnabled) {
      $this->exportQueueController->enqueueExportEntities($candidate_entities);
      return TRUE;
    }
    else {
      $exported_entities = [];
      $bulk_url_array = [];
      foreach ($candidate_entities as $candidate_entity) {
        $entity_type = $candidate_entity->getEntityTypeId();
        $entity_id = $candidate_entity->id();
        $bulk_url_array[$entity_type][$entity_id] = $entity_id;
        $exported_entity = $this->internalRequest->getEntityCdfByInternalRequest($entity_type, $entity_id);
        $exported_entities = array_merge($exported_entities, $exported_entity['entities']);
      }
      // Eliminate duplicates.
      $exported_cdfs = [];
      foreach ($exported_entities as $cdf) {
        $exported_cdfs[$cdf['uuid']] = $cdf;
      }

      // Now implode parameters.
      foreach ($bulk_url_array as $entity_type => $entities) {
        $bulk_url_array[$entity_type] = implode(',', $entities);
      }
      $resource_url = $this->entityManager->getBulkResourceUrl($bulk_url_array);

      // @TODO This logic should be reviewed later on. We are just saving the
      // exported entities and not using this information to optimize the export
      // yet.
      // Setting up INITIATED status to all tracked exported entities.
      foreach ($exported_cdfs as $exported_entity) {
        // Obtaining the entity ID from the entity.
        $this->trackExportedEntity($exported_entity);
      }
      // @TODO: If we are not able to set export status for entities then we are
      // not exporting entities. Check these lines for media entities.
      if (!empty($exported_cdfs)) {
        $this->entityManager->updateRemoteEntities($resource_url);
      }
      return FALSE;
    }
  }

  /**
   * Collects all Drupal Entities that needs to be sent to Hub.
   */
  public function getDrupalEntities() {
    $normalized = [
      'entities' => [],
    ];
    $normalized_entities = [];
    $request_from_contenthub = $this->isRequestFromAcquiaContentHub();
    $entities = $_GET;
    foreach ($entities as $entity => $entity_ids) {
      $ids = explode(",", $entity_ids);
      foreach ($ids as $id) {
        try {
          $bulk_cdf = $this->internalRequest->getEntityCDFByInternalRequest($entity, $id);
          $bulk_cdf = array_pop($bulk_cdf);
          if (is_array($bulk_cdf)) {
            foreach ($bulk_cdf as $cdf) {
              $normalized_entities[$cdf['uuid']] = $cdf;
            }
          }
        }
        catch (\Exception $e) {
          // Do nothing, route does not exist, but report it..
          $args = [
            '!type' => $entity,
            '!id' => $id,
            '!msg' => $e->getMessage(),
          ];
          $this->loggerFactory->get('acquia_contenthub')->error('Could not obtain the CDF for entity (!type, !id) : !msg', $args);
        }
      }
    }

    // If we reach here, then there was no error processing the sub-requests.
    // Save all entities in the tracking entities and return the response.
    $normalized['entities'] = array_values($normalized_entities);
    if ($request_from_contenthub) {
      foreach ($normalized['entities'] as $cdf) {
        $this->trackExportedEntity($cdf, TRUE);
      }
    }
    return JsonResponse::create($normalized);
  }

  /**
   * Resolves whether the current request comes from Acquia Content Hub or not.
   *
   * @return bool
   *   TRUE if request comes from Content Hub, FALSE otherwise.
   */
  public function isRequestFromAcquiaContentHub() {
    $request = Request::createFromGlobals();

    // This function already sits behind an access check to confirm that the
    // request for CDF came from Content Hub, but just in case that access is
    // opened to authenticated users or during development, we are using a
    // condition to prevent false tracking of entities as exported.
    $headers = array_map('current', $request->headers->all());
    if (isset($headers['user-agent']) && strpos($headers['user-agent'], 'Go-http-client') !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Save this entity in the Tracking table.
   *
   * @param array $cdf
   *   The entity that has to be tracked as exported entity.
   * @param bool $set_exported
   *   Set the export status to exported in the tracking table.
   */
  public function trackExportedEntity(array $cdf, $set_exported = FALSE) {
    if ($exported_entity = $this->contentHubEntitiesTracking->loadExportedByUuid($cdf['uuid'])) {
      $exported_entity->setModified($cdf['modified'])
        ->setInitiated();
    }
    else {
      // Add a new tracking record with exported status set, and
      // imported status empty.
      $entity = $this->entityRepository->loadEntityByUuid($cdf['type'], $cdf['uuid']);
      $exported_entity = $this->contentHubEntitiesTracking->setExportedEntity(
        $cdf['type'],
        $entity->id(),
        $cdf['uuid'],
        $cdf['modified'],
        $this->contentHubEntitiesTracking->getSiteOrigin()
      );
    }

    if ($set_exported) {
      $exported_entity->setExported();
    }

    // Now save the entity.
    $this->contentHubEntitiesTracking->save();
  }

  /**
   * Sets a record for an entity to be queued for export.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that has to be queued for export.
   */
  public function queueExportedEntity(ContentEntityInterface $entity) {
    if ($exported_entity = $this->contentHubEntitiesTracking->loadExportedByUuid($entity->uuid())) {
      $exported_entity->setQueued();
    }
    else {
      // Add a new tracking record with exported status set, and
      // imported status empty.
      $entity = $this->entityRepository->loadEntityByUuid($entity->getEntityTypeId(), $entity->uuid());
      $exported_entity = $this->contentHubEntitiesTracking->setExportedEntity(
        $entity->getEntityTypeId(),
        $entity->id(),
        $entity->uuid(),
      // Assigning current time/date.
        date('c'),
        $this->contentHubEntitiesTracking->getSiteOrigin()
      );
      $exported_entity->setQueued();
    }

    // Now save the entity.
    $this->contentHubEntitiesTracking->save();
  }

  /**
   * Deletes a set of exported entities.
   *
   * @param array $uuids
   *   An array of entities' UUIDs to delete.
   *
   * @return int
   *   The number of rows affected.
   */
  public function deleteExportedEntities(array $uuids) {
    // Prevent deleting all exported entities. Only proceed if a set of
    // entities is given.
    if (!empty($uuids)) {
      return $this->contentHubEntitiesTracking->deleteExportedEntities($uuids);
    }
    return 0;
  }

}
