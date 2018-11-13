<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentHubReindex.
 *
 * @package Drupal\acquia_contenthub\Controller
 */
class ContentHubReindex extends ControllerBase {

  const REINDEXING_STATE = 'acquia_contenthub.reindexing_state';
  const REINDEX_NONE     = 'reindex_none';
  const REINDEX_SENT     = 'reindex_sent';
  const REINDEX_FAILED   = 'reindex_failed';
  const REINDEX_FINISHED = 'reindex_finished';

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager
   */
  private $clientManager;

  /**
   * The Content Hub Entities Tracking Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  private $contentHubEntitiesTracking;

  /**
   * The Reindex State.
   *
   * @var string
   */
  protected $reindexState;

  /**
   * Public Constructor.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $contenthub_entities_tracking
   *   The table where all entities are tracked.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   */
  public function __construct(ContentHubEntitiesTracking $contenthub_entities_tracking, ClientManagerInterface $client_manager) {
    $this->clientManager = $client_manager;
    $this->contentHubEntitiesTracking = $contenthub_entities_tracking;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acquia_contenthub.acquia_contenthub_entities_tracking'),
      $container->get('acquia_contenthub.client_manager')
    );
  }

  /**
   * Gets the Reindexing State from the State Variable.
   *
   * @return string
   *   The Reindexing State.
   */
  private function getReindexingState() {
    $this->reindexState = \Drupal::state()->get(self::REINDEXING_STATE, self::REINDEX_NONE);
    return $this->reindexState;
  }

  /**
   * Sets a new Reindexing State.
   *
   * @param string $new_state
   *   The new Reindexing State.
   *
   * @return string
   *   The new state, if it was successfully set, the previous state otherwise.
   */
  private function setReindexingState($new_state) {
    if (in_array($new_state, [
      self::REINDEX_NONE,
      self::REINDEX_SENT,
      self::REINDEX_FAILED,
      self::REINDEX_FINISHED,
    ])) {
      $this->reindexState = $new_state;
      \Drupal::state()
        ->set(self::REINDEXING_STATE, $this->reindexState);
    }
    return $this->reindexState;
  }

  /**
   * Sets the Reindexing State as REINDEX_NONE.
   *
   * @return string
   *   The new state.
   */
  public function setReindexStateNone() {
    return $this->setReindexingState(self::REINDEX_NONE);
  }

  /**
   * Checks whether the current Reindexing State is REINDEX_NONE.
   *
   * @return bool
   *   TRUE if Reindexing State is REINDEX_NONE, FALSE otherwise.
   */
  public function isReindexNone() {
    return $this->getReindexingState() === self::REINDEX_NONE;
  }

  /**
   * Checks whether the current Reindexing State is REINDEX_SENT.
   *
   * @return bool
   *   TRUE if Reindexing State is REINDEX_SENT, FALSE otherwise.
   */
  public function isReindexSent() {
    return $this->getReindexingState() === self::REINDEX_SENT;
  }

  /**
   * Sets the Reindexing State as REINDEX_SENT.
   *
   * @return string
   *   The new state.
   */
  public function setReindexStateSent() {
    return $this->setReindexingState(self::REINDEX_SENT);
  }

  /**
   * Checks whether the current Reindexing State is REINDEX_FAILED.
   *
   * @return bool
   *   TRUE if Reindexing State is REINDEX_FAILED, FALSE otherwise.
   */
  public function isReindexFailed() {
    return $this->getReindexingState() === self::REINDEX_FAILED;
  }

  /**
   * Sets the Reindexing State as REINDEX_FAILED.
   *
   * @return string
   *   The new state.
   */
  public function setReindexStateFailed() {
    return $this->setReindexingState(self::REINDEX_FAILED);
  }

  /**
   * Checks whether the current Reindexing State is REINDEX_FINISHED.
   *
   * @return bool
   *   TRUE if Reindexing State is REINDEX_FINISHED, FALSE otherwise.
   */
  public function isReindexFinished() {
    return $this->getReindexingState() === self::REINDEX_FINISHED;
  }

  /**
   * Sets the Reindexing State as REINDEX_FINISHED.
   *
   * @return string
   *   The new state.
   */
  public function setReindexStateFinished() {
    return $this->setReindexingState(self::REINDEX_FINISHED);
  }

  /**
   * Set Exported Entities to be Re-indexed.
   *
   * @param string $entity_type_id
   *   The Entity type.
   * @param string $bundle_id
   *   The Entity bundle.
   *
   * @return bool
   *   TRUE if subscription can be re-indexed, FALSE otherwise.
   */
  public function setExportedEntitiesToReindex($entity_type_id = NULL, $bundle_id = NULL) {
    // Set exported entities with the REINDEX flag.
    $this->contentHubEntitiesTracking->setExportedEntitiesForReindex($entity_type_id, $bundle_id);

    // Collect all entities that were flagged for REINDEX.
    $entities = $this->contentHubEntitiesTracking->getEntitiesToReindex();
    if (count($entities) == 0) {
      $this->setReindexStateNone();
      return FALSE;
    }

    // Delete all entities set to reindex from Content Hub.
    foreach ($entities as $entity) {
      $this->clientManager->createRequest('deleteEntity', [$entity->entity_uuid]);
    }

    // We have a sent a lot of delete requests to Content Hub, wait 10 seconds
    // before proceeding to reindex the subscription.
    sleep(10);

    // Now reindex subscription.
    if ($response = $this->clientManager->createRequest('reindex')) {
      if (isset($response['success']) && $response['success'] === TRUE) {
        // Saving Reindexing State.
        $this->setReindexStateSent();
        return TRUE;
      }
    }

    // The reindex request has failed, then set the reindex state as failed.
    $this->setReindexStateFailed();
    return FALSE;
  }

  /**
   * Obtains number of entities to re-export.
   *
   * @return int
   *   Number of entities to re-export.
   */
  public function getCountReExportEntities() {
    return $this->contentHubEntitiesTracking->getCountEntitiesToReindex();
  }

  /**
   * Obtains the entities to re-export.
   *
   * @param int $offset
   *   Offset from the list.
   * @param int $limit
   *   Length of entities to take.
   *
   * @return array
   *   An array of entities to re-export.
   */
  public function getReExportEntities($offset = 0, $limit = 10) {
    $entities = $this->contentHubEntitiesTracking->getEntitiesToReindex();
    return array_slice($entities, $offset, $limit);
  }

  /**
   * Obtains a list of entities exported to Content Hub not owned by this site.
   *
   * For the specific entity type, it queries Content Hub and checks if the
   * previously exported entities belong to this exporting site origin or they
   * have been exported by other sites.
   *
   * Limitation: This check uses the "listEntities" method from ContentHubClient
   * without using pagination, so it will only check for the first 1000 entities
   * returned by this list. It will need to be improved at some point to deal
   * with pagination.
   *
   * @param string|null $entity_type_id
   *   The entity type ID.
   *
   * @return array
   *   An array of exported entities not owned by this site (different origin).
   */
  public function getExportedEntitiesNotOwnedByThisSite($entity_type_id = NULL) {
    $external_ownership = [];
    $options = empty($entity_type_id) ? [] : [
      'type' => $entity_type_id,
    ];
    $list = $this->clientManager->createRequest('listEntities', [$options]);
    if (isset($list['success']) && $list['success'] && isset($list['data']) && is_array($list['data'])) {
      $origin = $this->contentHubEntitiesTracking->getSiteOrigin();
      foreach ($list['data'] as $entity) {
        if ($entity['origin'] !== $origin) {
          $external_ownership[] = $entity;
        }
      }
    }
    return $external_ownership;
  }

  /**
   * Re-export Entities.
   *
   * @param int $limit
   *   The number of entities to re-export in a single batch process.
   * @param mixed $context
   *   The context array.
   */
  public static function reExportEntities($limit, &$context) {
    // Sleep for 3 seconds before start processing.
    sleep(3);
    /** @var \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex */
    $reindex = \Drupal::service('acquia_contenthub.acquia_contenthub_reindex');
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $reindex->getCountReExportEntities();
    }

    /** @var \Drupal\acquia_contenthub\EntityManager $entity_manager */
    $entity_manager = \Drupal::service('acquia_contenthub.entity_manager');
    $entities = $reindex->getReExportEntities(0, $limit);
    $messages = [];
    foreach ($entities as $entity_item) {
      $entity_type = $entity_item->entity_type;
      $entity_id = $entity_item->entity_id;
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
      $entity_manager->enqueueCandidateEntity($entity);

      // Storing Results and.
      $context['results'][] = $entity_id;
      $context['sandbox']['progress']++;
      $messages[] = $reindex->t('(type = @type, id = @id, uuid = @uuid)', [
        '@type' => $entity_type,
        '@id' => $entity_id,
        '@uuid' => $entity_item->entity_uuid,
      ]);
    }
    $context['message'] = $reindex->t('Exporting entities: @entities', [
      '@entities' => implode(',', $messages),
    ]);
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Implements Batch API to re-export entities after re-indexing.
   *
   * @param int $batch_size
   *   The batch size or number of entities processed in a single request.
   */
  public function reExportEntitiesAfterReindex($batch_size = 10) {
    $batch = [
      'title' => $this->t("Process Content Hub Export Queue"),
      'file' => drupal_get_path('module', 'acquia_contenthub') . '/acquia_contenthub.drush.inc',
      'operations' => [
        ['\Drupal\acquia_contenthub\Controller\ContentHubReindex::reExportEntities', [$batch_size]],
      ],
      'finished' => 'acquia_contenthub_reexport_finished',
    ];
    batch_set($batch);
  }

}
