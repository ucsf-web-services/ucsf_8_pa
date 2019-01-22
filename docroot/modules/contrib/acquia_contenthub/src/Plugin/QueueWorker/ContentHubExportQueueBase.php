<?php

namespace Drupal\acquia_contenthub\Plugin\QueueWorker;

use Drupal\acquia_contenthub\EntityManager;
use Drupal\acquia_contenthub\Controller\ContentHubEntityExportController;
use Drupal\acquia_contenthub\Controller\ContentHubExportQueueController;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer;
use Drupal\Core\Queue\RequeueException;

/**
 * Provides base functionality for the Content Hub Export Queue.
 */
abstract class ContentHubExportQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  protected $exportQueueController;

  /**
   * The CDF Normalizer.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer
   */
  protected $cdfNormalizer;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManager $entity_manager, ContentHubEntityExportController $acquia_contenthub_export_controller, ContentHubExportQueueController $export_queue_controller, ContentEntityCdfNormalizer $cdf_normalizer, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->entityManager = $entity_manager;
    $this->exportController = $acquia_contenthub_export_controller;
    $this->exportQueueController = $export_queue_controller;
    $this->cdfNormalizer = $cdf_normalizer;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $logger_channel_factory;
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

    /** @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer $cdf_normalizer */
    $cdf_normalizer = \Drupal::service('acquia_contenthub.normalizer.entity.acquia_contenthub_cdf');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entity_type_manager = $container->get('entity_type.manager');

    /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory */
    $logger_channel_factory = $container->get('logger.factory');

    return new static($entity_manager, $acquia_contenthub_export_controller, $export_queue_controller, $cdf_normalizer, $entity_type_manager, $logger_channel_factory);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // An item contains an array of entities to process in a single batch.
    $entities = $item->data;
    $this->entityManager->bulkExport();

    $exported_entities = [];
    $bulk_url_array = [];
    foreach ($entities as $entity) {
      $entity_type = $entity['entity_type'];
      $entity_id = $entity['entity_id'];
      $bulk_url_array[$entity_type][$entity_id] = $entity_id;

      // Obtaining the CDF from the normalizer service.
      $context['query_params']['include_references'] = 'true';
      $drupal_entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
      $exported_entity = $this->cdfNormalizer->normalize($drupal_entity, 'acquia_contenthub_cdf', $context);
      $exported_entity['entities'] = is_array($exported_entity['entities']) ? $exported_entity['entities'] : [];
      foreach ($exported_entity['entities'] as $key => $ch_entity) {
        $exported_entity['entities'][$key] = Json::decode($ch_entity->json());
      }

      $related_entity = $this
        ->entityTypeManager
        ->getStorage($entity_type)
        ->load($entity_id);

      if (!$this->entityManager->isEligibleEntity($related_entity)) {
        $this->loggerFactory
          ->get('acquia_contenthub')
          ->warning('Entity cannot be processed because it is not eligible for export anymore. UUID: @uuid, @backtrack',
          [
            '@uuid' => $related_entity->uuid(),
            '@backtrack' => __FUNCTION__,
          ]);

        continue;
      }

      $exported_entity['entities'] = is_array($exported_entity['entities']) ? $exported_entity['entities'] : [];
      $exported_entities = array_merge($exported_entities, $exported_entity['entities']);
    }

    // Eliminate duplicates.
    $exported_cdfs = [];
    foreach ($exported_entities as $cdf) {
      if (!empty($cdf)) {
        $exported_cdfs[$cdf['uuid']] = $cdf;
      }
    }
    $uuids = array_keys($exported_cdfs);

    // Log list of UUIDs being exported.
    $logger = \Drupal::getContainer()->get('logger.factory');
    $logger->get('acquia_contenthub')->debug('Queue sending export request to Content Hub for UUIDs %uuids.', ['%uuids' => implode(", ", array_keys($exported_cdfs))]);

    // Publish entities.
    if (!empty($exported_cdfs)) {
      if ($this->entityManager->putRemoteEntities(array_values($exported_cdfs))) {
        foreach ($exported_cdfs as $exported_entity) {
          // Obtaining the entity ID from the entity.
          $this->exportController->trackExportedEntity($exported_entity, TRUE);
        }
        return count($exported_cdfs);
      }
      else {
        // Error, cannot put entities to Content Hub.
        $message = $this->t('PUT request to Content Hub failed for these UUIDs: @uuids. Putting it back in the queue for later re-processing.', [
          '@uuids' => implode(', ', $uuids),
        ]);
        \Drupal::logger('acquia_contenthub')->debug($message->render());
        throw new RequeueException($message->render());
      }
    }
    else {
      // Nothing to export.
      $message = $this->t('Could not get the CDF for UUIDs: @uuids. Putting it back in the queue for later re-processing.', [
        '@uuids' => implode(', ', $uuids),
      ]);
      \Drupal::logger('acquia_contenthub')->debug($message->render());
      throw new RequeueException($message->render());
    }
  }

}
