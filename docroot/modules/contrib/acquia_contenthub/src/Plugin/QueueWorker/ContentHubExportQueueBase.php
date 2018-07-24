<?php

namespace Drupal\acquia_contenthub\Plugin\QueueWorker;

use Drupal\acquia_contenthub\EntityManager;
use Drupal\acquia_contenthub\Controller\ContentHubEntityExportController;
use Drupal\acquia_contenthub\Controller\ContentHubExportQueueController;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\ContentHubInternalRequest;

/**
 * Provides base functionality for the Content Hub Export Queue.
 */
abstract class ContentHubExportQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {


  /**
   * Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * Content Hub Entity Export Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubEntityExportController
   */
  protected $exportController;

  /**
   * Content Hub Export Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Plugin\QueueWorker\ContentHubExportQueueController
   */
  protected $exportQueueController;

  /**
   * The account switcher service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubInternalRequest
   */
  protected $internalRequest;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManager $entity_manager, ContentHubEntityExportController $acquia_contenthub_export_controller, ContentHubExportQueueController $export_queue_controller, ContentHubInternalRequest $internal_request) {
    $this->entityManager = $entity_manager;
    $this->exportController = $acquia_contenthub_export_controller;
    $this->exportQueueController = $export_queue_controller;
    $this->internalRequest = $internal_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\acquia_contenthub\EntityManager $entity_manager */
    $entity_manager = $container->get('acquia_contenthub.entity_manager');

    /** @var \Drupal\acquia_contenthub\Controller\ContentHubEntityExportController $acquia_contenthub_export_controller */
    $acquia_contenthub_export_controller = $container->get('acquia_contenthub.acquia_contenthub_export_entities');

    /** @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController $export_queue_controller */
    $export_queue_controller = $container->get('acquia_contenthub.acquia_contenthub_export_queue');

    /** @var \Drupal\acquia_contenthub\ContentHubInternalRequest $internal_request */
    $internal_request = $container->get('acquia_contenthub.internal_request');

    return new static($entity_manager, $acquia_contenthub_export_controller, $export_queue_controller, $internal_request);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // Wait a certain amount of seconds before proceeding.
    $waiting_time = $this->exportQueueController->getWaitingTime();
    sleep($waiting_time);

    // An item contains an array of entities to process in a single batch.
    $entities = $item->data;
    $this->entityManager->bulkExport();

    $exported_entities = [];
    $bulk_url_array = [];
    foreach ($entities as $entity) {
      $entity_type = $entity['entity_type'];
      $entity_id = $entity['entity_id'];
      $bulk_url_array[$entity_type][$entity_id] = $entity_id;
      $exported_entity = $this->internalRequest->getEntityCdfByInternalRequest($entity_type, $entity_id);
      $exported_entity['entities'] = is_array($exported_entity['entities']) ? $exported_entity['entities'] : [];
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

    foreach ($exported_cdfs as $exported_entity) {
      // Obtaining the entity ID from the entity.
      $this->exportController->trackExportedEntity($exported_entity);
    }

    // @TODO: If we are not able to set export status for entities then we are
    // not exporting entities. Check these lines for media entities.
    if (!empty($exported_cdfs)) {
      $result = $this->entityManager->updateRemoteEntities($resource_url);
      return ($result === FALSE) ? FALSE : count($exported_cdfs);
    }
    return 0;
  }

}
