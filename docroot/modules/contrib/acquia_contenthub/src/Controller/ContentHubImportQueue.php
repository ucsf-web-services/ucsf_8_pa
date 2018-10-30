<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\SuspendQueueException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements an Import Queue for entities.
 *
 * @package Drupal\acquia_contenthub\Controller
 */
class ContentHubImportQueue extends ControllerBase {

  /**
   * Queue Factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Config Factory Interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, QueueFactory $queue_factory) {
    $this->config = $config_factory;
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('queue')
    );
  }

  /**
   * Handle the route to create a batch process.
   */
  public function process() {
    $config = $this->config('acquia_contenthub.entity_config');
    $queue = $this->queueFactory->get('acquia_contenthub_import_queue');

    if ($queue->numberOfItems() < 1) {
      drupal_set_message('No items to process.');
      $this->redirect('acquia_contenthub.import_queue');
    }

    $batch = [
      'title' => $this->t('Process all remaining entities'),
      'operations' => [],
      'finished' => [self::class, 'batchFinished'],
    ];

    // Define a default value for batch_size in case it is not defined.
    $batch_size = $config->get('import_queue_batch_size');
    $batch_size = !empty($batch_size) && is_numeric($batch_size) ? $batch_size : 1;

    // Batch operations.
    for ($i = 0; ceil($i < $queue->numberOfItems() / $batch_size); $i++) {
      $batch['operations'][] = [[self::class, 'batchProcess'], [$config]];
    }

    batch_set($batch);
    return batch_process('/admin/config/services/acquia-contenthub/import-queue');
  }

  /**
   * Process the batch.
   *
   * The batch worker will run through the queued items and process them
   * according to their queue method.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The import configuration.
   * @param mixed $context
   *   The batch context.
   */
  public static function batchProcess(ImmutableConfig $config, &$context) {
    /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');
    /** @var \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager */
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    $queue = $queue_factory->get('acquia_contenthub_import_queue');
    $worker = $queue_manager->createInstance('acquia_contenthub_import_queue');
    $batch_size = $queue->numberOfItems() < $config->get('import_queue_batch_size') ? $queue->numberOfItems() : $config->get('import_queue_batch_size');

    for ($i = 0; $i < $batch_size; $i++) {
      if ($item = $queue->claimItem()) {
        try {
          $worker->processItem($item->data);
          $queue->deleteItem($item);
        }
        catch (SuspendQueueException $exception) {
          $context['errors'][] = $exception->getMessage();
          $context['success'] = FALSE;
          $queue->releaseItem($item);
          break;
        }
        catch (EntityStorageException $exception) {
          $context['errors'][] = $exception->getMessage();
          $context['success'] = FALSE;
          $queue->releaseItem($item);
          break;
        }
      }
    }
  }

  /**
   * Batch finish callback.
   *
   * This will inspect the results of the batch and will display a message to
   * indicate how the batch process ended.
   *
   * @param bool $success
   *   The result of batch process.
   * @param array $result
   *   The result of $context.
   * @param array $operations
   *   The operations that were run.
   */
  public static function batchFinished($success, array $result, array $operations) {
    if ($success) {
      drupal_set_message(t("Processed all Content Hub entities."));
      return;
    }
    $error_operation = reset($operations);
    drupal_set_message(t('An error occurred while processing @operation with arguments : @args', [
      '@operation' => $error_operation[0],
      '@args' => print_r($error_operation[0], TRUE),
    ]));
  }

}
