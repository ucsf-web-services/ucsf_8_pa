<?php

namespace Drupal\acquia_contenthub\Plugin\QueueWorker;

use Drupal\acquia_contenthub\ImportEntityManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Class ContentHubImportQueueBase.
 *
 * @package Drupal\acquia_contenthub\Plugin\QueueWorker
 */
abstract class ContentHubImportQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Import entity manager instance.
   *
   * @var \Drupal\acquia_contenthub\ImportEntityManager
   */
  protected $importEntityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ImportEntityManager $import_entity_manager) {
    $this->importEntityManager = $import_entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('acquia_contenthub.import_entity_manager')
    );
  }

  /**
   * Accessor for the entity manager.
   *
   * @return \Drupal\acquia_contenthub\ImportEntityManager
   *   The Import Entity Manager.
   */
  public function getEntityManager() {
    return $this->importEntityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // When processing via batch the items are not in the same format as what
    // the queue worker provides so we make sure that we can handle both types.
    $item = isset($item->data) ? $item->data : $item;

    /** @var \Drupal\acquia_contenthub\QueueItem\ImportQueueItem $data */
    foreach ($item as $data) {
      $this->getEntityManager()->importRemoteEntity($data->get('uuid'), $data->get('dependencies'), $data->get('author'), $data->get('status'));
    }
  }

}
