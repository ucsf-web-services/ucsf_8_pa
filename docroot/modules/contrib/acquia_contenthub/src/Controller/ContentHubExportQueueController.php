<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Implements an Export Queue Controller for Content Hub.
 */
class ContentHubExportQueueController extends ControllerBase {

  /**
   * The Queue Factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The Queue Manager.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManager
   */
  protected $queueManager;

  /**
   * Drupal\Core\Config\ImmutableConfig.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueueFactory $queue_factory, QueueWorkerManagerInterface $queue_manager, ConfigFactoryInterface $config_factory) {
    $this->queueFactory = $queue_factory;
    $this->queueManager = $queue_manager;
    $this->config = $config_factory->get('acquia_contenthub.entity_config');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue_factory = $container->get('queue');

    /** @var \Drupal\Core\Queue\QueueWorkerManager $queue_manager */
    $queue_manager = $container->get('plugin.manager.queue_worker');

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    return new static($queue_factory, $queue_manager, $config_factory);
  }

  /**
   * Adds entities to the export queue.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $candidate_entities
   *   An array of entities (uuid, entity object) to be exported to Content Hub.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   An array of successfully queued entities (uuid, entity object).
   */
  public function enqueueExportEntities(array $candidate_entities) {
    $queued_entities = [];
    // The collected entities are clean now and should all be processed.
    $exported_entities = [];
    foreach ($candidate_entities as $candidate_entity) {
      $entity_type = $candidate_entity->getEntityTypeId();
      $entity_id = $candidate_entity->id();
      $entity_uuid = $candidate_entity->uuid();
      $exported_entities[] = [
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'entity_uuid' => $entity_uuid,
      ];
      $queued_entities[$entity_uuid] = $candidate_entity;
    }
    unset($candidate_entities);

    // Obtain the export queue.
    $queue = $this->queueFactory->get('acquia_contenthub_export_queue');

    // Divide the list of entities into chunks to be processed in groups.
    $entities_per_item = $this->config->get('export_queue_entities_per_item');
    $entities_per_item = $entities_per_item ?: 1;
    $chunks = array_chunk($exported_entities, $entities_per_item);
    foreach ($chunks as $entities_chunk) {
      $uuids = [];
      foreach ($entities_chunk as $entity_chunk) {
        $uuids[] = $entity_chunk['entity_uuid'];
        unset($entity_chunk['entity_uuid']);
      }
      $item = new \stdClass();
      $item->data = $entities_chunk;
      if ($queue->createItem($item) === FALSE) {
        $messages = [];
        foreach ($uuids as $uuid) {
          $message = new TranslatableMarkup('(Type = @type, ID = @id, UUID = @uuid)', [
            '@type' => $queued_entities[$uuid]->getEntityTypeId(),
            '@id' => $queued_entities[$uuid]->id(),
            '@uuid' => $uuid,
          ]);
          $messages[] = $message->jsonSerialize();
          unset($queued_entities[$uuid]);
        }
        \Drupal::logger('acquia_contenthub')->debug('There was an error trying to enqueue the following entities for export: @entities.', [
          '@entities' => implode(', ', $messages),
        ]);
      }
    }
    return $queued_entities;
  }

  /**
   * Obtains the number of items in the export queue.
   *
   * @return mixed
   *   The number of items in the export queue.
   */
  public function getQueueCount() {
    $queue = $this->queueFactory->get('acquia_contenthub_export_queue');
    return $queue->numberOfItems();
  }

  /**
   * Execute the delete function for the ACH Export Queue.
   */
  public function purgeQueue() {
    $queue = $this->queueFactory->get('acquia_contenthub_export_queue');
    $queue->deleteQueue();
  }

  /**
   * Obtains the Queue waiting time in seconds.
   *
   * @return int
   *   Amount in seconds of time to wait before processing an item in the queue.
   */
  public function getWaitingTime() {
    $waiting_time = $this->config->get('export_queue_waiting_time');
    return $waiting_time ?: 3;
  }

  /**
   * Process all queue items with batch API.
   *
   * @param string|int $number_of_items
   *   The number of items to process.
   */
  public function processQueueItems($number_of_items = 'all') {
    // Create batch which collects all the specified queue items and process
    // them one after another.
    $batch = [
      'title' => $this->t("Process Content Hub Export Queue"),
      'operations' => [],
      'finished' => '\Drupal\acquia_contenthub\Controller\ContentHubExportQueueController::batchFinished',
    ];

    // Calculate the number of items to process.
    $queue = $this->queueFactory->get('acquia_contenthub_export_queue');
    $number_of_items = !is_numeric($number_of_items) ? $queue->numberOfItems() : $number_of_items;

    // Count number of the items in this queue, create enough batch operations.
    $batch_size = $this->config->get('export_queue_batch_size');
    $batch_size = $batch_size ?: 1;
    for ($i = 0; $i < ceil($number_of_items / $batch_size); $i++) {
      // Create batch operations.
      $batch['operations'][] = ['\Drupal\acquia_contenthub\Controller\ContentHubExportQueueController::batchProcess', [$number_of_items]];
    }

    // Adds the batch sets.
    batch_set($batch);
  }

  /**
   * Common batch processing callback for all operations.
   *
   * @param int|string $number_of_items
   *   The number of items to process.
   * @param mixed $context
   *   The context array.
   */
  public static function batchProcess($number_of_items, &$context) {
    // Get the queue implementation for acquia_contenthub_export_queue.
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('acquia_contenthub_export_queue');

    $queue_manager = \Drupal::service('plugin.manager.queue_worker');
    $queue_worker = $queue_manager->createInstance('acquia_contenthub_export_queue');

    // Get the number of items.
    $config_factory = \Drupal::service('config.factory');
    $config = $config_factory->get('acquia_contenthub.entity_config');
    $batch_size = $config->get('export_queue_batch_size');
    $batch_size = !empty($batch_size) && is_numeric($batch_size) ? $batch_size : 1;

    $number_of_queue = ($number_of_items < $batch_size) ? $number_of_items : $batch_size;

    // Repeat $number_of_queue times.
    for ($i = 0; $i < $number_of_queue; $i++) {
      // Get a queued item.
      if ($item = $queue->claimItem()) {
        try {
          // Generating a list of entitites.
          $entities = $item->data->data;
          $entities_list = [];
          foreach ($entities as $entity) {
            $entities_list[] = new TranslatableMarkup('(@entity_type, @entity_id)', [
              '@entity_type' => $entity['entity_type'],
              '@entity_id' => $entity['entity_id'],
            ]);
          }

          // Process item.
          try {
            $entities_processed = $queue_worker->processItem($item->data);
          }
          catch (RequeueException $ex) {
            $entities_processed = FALSE;
          }
          if ($entities_processed == FALSE) {
            // Indicate that the item could not be processed.
            if ($entities_processed === FALSE) {
              $message = new TranslatableMarkup('There was an error processing entities: @entities and their dependencies. The item has been sent back to the queue to be processed again later. Check your logs for more info.', [
                '@entities' => implode(',', $entities_list),
              ]);
            }
            else {
              $message = new TranslatableMarkup('No processing was done for entities: @entities and their dependencies. The item has been sent back to the queue to be processed again later. Check your logs for more info.', [
                '@entities' => implode(',', $entities_list),
              ]);
            }
            $context['message'] = SafeMarkup::checkPlain($message->jsonSerialize());
            $context['results'][] = SafeMarkup::checkPlain($message->jsonSerialize());
          }
          else {
            // If everything was correct, delete processed item from the queue.
            $queue->deleteItem($item);

            // Creating a text message to present to the user.
            $message = new TranslatableMarkup('Processed entities: @entities and their dependencies (@count @label sent).', [
              '@entities' => implode(',', $entities_list),
              '@count' => $entities_processed,
              '@label' => $entities_processed == 1 ? new TranslatableMarkup('entity') : new TranslatableMarkup('entities'),
            ]);
            $context['message'] = SafeMarkup::checkPlain($message->jsonSerialize());
            $context['results'][] = SafeMarkup::checkPlain($message->jsonSerialize());
          }

        }
        catch (SuspendQueueException $e) {
          // If there was an Exception thrown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it.
          $queue->releaseItem($item);
          break;
        }
      }
    }
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Whether the batch process succeeded or not.
   * @param array $results
   *   The results array.
   * @param array $operations
   *   An array of operations.
   */
  public static function batchFinished($success, array $results, array $operations) {
    if ($success) {
      drupal_set_message(t("The contents are successfully exported."));
    }
    else {
      $error_operation = reset($operations);
      drupal_set_message(t('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]
      ));
    }

    // Providing a report on the items processed by the queue.
    $elements = [
      '#theme' => 'item_list',
      '#type' => 'ul',
      '#items' => $results,
    ];
    $queue_report = \Drupal::service('renderer')->render($elements);
    drupal_set_message($queue_report);
  }

}
