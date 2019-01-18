<?php

namespace Drupal\acquia_contenthub;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Query\Merge;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Database\IntegrityConstraintViolationException;

/**
 * Tracks in a table the list of all entities imported from Content Hub.
 */
class ContentHubEntitiesTracking {

  const TABLE = 'acquia_contenthub_entities_tracking';

  // Internal constants, should not be used outside of this class.
  // Typical import status flows are:
  // 0) (status X)     -> (same status X).
  // 1) enabled       <-> disabled.
  // 2) enabled        -> local change.
  // 3) disabled       -> local change.
  // 4) local change   -> pending sync     -> enabled.
  // 5) disabled       -> pending sync     -> enabled.
  // 6) is dependent  <x> (i.e. status is immutable).
  const AUTO_UPDATE_ENABLED  = 'AUTO_UPDATE_ENABLED';
  const AUTO_UPDATE_DISABLED = 'AUTO_UPDATE_DISABLED';
  const PENDING_SYNC         = 'PENDING_SYNC';
  const HAS_LOCAL_CHANGE     = 'HAS_LOCAL_CHANGE';
  const IS_DEPENDENT         = 'IS_DEPENDENT';

  // 0) queued -> initiated.
  // 1) initiated -> exported.
  const QUEUED    = 'QUEUED';
  const INITIATED = 'INITIATED';
  const EXPORTED  = 'EXPORTED';
  const REINDEX   = 'REINDEX';

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The specific content hub keys.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $contentHubAdminConfig;

  /**
   * The Tracking Entity Record.
   *
   * @var object
   */
  protected $trackingEntity;

  /**
   * The list of locally-cached entities.
   *
   * @var array
   */
  protected $cachedTrackingEntities = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('config.factory')
    );
  }

  /**
   * TableSortExampleController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
    $this->contentHubAdminConfig = $config_factory->get('acquia_contenthub.admin_settings');
  }

  /**
   * Explicitly sets the Tracking Entity.
   *
   * @param string $entity_type
   *   The Entity Type.
   * @param int $entity_id
   *   The Entity ID.
   * @param string $entity_uuid
   *   The Entity UUID.
   * @param string $modified
   *   The CDF's modified timestamp.
   * @param string $origin
   *   The origin UUID.
   * @param string $status_export
   *   The Export Status.
   * @param string $status_import
   *   The Import Status.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This same object.
   */
  protected function setTrackingEntity($entity_type, $entity_id, $entity_uuid, $modified, $origin, $status_export, $status_import) {
    // If we don't have a valid input, return FALSE.
    $valid_input = !empty($entity_type) && !empty($entity_id) && Uuid::isValid($entity_uuid) && Uuid::isValid($origin);
    if (!$valid_input) {
      return FALSE;
    }

    // Either export status or import status has to exist, but not both or
    // neither. Otherwise return FALSE.
    $export_xor_import = empty($status_export) && !empty($status_import) || !empty($status_export) && empty($status_import);
    if (!$export_xor_import) {
      return FALSE;
    }

    // If we have a valid import status but site origin is the same as the
    // entity origin then return FALSE.
    // If we have a valid export status but site origin is not the same as the
    // entity origin then return FALSE.
    $site_origin = $this->contentHubAdminConfig->get('origin');
    if ($this->isImportedEntity() && $this->getOrigin() === $site_origin) {
      return FALSE;
    }
    if ($this->isExportedEntity() && $this->getOrigin() !== $site_origin) {
      return FALSE;
    }

    // Set the current tracking entity.
    $this->trackingEntity = (object) [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'entity_uuid' => $entity_uuid,
      'modified' => $modified,
      'origin' => $origin,
      'status_export' => $status_export,
      'status_import' => $status_import,
    ];

    // Cache the entity object.
    $this->cachedTrackingEntities[$entity_type][$entity_id] = $this->trackingEntity;

    return $this;
  }

  /**
   * Helper function to set the Exported Tracking Entity.
   *
   * @param string $entity_type
   *   The Entity Type.
   * @param int $entity_id
   *   The Entity ID.
   * @param string $entity_uuid
   *   The Entity UUID.
   * @param string $modified
   *   The CDF's modified timestamp.
   * @param string $origin
   *   The origin UUID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This same object.
   */
  public function setExportedEntity($entity_type, $entity_id, $entity_uuid, $modified, $origin) {
    return $this->setTrackingEntity($entity_type, $entity_id, $entity_uuid, $modified, $origin, self::INITIATED, '');
  }

  /**
   * Helper function to set the Imported Tracking Entity.
   *
   * @param string $entity_type
   *   The Entity Type.
   * @param int $entity_id
   *   The Entity ID.
   * @param string $entity_uuid
   *   The Entity UUID.
   * @param string $modified
   *   The CDF's modified timestamp.
   * @param string $origin
   *   The origin UUID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This same object.
   */
  public function setImportedEntity($entity_type, $entity_id, $entity_uuid, $modified, $origin) {
    return $this->setTrackingEntity($entity_type, $entity_id, $entity_uuid, $modified, $origin, '', self::AUTO_UPDATE_ENABLED);
  }

  /**
   * Returns the Imported Entity object.
   *
   * @return object
   *   The Imported Entity object.
   */
  protected function getTrackingEntity() {
    return $this->trackingEntity;
  }

  /**
   * Returns the Entity ID.
   *
   * @return int
   *   The Entity ID.
   */
  public function getEntityId() {
    return isset($this->getTrackingEntity()->entity_id) ? $this->getTrackingEntity()->entity_id : NULL;
  }

  /**
   * Returns the Entity Type.
   *
   * @return string
   *   The Entity Type.
   */
  public function getEntityType() {
    return isset($this->getTrackingEntity()->entity_type) ? $this->getTrackingEntity()->entity_type : NULL;
  }

  /**
   * Returns the Entity's UUID.
   *
   * @return string
   *   The Entity's UUID.
   */
  public function getUuid() {
    return isset($this->getTrackingEntity()->entity_uuid) ? $this->getTrackingEntity()->entity_uuid : NULL;
  }

  /**
   * Returns the Export Status.
   *
   * @return string
   *   The Export Status.
   */
  protected function getExportStatus() {
    return isset($this->getTrackingEntity()->status_export) ? $this->getTrackingEntity()->status_export : NULL;
  }

  /**
   * Check if the entity initiated or not.
   *
   * @return bool
   *   TRUE if the entity initiated, FALSE otherwise.
   */
  public function isInitiated() {
    return $this->getExportStatus() === self::INITIATED;
  }

  /**
   * Check if the entity queued or not.
   *
   * @return bool
   *   TRUE if the entity queued, FALSE otherwise.
   */
  public function isQueued() {
    return $this->getExportStatus() === self::QUEUED;
  }

  /**
   * Check if the entity exported or not.
   *
   * @return bool
   *   TRUE if the entity exported, FALSE otherwise.
   */
  public function isExported() {
    return $this->getExportStatus() === self::EXPORTED;
  }

  /**
   * Check if the entity is set for reindex or not.
   *
   * @return bool
   *   TRUE if the entity is set for REINDEX, FALSE otherwise.
   */
  public function isReindex() {
    return $this->getExportStatus() === self::REINDEX;
  }

  /**
   * Returns the Import Status.
   *
   * This function should not be public and the service's consumers should not
   * know about the class's AUTO_UPDATE_* contants.
   *
   * @return string
   *   The Import Status.
   */
  protected function getImportStatus() {
    return isset($this->getTrackingEntity()->status_import) ? $this->getTrackingEntity()->status_import : NULL;
  }

  /**
   * Check if the entity auto-updates or not.
   *
   * @return bool
   *   TRUE if the entity auto updates, FALSE otherwise.
   */
  public function isAutoUpdate() {
    return $this->getImportStatus() === self::AUTO_UPDATE_ENABLED;
  }

  /**
   * Check if the entity auto-updates is disabled.
   *
   * @return bool
   *   TRUE if the entity auto update is disabled, FALSE otherwise.
   */
  public function isAutoUpdateDisabled() {
    return $this->getImportStatus() === self::AUTO_UPDATE_DISABLED;
  }

  /**
   * Check if the entity is pending synchronization to Content Hub or not.
   *
   * @return bool
   *   TRUE if the entity is pending synchronization, FALSE otherwise.
   */
  public function isPendingSync() {
    return $this->getImportStatus() === self::PENDING_SYNC;
  }

  /**
   * Check if the entity has local change or not.
   *
   * @return bool
   *   TRUE if the entity has local change, FALSE otherwise.
   */
  public function hasLocalChange() {
    return $this->getImportStatus() === self::PENDING_SYNC ||
      $this->getImportStatus() === self::HAS_LOCAL_CHANGE;
  }

  /**
   * Check if the entity is a dependent of another entity.
   *
   * @return bool
   *   TRUE if the entity is a dependent, FALSE otherwise.
   */
  public function isDependent() {
    return $this->getImportStatus() === self::IS_DEPENDENT;
  }

  /**
   * Returns the modified timestamp.
   *
   * @return string
   *   The modified timestamp.
   */
  public function getModified() {
    return isset($this->getTrackingEntity()->modified) ? $this->getTrackingEntity()->modified : NULL;
  }

  /**
   * Returns the Origin.
   *
   * @return int|string
   *   The Origin.
   */
  public function getOrigin() {
    return isset($this->getTrackingEntity()->origin) ? $this->getTrackingEntity()->origin : NULL;
  }

  /**
   * Return this site's origin.
   *
   * @return array|mixed|null
   *   The UUID of this site's origin.
   */
  public function getSiteOrigin() {
    return $this->contentHubAdminConfig->get('origin');
  }

  /**
   * Sets the Export Status.
   *
   * @param string $status_export
   *   Could be INITIATED or EXPORTED.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   This ContentHubEntitiesTracking object if succeeds, FALSE otherwise.
   */
  protected function setExportStatus($status_export) {
    $this->getTrackingEntity()->status_export = $status_export;
    return $this;
  }

  /**
   * Sets the entity to the state of "queued".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setQueued() {
    $this->setExportStatus(self::QUEUED);
    return $this;
  }

  /**
   * Sets the entity to the state of "initiated".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setInitiated() {
    $this->setExportStatus(self::INITIATED);
    return $this;
  }

  /**
   * Sets the entity to the state of "exported".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setExported() {
    $this->setExportStatus(self::EXPORTED);
    return $this;
  }

  /**
   * Sets the Import Status.
   *
   * @param string $status_import
   *   See the constants for possible values.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   This ContentHubEntitiesTracking object if succeeds, FALSE otherwise.
   *
   * @throws \Exception
   *   When trying to set status as "IS_DEPENDENT", which is immutable.
   */
  protected function setImportStatus($status_import) {
    if ($this->isDependent()) {
      throw new \Exception('The "IS_DEPENDENT" status is immutable, and cannot be set again.');
    }
    $this->getTrackingEntity()->status_import = $status_import;
    return $this;
  }

  /**
   * Sets the entity to auto-update.
   *
   * @param bool $auto_update
   *   TRUE if set to auto update, FALSE otherwise.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setAutoUpdate($auto_update = TRUE) {
    // Case 1: If current state is already "has local change" or "pending
    // update" and we are to set "no auto update", don't set anything. This is
    // because "no auto update" is already implied by current status.
    if ($this->hasLocalChange() && !$auto_update) {
      return $this;
    }
    // All other cases: set as instructed.
    $auto_update_value = $auto_update ? self::AUTO_UPDATE_ENABLED : self::AUTO_UPDATE_DISABLED;
    $this->setImportStatus($auto_update_value);
    return $this;
  }

  /**
   * Sets the entity to the state of "pending synchronization from Content Hub".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setPendingSync() {
    $this->setImportStatus(self::PENDING_SYNC);
    return $this;
  }

  /**
   * Sets the entity to the state of "has local change".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setLocalChange() {
    $this->setImportStatus(self::HAS_LOCAL_CHANGE);
    return $this;
  }

  /**
   * Sets the entity to the state of "is a dependent".
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setDependent() {
    $this->setImportStatus(self::IS_DEPENDENT);
    return $this;
  }

  /**
   * Sets the modified timestamp.
   *
   * @param string $modified
   *   Sets the modified timestamp.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   This ContentHubEntitiesTracking object.
   */
  public function setModified($modified) {
    $this->getTrackingEntity()->modified = $modified;
    return $this;
  }

  /**
   * Returns the tracking entity if it is an exported entity.
   *
   * @return ContentHubEntitiesTracking|bool
   *   This entity if it is an exported entity, FALSE otherwise.
   */
  protected function isExportedEntity() {
    return empty($this->getExportStatus()) ? FALSE : $this;
  }

  /**
   * Returns the tracking entity if it is an imported entity.
   *
   * @return ContentHubEntitiesTracking|bool
   *   This record if it is an imported entity, FALSE otherwise.
   */
  protected function isImportedEntity() {
    return empty($this->getImportStatus()) ? FALSE : $this;
  }

  /**
   * Saves a record of an imported entity.
   *
   * @return bool
   *   TRUE if saving is successful, FALSE otherwise.
   */
  public function save() {
    // If we reached here then we have a valid input and can save safely.
    $query = $this->database->merge(self::TABLE)
      ->key([
        'entity_id' => $this->getEntityId(),
        'entity_type' => $this->getEntityType(),
        'entity_uuid' => $this->getUuid(),
      ])
      ->fields([
        'status_export' => $this->getExportStatus(),
        'status_import' => $this->getImportStatus(),
        'modified' => $this->getModified(),
        'origin' => $this->getOrigin(),
      ]);

    try {
      $result = $query->execute();
    }
    catch (IntegrityConstraintViolationException $exception) {
      // There is an entity in the database with the same ID but different UUID.
      // It needs to be deleted before we can proceed.
      if ($record = ContentHubEntitiesTracking::loadByDrupalEntity($this->getEntityType(), $this->getEntityId())) {
        $record->delete();
        \Drupal::logger('acquia_contenthub')->debug('An outdated record for entity (%id, %type, %uuid) was deleted from the Entities Tracking table due to UUID mismatch.', [
          '%id' => $record->getEntityId(),
          '%type' => $record->getEntityType(),
          '%uuid' => $record->getUuid(),
        ]);

        // Now retry the original query.
        $result = $query->execute();
      }
      else {
        // If there is no duplicate record with different UUID, then the error
        // is unknown. Just throw the exception as it would normally happen.
        throw $exception;
      }
    }

    switch ($result) {
      case Merge::STATUS_INSERT:
      case Merge::STATUS_UPDATE:
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Deletes the entry for this particular entity.
   */
  public function delete() {
    $entity_type = $this->getEntityType();
    $entity_id = $this->getEntityId();
    if (!empty($entity_type) && !empty($entity_id)) {
      unset($this->cachedTrackingEntities[$entity_type][$entity_id]);
      return $this->database->delete(self::TABLE)
        ->condition('entity_type', $this->getEntityType())
        ->condition('entity_id', $this->getEntityId())
        ->execute();
    }
    if (Uuid::isValid($this->getUuid())) {
      return $this->database->delete(self::TABLE)
        ->condition('entity_uuid', $this->getUuid())
        ->execute();
    }
    return FALSE;
  }

  /**
   * Loads an Exported Entity tracking record by entity key information.
   *
   * @param string $entity_type
   *   The Entity type.
   * @param string $entity_id
   *   The entity ID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   The ContentHubEntitiesTracking object if it exists and is exported,
   *   FALSE otherwise.
   */
  public function loadExportedByDrupalEntity($entity_type, $entity_id) {
    if ($exported_entity = $this->loadByDrupalEntity($entity_type, $entity_id)) {
      return $exported_entity->isExportedEntity();
    }
    return FALSE;
  }

  /**
   * Loads an Imported Entity tracking record by entity key information.
   *
   * @param string $entity_type
   *   The Entity type.
   * @param string $entity_id
   *   The entity ID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   The ContentHubEntitiesTracking object if it exists and is imported,
   *   FALSE otherwise.
   */
  public function loadImportedByDrupalEntity($entity_type, $entity_id) {
    if ($imported_entity = $this->loadByDrupalEntity($entity_type, $entity_id)) {
      return $imported_entity->isImportedEntity();
    }
    return FALSE;
  }

  /**
   * Loads a record using Drupal entity key information.
   *
   * @param string $entity_type
   *   The Entity type.
   * @param string $entity_id
   *   The entity ID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   This ContentHubEntitiesTracking object if succeeds, FALSE otherwise.
   */
  protected function loadByDrupalEntity($entity_type, $entity_id) {
    // Utilize local cache to skip database calls.
    if (isset($this->cachedTrackingEntities[$entity_type][$entity_id])) {
      $tracking_entity = $this->cachedTrackingEntities[$entity_type][$entity_id];
      $this->setTrackingEntity($tracking_entity->entity_type, $tracking_entity->entity_id, $tracking_entity->entity_uuid, $tracking_entity->modified, $tracking_entity->origin, $tracking_entity->status_export, $tracking_entity->status_import);
      return $this;
    }

    $result = $this->database->select(self::TABLE, 'ci')
      ->fields('ci')
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_id)
      ->execute()
      ->fetchAssoc();

    if (!$result) {
      return FALSE;
    }

    $this->setTrackingEntity($result['entity_type'], $result['entity_id'], $result['entity_uuid'], $result['modified'], $result['origin'], $result['status_export'], $result['status_import']);
    return $this;
  }

  /**
   * Loads an Exported Entity tracking record by UUID.
   *
   * @param string $entity_uuid
   *   The entity uuid.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   The ContentHubEntitiesTracking object if it exists and is exported,
   *   FALSE otherwise.
   */
  public function loadExportedByUuid($entity_uuid) {
    if ($exported_entity = $this->loadByUuid($entity_uuid)) {
      return $exported_entity->isExportedEntity();
    }
    return FALSE;
  }

  /**
   * Loads an Imported Entity tracking record by UUID.
   *
   * @param string $entity_uuid
   *   The entity uuid.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   The ContentHubEntitiesTracking object if it exists and is imported,
   *   FALSE otherwise.
   */
  public function loadImportedByUuid($entity_uuid) {
    if ($imported_entity = $this->loadByUuid($entity_uuid)) {
      return $imported_entity->isImportedEntity();
    }
    return FALSE;
  }

  /**
   * Loads a record using an Entity's UUID.
   *
   * @param string $entity_uuid
   *   The entity's UUID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   This ContentHubEntitiesTracking object if succeeds, FALSE otherwise.
   */
  public function loadByUuid($entity_uuid) {
    if (!Uuid::isValid($entity_uuid)) {
      return FALSE;
    }
    $result = $this->database->select(self::TABLE, 'ci')
      ->fields('ci')
      ->condition('entity_uuid', $entity_uuid)
      ->execute()
      ->fetchAssoc();

    if (!$result) {
      return FALSE;
    }

    $this->setTrackingEntity($result['entity_type'], $result['entity_id'], $result['entity_uuid'], $result['modified'], $result['origin'], $result['status_export'], $result['status_import']);
    return $this;
  }

  /**
   * Produces a database query that matches an origin and status.
   *
   * @param string $origin
   *   The origin UUID.
   * @param string $status_export
   *   The Export Status.
   * @param string $status_import
   *   The Import Status.
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return bool|\Drupal\Core\Database\Query\SelectInterface
   *   The Select Query or FALSE.
   */
  private function getFromOriginQuery($origin, $status_export = '', $status_import = '', $entity_type_id = '') {
    if (Uuid::isValid($origin) && ($status_export xor $status_import)) {
      /** @var \Drupal\Core\Database\Query\SelectInterface $query */
      $query = $this->database->select(self::TABLE, 'ci')
        ->fields('ci')
        ->condition('origin', $origin);
      if (!empty($status_export)) {
        $query = $query->condition('status_export', $status_export);
      }
      else {
        $query = $query->condition('status_import', $status_import);
      }
      if (!empty($entity_type_id)) {
        $query = $query->condition('entity_type', $entity_type_id);
      }
      return $query;
    }
    return FALSE;
  }

  /**
   * Database query that fetches entities based on type and status.
   *
   * @param string $status_import
   *   The Import Status.
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   Entities returned from the query.
   */
  public function getImportedEntities($status_import = '', $entity_type_id = '') {
    if ($status_import) {
      /** @var \Drupal\Core\Database\Query\SelectInterface $query */
      $query = $this->database->select(self::TABLE, 'ci')
        ->fields('ci');
      $query = $query->condition('status_import', $status_import);
      if (!empty($entity_type_id)) {
        $query = $query->condition('entity_type', $entity_type_id);
      }
      return $query->execute()->fetchAll();
    }
    return [];
  }

  /**
   * Lists all imported entities that match a certain origin and status.
   *
   * @param string $origin
   *   The origin UUID.
   * @param string $status_export
   *   The Export Status.
   * @param string $status_import
   *   The Import Status.
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   An array containing the list of imported entities from a certain origin.
   */
  private function getFromOrigin($origin, $status_export = '', $status_import = '', $entity_type_id = '') {
    if ($query = $this->getFromOriginQuery($origin, $status_export, $status_import, $entity_type_id)) {
      return $query
        ->execute()
        ->fetchAll();
    }
    return [];
  }

  /**
   * Obtains the number of entities that match a certain origin and status.
   *
   * @param string $origin
   *   The origin UUID.
   * @param string $status_export
   *   The Export Status.
   * @param string $status_import
   *   The Import Status.
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return int
   *   The number of entities.
   */
  private function getFromOriginCount($origin, $status_export = '', $status_import = '', $entity_type_id = '') {
    if ($query = $this->getFromOriginQuery($origin, $status_export, $status_import, $entity_type_id)) {
      return $query->countQuery()->execute()->fetchField();
    }
    return 0;
  }

  /**
   * Obtains a list of exported entities set to Reindex.
   *
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   An array of Tracked Entities set to reindex.
   */
  public function getEntitiesToReindex($entity_type_id = NULL) {
    $origin = $this->getSiteOrigin();
    return $this->getFromOrigin($origin, self::REINDEX, '', $entity_type_id);
  }

  /**
   * Obtains a list of exported entities.
   *
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   An array of Tracked Entities published to Content Hub.
   */
  public function getPublishedEntities($entity_type_id = NULL) {
    $origin = $this->getSiteOrigin();
    return $this->getFromOrigin($origin, self::EXPORTED, '', $entity_type_id);
  }

  /**
   * Obtains a list of "initiated" entities.
   *
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   An array of Tracked Entities with status_export = "initiated".
   */
  public function getInitiatedEntities($entity_type_id = NULL) {
    $origin = $this->getSiteOrigin();
    return $this->getFromOrigin($origin, self::INITIATED, '', $entity_type_id);
  }

  /**
   * Obtains a list of "queued" entities.
   *
   * @param string $entity_type_id
   *   The Entity type.
   *
   * @return array
   *   An array of Tracked Entities with status_export = "queued".
   */
  public function getQueuedEntities($entity_type_id = NULL) {
    $origin = $this->getSiteOrigin();
    return $this->getFromOrigin($origin, self::QUEUED, '', $entity_type_id);
  }

  /**
   * Obtains the number of exported entities marked for Reindex.
   *
   * @return int
   *   The number of entities marked for reindex.
   */
  public function getCountEntitiesToReindex() {
    $origin = $this->getSiteOrigin();
    return $this->getFromOriginCount($origin, self::REINDEX);
  }

  /**
   * Sets all Exported Entities as Need Reindex.
   *
   * @param string $entity_type_id
   *   The Entity type.
   * @param string $bundle_id
   *   The Entity bundle.
   *
   * @return bool|\Drupal\Core\Database\StatementInterface|int|null
   *   The new Update Object if given a valid origin, FALSE otherwise.
   */
  public function setExportedEntitiesForReindex($entity_type_id = '', $bundle_id = NULL) {
    $origin = $this->getSiteOrigin();
    if (Uuid::isValid($origin)) {
      if (empty($bundle_id)) {
        return $this->database->update(self::TABLE)
          ->condition('entity_type', $entity_type_id)
          ->condition('status_export', self::EXPORTED)
          ->condition('origin', $origin)
          ->fields([
            'status_export' => self::REINDEX,
          ])
          ->execute();
      }
      else {
        // Capture all tracked entities from a specific bundle stored in the
        // tracking table to re-export later on.
        $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
        $base_table = $entity_type->getBaseTable();
        $id_col = $entity_type->getKey("id");
        $bundle_col = $entity_type->getKey("bundle");
        $query = $this->database->select(self::TABLE, 'a')
          ->fields('a', ['entity_id']);
        $query->addJoin('INNER', $base_table, 'b', "a.entity_id = b.{$id_col}");
        $query
          ->condition('a.entity_type', $entity_type_id)
          ->condition('a.status_export', self::EXPORTED)
          ->condition('a.origin', $origin)
          ->condition("b.{$bundle_col}", $bundle_id);
        $entity_ids = $query->execute()->fetchCol("entity_id");

        // If there are entities matching this condition, mark them for
        // re-export after reindexation.
        if (!empty($entity_ids)) {
          return $this->database->update(self::TABLE)
            ->condition('entity_type', $entity_type_id)
            ->condition('status_export', self::EXPORTED)
            ->condition('entity_id', $entity_ids, 'IN')
            ->condition('origin', $origin)
            ->fields([
              'status_export' => self::REINDEX,
            ])
            ->execute();
        }
      }
    }
    return FALSE;
  }

  /**
   * Deletes all or a given set of exported entities from the tracking table.
   *
   * @param array $uuids
   *   An array of entities' uuids to delete.
   *
   * @return int
   *   The output result from the delete query.
   */
  public function deleteExportedEntities(array $uuids = []) {
    $query = $this->database->delete(self::TABLE)
      ->condition('status_export', [
        self::QUEUED,
        self::INITIATED,
        self::EXPORTED,
      ], 'IN');

    // If a set of uuids were given.
    if (count($uuids) > 0) {
      $query->condition('entity_uuid', $uuids, 'IN');
    }

    // Delete entities.
    return $query->execute();
  }

  /**
   * Method to load multiple uuid, timestamp by given array.
   *
   * @param array $uuids
   *   Array of uuid.
   *
   * @return array
   *   Array[uuid] = timestamp
   */
  public function loadMultipleByUuid(array $uuids) {
    $query = $this->database->select(self::TABLE, 'acet');

    $query->addField('acet', 'entity_uuid');
    $query->addField('acet', 'modified');

    $query->condition('entity_uuid', $uuids, 'IN');
    $query->condition('status_import', self::AUTO_UPDATE_ENABLED);

    $result = $query->execute()->fetchAllKeyed();
    return $result;
  }

}
