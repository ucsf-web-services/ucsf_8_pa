<?php

namespace Drupal\acquia_contenthub;

use Drupal\Core\Entity\RevisionableInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Component\Utility\UrlHelper;
use GuzzleHttp\Exception\RequestException;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides a service for managing entity actions for Content Hub.
 *
 * @TODO To be renamed to "ExportEntityManager".
 */
class EntityManager {

  // Possible actions that the Entity Manager can queue entities for.
  const EXPORT = 'EXPORT';
  const UNEXPORT = 'UNEXPORT';

  /**
   * Base root.
   *
   * @var string
   */
  private $baseRoot;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

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
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $entityTypeBundleInfoManager;

  /**
   * The list of candidate entities for next bulk export.
   *
   * @var array
   */
  private $candidateEntities = [
    SELF::EXPORT => [],
    SELF::UNEXPORT => [],
  ];

  /**
   * The "shutdown function is registered" flag.
   *
   * @var bool
   */
  private $shutdownFunctionRegistered = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('acquia_contenthub.client_manager'),
      $container->get('acquia_contenthub.acquia_contenthub_entities_tracking'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * Constructs an EntityManager object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $acquia_contenthub_entities_tracking
   *   The Content Hub Entities Tracking.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info_manager
   *   The Entity Type Bundle Info Manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, ClientManagerInterface $client_manager, ContentHubEntitiesTracking $acquia_contenthub_entities_tracking, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info_manager) {
    $this->baseRoot = isset($GLOBALS['base_root']) ? $GLOBALS['base_root'] : '';
    $this->loggerFactory = $logger_factory;
    $this->clientManager = $client_manager;
    $this->contentHubEntitiesTracking = $acquia_contenthub_entities_tracking;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfoManager = $entity_type_bundle_info_manager;
    // Get the content hub config settings.
    $this->config = $config_factory->get('acquia_contenthub.admin_settings');
  }

  /**
   * Get candidate entities.
   *
   * @return array
   *   The list of candidate entities of a specific action.
   */
  public function getExportCandidateEntities() {
    return $this->candidateEntities[SELF::EXPORT];
  }

  /**
   * Enqueue an entity with an operation to be performed on Content Hub.
   *
   * @param object $entity
   *   The Drupal Entity object.
   * @param bool $do_export
   *   TRUE if the entity action is 'EXPORT'; FALSE, if it is 'UNEXPORT'.
   */
  public function enqueueCandidateEntity($entity, $do_export = TRUE) {
    // Early return, if entity is not eligible.
    if (!$this->isEligibleEntity($entity)) {
      return;
    }

    // Register shutdown function.
    $this->registerShutdownFunction();

    $unexporting_entities = [];
    $exporting_entities = [];
    // Only care about the case where entity is 1) node, and 2) updating.
    if ($entity instanceof NodeInterface && isset($entity->original)) {
      // We also know the $do_export at this point should be TRUE.
      $old_is_published = $entity->original->isPublished();
      $new_is_published = $this->isPublished($entity);
      if (!$new_is_published) {
        $do_export = FALSE;
      }
      // If "published to unpublished", unexport it and its old descendants.
      if ($old_is_published && !$new_is_published) {
        $unexporting_entities += $this->getReferencedEntities($entity->original);
      }
    }
    // Only add references to be exported if the entity is to be exported. In
    // the case of unexporting/deletion, references should not be exported.
    if ($do_export) {
      $exporting_entities += $this->getReferencedEntities($entity);
    }

    // If "to published", we have to move any disassociated entities exporting
    // list to unexporting list.
    if ($do_export && $entity instanceof EntityInterface && isset($entity->original)) {
      // Find all UUIDs of the exported entities.
      $exporting_entities_uuids = [];
      foreach ($exporting_entities as $exporting_entity) {
        $exporting_entities_uuids[$exporting_entity->uuid()] = TRUE;
      }

      $exported_entities = $this->getReferencedEntities($entity->original);
      // For each exported entity, check if it still remains associated.
      foreach ($exported_entities as $key => $exported_entity) {
        // If remain associated, leave it alone.
        if (isset($exporting_entities_uuids[$exported_entity->uuid()])) {
          continue;
        }
        // Otherwise, if disassociated, move it from export to unexport queue.
        $unexporting_entities[] = $exported_entity;
        unset($exported_entities[$key]);
      }
    }

    // Enqueue unexport and export entities separately.
    $this->enqueueQualifiedEntities($unexporting_entities, FALSE);
    $this->enqueueQualifiedEntities($exporting_entities, TRUE);
    $action = $do_export ? SELF::EXPORT : SELF::UNEXPORT;
    $this->candidateEntities[$action][$entity->uuid()] = $entity;
  }

  /**
   * Enqueue array of entities with an operation to be performed on Content Hub.
   *
   * @param array $entities
   *   A list of entities.
   * @param bool $do_export
   *   TRUE if the entity action is 'EXPORT'; FALSE, if it is 'UNEXPORT'.
   */
  private function enqueueQualifiedEntities(array $entities, $do_export) {
    foreach ($entities as $entity) {
      // TODO: Paragraphs' handling is hardcoded in Export Entity Manager right
      // now, but need to be refactored out of this class in the future.
      if (!$entity instanceof ParagraphInterface) {
        continue;
      }
      $this->enqueueCandidateParagraph($entity, $do_export);
    }
  }

  /**
   * Enqueue a paragraph with an operation to be performed on Content Hub.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The Paragraph object.
   * @param bool $do_export
   *   TRUE if the entity action is 'EXPORT'; FALSE, if it is 'UNEXPORT'.
   */
  private function enqueueCandidateParagraph(ParagraphInterface $paragraph, $do_export) {
    // Early return, if entity is not eligible.
    if (!$this->isEligibleEntity($paragraph)) {
      return;
    }
    $referenced_entities = $paragraph->referencedEntities();
    $this->enqueueQualifiedEntities($referenced_entities, $do_export);
    $action = $do_export ? SELF::EXPORT : SELF::UNEXPORT;
    $this->candidateEntities[$action][$paragraph->uuid()] = $paragraph;
  }

  /**
   * Register shutdown function.
   */
  private function registerShutdownFunction() {
    if ($this->shutdownFunctionRegistered) {
      return;
    }
    // Register shutdown function to send/delete entities to/from Content Hub.
    drupal_register_shutdown_function('acquia_contenthub_bulk_export');
    $this->shutdownFunctionRegistered = TRUE;
  }

  /**
   * Tracks the number of entities that fail to bulk upload.
   *
   * @param string $num
   *   Number of failed entities added to the pool.
   *
   * @return string
   *   The total number of entities that failed to bulk upload.
   */
  private function entityFailures($num = NULL) {
    $total = &drupal_static(__METHOD__);
    if (!isset($total)) {
      $total = is_int($num) ? $num : 0;
    }
    else {
      $total = is_int($num) ? $total + $num : $total;
    }
    return $total;
  }

  /**
   * Sends the entities for update to Content Hub.
   *
   * @param string $resource_url
   *   The Resource Url.
   *
   * @return bool
   *   Returns the response.
   */
  public function updateRemoteEntities($resource_url) {
    if ($response = $this->clientManager->createRequest('updateEntities', [$resource_url])) {
      $response = json_decode($response->getBody(), TRUE);
    }
    return empty($response['success']) ? FALSE : TRUE;
  }

  /**
   * Bulk-export all the enqueued entities.
   */
  public function bulkExport() {
    $this->unexportDisqualifiedExportCandidateEntities();
    $this->unexportCandidateEntities();
  }

  /**
   * Delete entities from Content Hub that are explicitly un-exported.
   */
  private function unexportCandidateEntities() {
    $candidate_entites = $this->candidateEntities[SELF::UNEXPORT];
    foreach ($candidate_entites as $uuid => $candidate_entity) {
      $this->deleteRemoteEntity($candidate_entity);
      unset($this->candidateEntities[SELF::UNEXPORT][$uuid]);
    }
  }

  /**
   * Delete entities from Content Hub that are disqualified of exporting.
   */
  private function unexportDisqualifiedExportCandidateEntities() {
    $candidate_entites = $this->candidateEntities[SELF::EXPORT];
    foreach ($candidate_entites as $uuid => $candidate_entity) {
      $root_ancestor_entity = $this->findRootAncestorEntity($candidate_entity);
      // If root ancestor is not published, delete the current entity.
      if ($root_ancestor_entity instanceof NodeInterface && !$this->isPublished($root_ancestor_entity)) {
        $this->candidateEntities[SELF::UNEXPORT][$uuid] = $candidate_entity;
        unset($this->candidateEntities[SELF::EXPORT][$uuid]);
      }
    }
  }

  /**
   * Delete an entity from Content Hub.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Content Hub Entity.
   */
  private function deleteRemoteEntity(EntityInterface $entity) {
    // Check if the entity was never exported first, to avoid sending a DELETE
    // request to the Content Hub service.
    if ($this->contentHubEntitiesTracking->loadExportedByUuid($entity->uuid()) === FALSE) {
      return;
    }
    /** @var \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager */
    try {
      $client = $this->clientManager->getConnection();
    }
    catch (ContentHubException $e) {
      $this->loggerFactory->get('acquia_contenthub')->error($e->getMessage());
      return;
    }

    $resource_url = $this->getResourceUrl($entity);
    $args = [
      '%type' => $entity->getEntityTypeId(),
      '%uuid' => $entity->uuid(),
      '%id' => $entity->id(),
    ];
    if (!$resource_url) {
      $this->loggerFactory->get('acquia_contenthub')->error('Error trying to form a unique resource Url for %type with uuid %uuid and id %id', $args);
      return;
    }

    try {
      $uuid = $entity->uuid();
      $response = $client->deleteEntity($uuid);
      $args = [
        '%uuid' => $uuid,
        '%type' => $entity->getEntityTypeId(),
        '%id' => $entity->id(),
      ];
      $this->loggerFactory->get('acquia_contenthub')->debug('Deleting remote entity with UUID = %uuid (%type, %id)', $args);
      $exported_entity = $this->contentHubEntitiesTracking->loadExportedByUuid($uuid);
      if ($exported_entity) {
        $exported_entity->delete();
      }
    }
    catch (RequestException $e) {
      $args['%error'] = $e->getMessage();
      $this->loggerFactory->get('acquia_contenthub')->error('Error trying to post the resource url for %type with uuid %uuid and id %id with a response from the API: %error', $args);
      return;
    }

    // Make sure it is within the 2XX range. Expected response is a 202.
    $status_code = $response->getStatusCode();
    if (substr($status_code, 0, 2) !== '20') {
      $this->loggerFactory->get('acquia_contenthub')->error('Error trying to post the resource url for %type with uuid %uuid and id %id: Response status code was not 20X as expected.', $args);
    }
  }

  /**
   * Returns the local Resource URL.
   *
   * This is an absolute URL, which base_url can be overwritten with the
   * variable 'acquia_contenthub_rewrite_localdomain', which is especially
   * useful in cases where the Content Hub module is installed in a Drupal site
   * that is running locally (not from the public internet).
   *
   * @return string|bool
   *   The absolute resource URL, if it can be generated, FALSE otherwise.
   */
  private function getResourceUrl(EntityInterface $entity, $include_references = 'true') {
    // Check if there are link templates defined for the entity type and
    // use the path from the route instead of the default.
    $entity_type_id = $entity->getEntityTypeId();

    $route_name = 'acquia_contenthub.entity.' . $entity_type_id . '.GET.acquia_contenthub_cdf';
    $url_options = [
      'entity_type' => $entity_type_id,
      $entity_type_id => $entity->id(),
      '_format' => 'acquia_contenthub_cdf',
      'include_references' => $include_references,
    ];

    return $this->getResourceUrlByRouteName($route_name, $url_options);
  }

  /**
   * Returns the route's resource URL.
   *
   * @param string $route_name
   *   Route name.
   * @param array $url_options
   *   Bulk-upload Url query params.
   *
   * @return string
   *   returns URL.
   */
  private function getResourceUrlByRouteName($route_name, array $url_options = []) {
    $url = Url::fromRoute($route_name, $url_options);
    $path = $url->toString();

    // Get the content hub config settings.
    $rewrite_localdomain = $this->config->get('rewrite_domain');

    if (UrlHelper::isExternal($path)) {
      // If for some reason the $path is an external URL, do not further
      // prefix a domain, and do not overwrite the given domain.
      $full_path = $path;
    }
    elseif ($rewrite_localdomain) {
      $full_path = $rewrite_localdomain . $path;
    }
    else {
      $full_path = $this->baseRoot . $path;
    }
    $url = Url::fromUri($full_path);

    return $url->toUriString();
  }

  /**
   * Builds the bulk-upload url to make a single request.
   *
   * @param array $url_options
   *   Bulk-upload Url query params.
   *
   * @return string
   *   returns URL.
   */
  public function getBulkResourceUrl(array $url_options = []) {
    $route_name = 'acquia_contenthub.acquia_contenthub_bulk_cdf';
    return $this->getResourceUrlByRouteName($route_name, $url_options);
  }

  /**
   * Checks whether the current entity should be transferred to Content Hub.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Drupal entity.
   *
   * @return bool
   *   True if it can be parsed, False if it not a suitable entity for sending
   *   to content hub.
   */
  private function isEligibleEntity(EntityInterface $entity) {
    // Early return, if already sync'ing.
    if (!empty($entity->__contenthub_entity_syncing)) {
      return FALSE;
    }

    // Currently Content Hub does not support configuration entities to be
    // exported. Only content entities can be exported to Content Hub.
    if ($entity instanceof ConfigEntityInterface) {
      return FALSE;
    }

    $entity_type_id = $entity->getEntityTypeId();
    /** @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface $entity_type_config */
    $entity_type_config = $this->getContentHubEntityTypeConfigurationEntity($entity_type_id);

    $bundle_id = $entity->bundle();
    if (empty($entity_type_config) || empty($entity_type_config->isEnableIndex($bundle_id))) {
      return FALSE;
    }

    // If this is a file with status = 0 (TEMPORARY FILE) do not export it.
    // This is a check to avoid exporting temporary files.
    if ($entity instanceof FileInterface && $entity->isTemporary()) {
      return FALSE;
    }

    // If the entity has been imported before, then it didn't originate from
    // this site and shouldn't be exported.
    $entity_id = $entity->id();
    if ($this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity_type_id, $entity_id) !== FALSE) {
      // Is this an entity that does not belong to this site? Has it been
      // previously imported from Content Hub?
      $uuid = $entity->uuid();
      // We cannot bulk upload this entity because it does not belong to this
      // site. Add it to the pool of failed entities.
      if (isset($uuid)) {
        $this->entityFailures(1);

        // We can use this pool of failed entities to display a message to the
        // user about the entities that failed to export.
        // $args = [
        // '%type' => $entity_type_id,
        // '%uuid' => $uuid,
        // ];
        // $message = new FormattableMarkup('Cannot export %type entity with
        // UUID = %uuid to Content Hub because it was previously imported
        // (did not originate from this site).', $args);
        // $this->loggerFactory->get('acquia_contenthub')->error($message);
      }
      return FALSE;
    }

    $status = $entity->getEntityType()->hasKey("status") ? $entity->getEntityType()->getKey("status") : NULL;
    $revision_col = $entity->getEntityType()->hasKey("revision") ? $entity->getEntityType()->getKey("revision") : NULL;
    if ($status && $revision_col && $entity instanceof RevisionableInterface) {
      $definition = $entity->getFieldDefinition($status);
      $property = $definition->getFieldStorageDefinition()->getMainPropertyName();
      $value = $entity->get($status)->$property;
      if (!$value) {
        $table = $entity->getEntityType()->getBaseTable();
        $id_col = $entity->getEntityType()->getKey("id");
        $query = \Drupal::database()->select($table)
          ->fields($table, [$revision_col]);
        $query->condition("$table.$id_col", $entity->id());
        $revision_id = $query->execute()->fetchField();
        if ($revision_id != $entity->getRevisionId()) {
          return FALSE;
        }
      }
    }

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::getContainer()->get("module_handler");
    $results = $module_handler->invokeAll('acquia_contenthub_is_eligible_entity', [$entity]);
    foreach ($results as $result) {
      if ($result === FALSE) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Checks whether the current dependency should be transferred to Content Hub.
   *
   * Dependencies have an additional check as to whether they should be trans-
   * ferred to Content Hub. If they have been previously exported then they do
   * not need to be exported again. Dependent entities are those which are
   * referenced from an entity that has been fired through entity hooks.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return bool
   *   TRUE if it is eligible for export to Content Hub, FALSE otherwise.
   */
  public function isEligibleDependency(EntityInterface $entity) {
    if ($this->isEligibleEntity($entity)) {
      if ($entity_tracking = $this->contentHubEntitiesTracking->loadExportedByUuid($entity->uuid())) {
        if ($entity_tracking->isExported()) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns the list of enabled entity types for Content Hub.
   *
   * @return string[]
   *   A list of enabled entity type IDs.
   */
  public function getContentHubEnabledEntityTypeIds() {
    /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig[] $entity_type_ids */
    $entity_type_ids = $this->getContentHubEntityTypeConfigurationEntities();

    $enabled_entity_type_ids = [];
    foreach ($entity_type_ids as $entity_type_id => $entity_type_config) {
      $bundles = $entity_type_config->getBundles();

      // For a type to be enabled, it must at least have one bundle enabled.
      if (!empty(array_filter(array_column($bundles, 'enable_index')))) {
        $enabled_entity_type_ids[] = $entity_type_id;
      }
    }
    return $enabled_entity_type_ids;
  }

  /**
   * Returns the Content Hub configuration entity for this entity type.
   *
   * @param string $entity_type_id
   *   The Entity type ID.
   *
   * @return bool|\Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface
   *   The Configuration entity if exists, FALSE otherwise.
   */
  public function getContentHubEntityTypeConfigurationEntity($entity_type_id) {
    /** @var \Drupal\rest\RestResourceConfigInterface $contenthub_entity_config_storage */
    $contenthub_entity_config_storage = $this->entityTypeManager->getStorage('acquia_contenthub_entity_config');

    /** @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface[] $contenthub_entity_config_ids */
    $contenthub_entity_config_ids = $contenthub_entity_config_storage->loadMultiple([$entity_type_id]);
    $contenthub_entity_config_id = isset($contenthub_entity_config_ids[$entity_type_id]) ? $contenthub_entity_config_ids[$entity_type_id] : FALSE;
    return $contenthub_entity_config_id;
  }

  /**
   * Returns the list of configured Content Hub configuration entities.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface[]
   *   An array of Content Hub Configuration entities
   */
  public function getContentHubEntityTypeConfigurationEntities() {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $contenthub_entity_config_storage */
    $contenthub_entity_config_storage = $this->entityTypeManager->getStorage('acquia_contenthub_entity_config');

    /** @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface[] $contenthub_entity_config_ids */
    $contenthub_entity_config_ids = $contenthub_entity_config_storage->loadMultiple();
    return $contenthub_entity_config_ids;
  }

  /**
   * Obtains the list of entity types.
   */
  public function getAllowedEntityTypes() {
    // List of entities that are excluded from displaying on
    // entity configuration page and will not be pushed to Content Hub.
    $excluded_types = $this->getExcludedContentEntityTypeIds();
    $types = $this->entityTypeManager->getDefinitions();
    $entity_types = [];
    foreach ($types as $type => $entity) {
      // We only support content entity types at the moment, since config
      // entities don't implement \Drupal\Core\TypedData\ComplexDataInterface.
      if ($entity instanceof ContentEntityTypeInterface) {
        // Skip excluded types.
        if (in_array($type, $excluded_types)) {
          continue;
        }
        $bundles = $this->entityTypeBundleInfoManager->getBundleInfo($type);

        // Here we need to load all the different bundles?
        if (isset($bundles) && count($bundles) > 0) {
          foreach ($bundles as $key => $bundle) {
            $entity_types[$type][$key] = $bundle['label'];
          }
        }
      }
    }
    $entity_types = array_diff_key($entity_types, $excluded_types);
    return $entity_types;
  }

  /**
   * List of Content Entity Type Ids that are excluded from Content Hub.
   *
   * @return array
   *   List of content entity type IDs.
   */
  private function getExcludedContentEntityTypeIds() {
    return [
      'comment',
      'contact_message',
      'crop',
      'menu_link_content',
      'scheduled_update',
      'search_api_task',
      'shortcut',
      'user',
    ];
  }

  /**
   * Checks whether the current entity is supported by Content Hub.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   TRUE if it is a supported entity, FALSE otherwise.
   */
  public function isSupportedContentHubEntity(EntityInterface $entity) {
    // If the entity is not a Content Entity then it is not supported.
    if (!($entity instanceof ContentEntityInterface)) {
      return FALSE;
    }

    // From all content entities, some of them are excluded.
    $excluded_types = $this->getExcludedContentEntityTypeIds();
    if (in_array($entity->getEntityTypeId(), $excluded_types)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Obtains a list of referenced eligible Content Hub Entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity to check for references.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of all the referenced entities
   */
  private function getReferencedEntities(EntityInterface $entity) {
    $referenced_entities = $entity->referencedEntities();
    foreach ($referenced_entities as $key => $referenced_entity) {
      if (!$this->isSupportedContentHubEntity($referenced_entity)) {
        unset($referenced_entities[$key]);
      }
    }
    return $referenced_entities;
  }

  /**
   * Returns the entity's root ancestor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The current (child) entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The root ancestor entity.
   */
  private function findRootAncestorEntity(EntityInterface $entity) {
    if (!$entity || !method_exists($entity, 'getParentEntity')) {
      return $entity;
    }

    return $this->findRootAncestorEntity($entity->getParentEntity());
  }

  /**
   * Detect the publishing status of a particular entity.
   *
   * If the entity is translatable and has at least one published translation
   * then the resulting publishing status will be TRUE.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Content Entity.
   *
   * @return bool
   *   TRUE if at least one published translation, FALSE otherwise.
   */
  public function isPublished(ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::getContainer()->get("module_handler");
    if ($module_handler->moduleExists('content_translation')) {
      /** @var \Drupal\content_translation\ContentTranslationManagerInterface $translation_manager */
      $translation_manager = \Drupal::getContainer()->get("content_translation.manager");

      // If we are using content_translation module and this is a 'translatable'
      // entity, then check if at least one translation is published.
      if ($translation_manager->isEnabled($entity->getEntityTypeId(), $entity->bundle())) {
        // Check whether this entity has at least one published translation.
        $languages = $entity->getTranslationLanguages();
        foreach ($languages as $language) {
          $langcode = $language->getId();
          $localized_entity = $entity->getTranslation($langcode);
          /** @var \Drupal\content_translation\ContentTranslationMetadataWrapperInterface $translation_metadata */
          $translation_metadata = $translation_manager->getTranslationMetadata($localized_entity);
          if ($translation_metadata->isPublished()) {
            return TRUE;
          }
        }
        return FALSE;
      }
    }
    return ($entity instanceof NodeInterface) ? $entity->isPublished() : TRUE;
  }

}
