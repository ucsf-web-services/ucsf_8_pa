<?php

namespace Drupal\acquia_contenthub;

use Drupal\acquia_contenthub\QueueItem\ImportQueueItem;
use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\diff\DiffEntityComparison;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;

/**
 * Provides a service for managing imported entities' actions.
 */
class ImportEntityManager {

  use StringTranslationTrait;

  private $format = 'acquia_contenthub_cdf';

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * The Serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  private $serializer;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  private $entityRepository;

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
   * Diff module's entity comparison service.
   *
   * @var \Drupal\diff\DiffEntityComparison
   */
  private $diffEntityComparison;

  /**
   * The content hub entity manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  private $entityManager;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queue;

  /**
   * Implements the static interface create method.
   */
  public static function create(ContainerInterface $container) {
    kint($container->get('queue'));
    return new static(
      $container->get('database'),
      $container->get('logger.factory'),
      $container->get('serializer'),
      $container->get('entity.repository'),
      $container->get('acquia_contenthub.client_manager'),
      $container->get('acquia_contenthub.acquia_contenthub_entities_tracking'),
      $container->get('diff.entity_comparison'),
      $container->get('acquia_contenthub.entity_manager'),
      $container->get('string_translation'),
      $container->get('queue')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The Logger Factory.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The Serializer.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The Entity Repository.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entities_tracking
   *   The Content Hub Entities Tracking Service.
   * @param \Drupal\diff\DiffEntityComparison $entity_comparison
   *   The Diff module's Entity Comparison service.
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The entity manager for Content Hub.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The Queue Factory.
   */
  public function __construct(Connection $database, LoggerChannelFactoryInterface $logger_factory, SerializerInterface $serializer, EntityRepositoryInterface $entity_repository, ClientManagerInterface $client_manager, ContentHubEntitiesTracking $entities_tracking, DiffEntityComparison $entity_comparison, EntityManager $entity_manager, TranslationInterface $string_translation, QueueFactory $queue_factory) {
    $this->database = $database;
    $this->loggerFactory = $logger_factory;
    $this->serializer = $serializer;
    $this->entityRepository = $entity_repository;
    $this->clientManager = $client_manager;
    $this->contentHubEntitiesTracking = $entities_tracking;
    $this->diffEntityComparison = $entity_comparison;
    $this->entityManager = $entity_manager;
    $this->stringTranslation = $string_translation;
    $this->queue = $queue_factory;
  }

  /**
   * Compare entities by checking if the entities referenced by it has changed.
   *
   * Note: It needs to be a changed entity (has $entity->original).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check for differences.
   *
   * @return bool
   *   TRUE if it finds differences, FALSE otherwise.
   */
  private function compareReferencedEntities(EntityInterface $entity) {
    $new_references = $entity->referencedEntities();
    $old_references = $entity->original->referencedEntities();
    $new_uuids = array_map([$this, 'excludeCompareReferencedEntities'], $new_references);
    $old_uuids = array_map([$this, 'excludeCompareReferencedEntities'], $old_references);
    $changes = array_diff($new_uuids, $old_uuids);
    if (!empty($changes)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Excludes entity from comparison.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check for differences.
   *
   * @return null|string
   *   Entity uuid if entity is not excluded from comparison, NULL otherwise.
   */
  private function excludeCompareReferencedEntities(EntityInterface $entity) {

    // Currently Content Hub does not support configuration entities.
    if ($entity->getEntityType()->entityClassImplements('\Drupal\Core\Config\Entity\ConfigEntityInterface')) {
      return FALSE;
    }

    return $entity->uuid();
  }

  /**
   * Compare entities by checking if the fields information has changed.
   *
   * Note: It needs to be a changed entity (has $entity->original).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check for differences.
   *
   * @return bool
   *   TRUE if it finds differences, FALSE otherwise.
   */
  private function compareRevisions(EntityInterface $entity) {
    // Check if the entity has introduced any local changes.
    $field_comparisons = $this->diffEntityComparison->compareRevisions($entity->original, $entity);
    foreach ($field_comparisons as $field_comparison => $field_comparison_value) {
      list ($entity_id, $field_comparison_data) = explode(':', $field_comparison);
      list ($entity_type_id, $field_comparison_name) = explode('.', $field_comparison_data);

      if ($entity_id == $entity->id() && $entity_type_id == $entity->getEntityTypeId() && $this->isFieldReferencedToSubclassOf($entity, $field_comparison_name)) {
        continue;
      }

      if ($field_comparison_value['#data']['#left'] !== $field_comparison_value['#data']['#right']) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Determine if field have link to ConfigEntityInterface entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check for differences.
   * @param string $field_name
   *   The machine name of the field.
   * @param string $subclass
   *   The class or interface to check.
   *
   * @return bool
   *   TRUE if field have link to ConfigEntityInterface entity, FALSE otherwise.
   */
  private function isFieldReferencedToSubclassOf(EntityInterface $entity, $field_name, $subclass = '\Drupal\Core\Config\Entity\ConfigEntityInterface') {
    if (empty($entity) || empty($field_name)) {
      return FALSE;
    }
    $field_type = $entity->getFieldDefinition($field_name)->getType();

    if ($field_type == 'entity_reference') {
      $field_references = $entity->get($field_name)->referencedEntities();
      foreach ($field_references as $field_reference) {
        if ($field_reference->getEntityType()->entityClassImplements($subclass)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Act on the entity's presave action.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that is being saved.
   */
  public function entityPresave(EntityInterface $entity) {
    // Early return, if the entity doesn't have an older version or if it is
    // already sync'ing.
    if (!isset($entity->original) || !empty($entity->__contenthub_entity_syncing)) {
      return;
    }

    // Find the top-level host entity's import entity.
    $imported_entity = $this->findRootAncestorImportEntity($entity);
    // Early return, if the entity is not an imported entity, or it is not
    // "pending sync" or "has local change".
    if (!$imported_entity || $imported_entity->isPendingSync() || $imported_entity->hasLocalChange()) {
      return;
    }

    $has_local_change = $this->compareRevisions($entity) || $this->compareReferencedEntities($entity);

    // Don't do anything else if there is no local change.
    if (!$has_local_change) {
      return;
    }

    // Set and store the imported entity as having local changes.
    $imported_entity->setLocalChange();
    $imported_entity->save();
  }

  /**
   * Loads the Remote Content Hub Entity.
   *
   * @param string $uuid
   *   The Remote Entity UUID.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntityDependency|bool
   *   The Content Hub Entity Dependency if found, FALSE otherwise.
   */
  private function loadRemoteEntity($uuid) {
    $entity = $this->clientManager->createRequest('readEntity', [$uuid]);
    if (!$entity) {
      return FALSE;
    }

    return new ContentHubEntityDependency($entity);
  }

  /**
   * Obtains all dependencies for the current Content Hub Entity.
   *
   * It collects dependencies on all levels, flattening out the dependency array
   * to avoid looping circular dependencies.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $content_hub_entity
   *   The Content Hub Entity.
   * @param array $dependencies
   *   An array of \Drupal\acquia_contenthub\ContentHubEntityDependency.
   * @param bool|true $use_chain
   *   If the dependencies should be unique to the dependency chain or not.
   *
   * @return array
   *   An array of \Drupal\acquia_contenthub\ContentHubEntityDependency.
   */
  private function getAllRemoteDependencies(ContentHubEntityDependency $content_hub_entity, array &$dependencies, $use_chain = TRUE) {
    // Obtaining dependencies of this entity.
    $dep_dependencies = $this->getRemoteDependencies($content_hub_entity, $use_chain);

    /** @var \Drupal\acquia_contenthub\ContentHubEntityDependency $content_hub_dependency */
    foreach ($dep_dependencies as $uuid => $content_hub_dependency) {
      if (isset($dependencies[$uuid])) {
        continue;
      }

      // Also check if this dependency has been 1) previously imported, 2) is an
      // independent entity, and 3) has the same modified timestamp. If the
      // 'modified' timestamp matches, then we know we are trying to import an
      // entity that has no change, then it does not need to be imported again.
      $imported_entity = $this->contentHubEntitiesTracking->loadImportedByUuid($uuid);
      if ($imported_entity && !$imported_entity->isDependent() && $imported_entity->getModified() === $content_hub_dependency->getRawEntity()->getModified()) {
        continue;
      }

      $dependencies[$uuid] = $content_hub_dependency;
      $this->getAllRemoteDependencies($content_hub_dependency, $dependencies, $use_chain);
    }
    return array_reverse($dependencies, TRUE);
  }

  /**
   * Obtains First-level remote dependencies for the current Content Hub Entity.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $content_hub_entity
   *   The Content Hub Entity.
   * @param bool|true $use_chain
   *   If the dependencies should be unique to the dependency chain or not.
   *
   * @return array
   *   An array of \Drupal\acquia_contenthub\ContentHubEntityDependency.
   */
  private function getRemoteDependencies(ContentHubEntityDependency $content_hub_entity, $use_chain = TRUE) {
    $dependencies = [];
    $uuids = $content_hub_entity->getRemoteDependencies();

    foreach ($uuids as $uuid) {
      $content_hub_dependent_entity = $this->loadRemoteEntity($uuid);
      if ($content_hub_dependent_entity === FALSE) {
        continue;
      }
      // If this dependency is already tracked in the dependency chain
      // then we don't need to consider it a dependency unless we're not using
      // the chain.
      if ($content_hub_entity->isInDependencyChain($content_hub_dependent_entity) && $use_chain) {
        $content_hub_dependent_entity->setParent($content_hub_entity);
        continue;
      }
      $content_hub_dependent_entity->setParent($content_hub_entity);
      $dependencies[$uuid] = $content_hub_dependent_entity;
    }
    return $dependencies;
  }

  /**
   * Import an entity.
   *
   * @param string $uuid
   *   The UUID of the Entity to save.
   * @param bool $include_dependencies
   *   TRUE if we want to save all its dependencies, FALSE otherwise.
   * @param string $author
   *   The UUID of the author (user) that will own the entity.
   * @param int $status
   *   The publishing status of the entity (Applies to nodes).
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON Response.
   */
  public function import($uuid, $include_dependencies = TRUE, $author = NULL, $status = 0) {
    if (\Drupal::config('acquia_contenthub.entity_config')->get('import_with_queue')) {
      return $this->addEntityToImportQueue($uuid, $include_dependencies, $author, $status);
    }
    return $this->importRemoteEntity($uuid, $include_dependencies, $author, $status);
  }

  /**
   * Saves a Content Hub Entity into a Drupal Entity, given its UUID.
   *
   * This method accepts a parameter if we want to save all its dependencies.
   * Note that dependencies could be of 2 different types:
   *   - pre-dependency or Entity Independent:
   *       Has to be created before the host-entity and referenced from it.
   *   - post-dependency or Entity Dependent:
   *       Has to be created after the host-entity and referenced from it.
   * This is a recursive method, and will also create dependencies of the
   * dependencies.
   *
   * @param string $uuid
   *   The UUID of the Entity to save.
   * @param bool $include_dependencies
   *   TRUE if we want to save all its dependencies, FALSE otherwise.
   * @param string $author
   *   The UUID of the author (user) that will own the entity.
   * @param int $status
   *   The publishing status of the entity (Applies to nodes).
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON Response.
   */
  public function importRemoteEntity($uuid, $include_dependencies = TRUE, $author = NULL, $status = 0) {
    // Checking that the parameter given is a UUID.
    if (!Uuid::isValid($uuid)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }

    // If the Entity is not found in Content Hub then return a 404 Not Found.
    $contenthub_entity = $this->loadRemoteEntity($uuid);
    if (!$contenthub_entity) {
      $message = $this->t('Entity with UUID = @uuid not found.', [
        '@uuid' => $uuid,
      ]);
      return $this->jsonErrorResponseMessage($message, FALSE, 404);
    }

    $origin = $contenthub_entity->getRawEntity()->getOrigin();
    $site_origin = $this->contentHubEntitiesTracking->getSiteOrigin();

    // Checking that the entity origin is different than this site's origin.
    if ($origin === $site_origin) {
      $args = [
        '@type' => $contenthub_entity->getRawEntity()->getType(),
        '@uuid' => $contenthub_entity->getRawEntity()->getUuid(),
        '@origin' => $origin,
      ];
      $message = $this->t('Cannot save "@type" entity with uuid="@uuid". It has the same origin as this site: "@origin"', $args);
      $this->loggerFactory->get('acquia_contenthub')->warning($message);
      $result = FALSE;
      return $this->jsonErrorResponseMessage($message, $result, 403);
    }

    // Checking if bundle exists.
    $allowed_entity_types = $this->entityManager->getAllowedEntityTypes();
    $contenthub_entity_attribute = $contenthub_entity->getRawEntity()->getAttribute('type')['value'];
    $contenthub_entity_bundle = $contenthub_entity_attribute ? reset($contenthub_entity_attribute) : NULL;

    if ($contenthub_entity_bundle && !array_key_exists($contenthub_entity_bundle, $allowed_entity_types[$contenthub_entity->getRawEntity()->getType()])) {
      $args = [
        '@type' => $contenthub_entity->getRawEntity()->getType(),
        '@uuid' => $contenthub_entity->getRawEntity()->getUuid(),
        '@bundle' => $contenthub_entity_bundle,
      ];
      $message = $this->t('Cannot save "@type" entity with uuid="@uuid". Missing "@type" entity with bundle "@bundle"', $args);
      $this->loggerFactory->get('acquia_contenthub')->warning($message);
      $result = FALSE;
      return $this->jsonErrorResponseMessage($message, $result, 403);
    }

    // Collect and flat out all dependencies.
    $dependencies = [];
    if ($include_dependencies) {
      $dependencies = $this->getAllRemoteDependencies($contenthub_entity, $dependencies, TRUE);
    }

    // Obtaining the Status of the parent entity, if it is a node and
    // setting the publishing status of that entity.
    $contenthub_entity->setStatus($status);

    // Assigning author to this entity and dependencies.
    $contenthub_entity->setAuthor($author);

    foreach ($dependencies as $uuid => $dependency) {
      $dependencies[$uuid]->setAuthor($author);
      // Only change the Node status of dependent entities if they are nodes,
      // if the status flag is set and if they haven't been imported before.
      $entity_type = $dependency->getEntityType();
      if (isset($status) && $entity_type === 'node' && !$this->contentHubEntitiesTracking->loadImportedByUuid($uuid)) {
        $dependencies[$uuid]->setStatus($status);
      }
    }

    // Save this entity and all its dependencies.
    return $this->importRemoteEntityDependencies($contenthub_entity, $dependencies);
  }

  /**
   * Saves the current Drupal Entity and all its dependencies.
   *
   * This method is not to be used alone but to be used from
   * importRemoteEntity() method, which is why it is private.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $contenthub_entity
   *   The Content Hub Entity.
   * @param array $dependencies
   *   An array of ContentHubEntityDependency objects.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|null
   *   The Drupal entity being created.
   */
  private function importRemoteEntityDependencies(ContentHubEntityDependency $contenthub_entity, array &$dependencies) {
    // Un-managed assets are also pre-dependencies for an entity and they would
    // need to be saved before we can create the current entity.
    $this->saveUnManagedAssets($contenthub_entity);

    // Create pre-dependencies.
    foreach ($contenthub_entity->getDependencyChain() as $uuid) {
      $content_hub_entity_dependency = isset($dependencies[$uuid]) ? $dependencies[$uuid] : FALSE;
      if ($content_hub_entity_dependency && !isset($content_hub_entity_dependency->__processed) && $content_hub_entity_dependency->getRelationship() == ContentHubEntityDependency::RELATIONSHIP_INDEPENDENT) {
        $dependencies[$uuid]->__processed = TRUE;
        $this->importRemoteEntityDependencies($content_hub_entity_dependency, $dependencies);
      }
    }

    // Check already imported entity, that auto import is available or not.
    $trackingEntity = $this->contentHubEntitiesTracking->loadImportedByUuid($contenthub_entity->getRawEntity()->getUuid());
    if ($trackingEntity && $trackingEntity->isAutoUpdateDisabled()) {
      return new JsonResponse(NULL);
    }

    // Now that we have created all its pre-dependencies, create the current
    // Drupal entity.
    $response = $this->importRemoteEntityNoDependencies($contenthub_entity);

    // Create post-dependencies.
    foreach ($contenthub_entity->getDependencyChain() as $uuid) {
      $content_hub_entity_dependency = isset($dependencies[$uuid]) ? $dependencies[$uuid] : FALSE;
      if ($content_hub_entity_dependency && !isset($content_hub_entity_dependency->__processed) && $content_hub_entity_dependency->getRelationship() == ContentHubEntityDependency::RELATIONSHIP_DEPENDENT) {
        $dependencies[$uuid]->__processed = TRUE;
        $this->importRemoteEntityDependencies($content_hub_entity_dependency, $dependencies);
      }
    }
    return $response;
  }

  /**
   * Saves Unmanaged Assets.
   */
  private function saveUnManagedAssets($contenthub_entity) {
    // @TODO: Implement this function to save unmanaged files.
  }

  /**
   * Saves an Entity without taking care of dependencies.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $contenthub_entity
   *   The Content Hub Entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The Response.
   *
   * @throws \Exception
   *   Throws exception in certain cases.
   */
  private function importRemoteEntityNoDependencies(ContentHubEntityDependency $contenthub_entity) {
    // Import the entity.
    $entity_type = $contenthub_entity->getRawEntity()->getType();
    $class = \Drupal::entityTypeManager()->getDefinition($entity_type)->getClass();

    // Check if this dependency has originated in this site or not.
    $site_origin = $this->contentHubEntitiesTracking->getSiteOrigin();
    if ($contenthub_entity->getRawEntity()->getOrigin() == $site_origin) {
      $args = [
        '@type' => $contenthub_entity->getRawEntity()->getType(),
        '@uuid' => $contenthub_entity->getRawEntity()->getUuid(),
        '@origin' => $contenthub_entity->getRawEntity()->getOrigin(),
      ];
      $message = $this->t('Cannot save "@type" entity with uuid="@uuid". It has the same origin as this site: "@origin"', $args);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
      return $this->jsonErrorResponseMessage($message, FALSE, 400);
    }

    try {
      $entity = $this->serializer->deserialize($contenthub_entity->getRawEntity()->json(), $class, $this->format);
    }
    catch (\UnexpectedValueException $e) {
      $error = $e->getMessage();
      return $this->jsonErrorResponseMessage($error, FALSE, 400);
    }

    // If the entity cannot be serialized (we detected it cannot be saved
    // because it has missing information) then we should not try to save it.
    if (empty($entity)) {
      $message = $this->t('Entity (type = "%type", uuid = "%uuid") cannot be saved.', [
        '%type' => $entity_type,
        '%uuid' => $contenthub_entity->getUuid(),
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
      return new JsonResponse(NULL);
    }

    // Finally Save the Entity.
    $transaction = $this->database->startTransaction();
    try {
      // Add synchronization flag.
      $entity->__contenthub_entity_syncing = TRUE;

      if ($entity instanceof ContentEntityInterface && $entity->hasField('path')) {
        $languages = $entity->getTranslationLanguages();
        /** @var \Drupal\Core\Path\AliasManagerInterface $alias_manager */
        $alias_manager = \Drupal::service('path.alias_manager');
        foreach ($languages as $language) {
          $entity = $entity->getTranslation($language->getId());
          $path = $entity->get('path')->first()->getValue();
          if (!empty($path['pid']) || !isset($path['alias'])) {
            continue;
          }
          $raw_path = $entity->id() ? '/' . $entity->toUrl()->getInternalPath() : $alias_manager->getPathByAlias($path['alias'], $path['langcode']);
          if (!$raw_path) {
            continue;
          }
          $query = \Drupal::database()->select('url_alias', 'ua')
            ->fields('ua', ['pid']);
          $query->condition('ua.source', $raw_path);
          $alias = $query->execute()->fetchObject();
          if (!isset($alias->pid)) {
            continue;
          }
          $path['pid'] = $alias->pid;
          $path['source'] = $raw_path;
          $entity->set('path', [$path]);
        }
      }
      // Check if there exists a local "redirect" entity with the same hash.
      if ($entity->getEntityTypeId() === 'redirect' && $entity->getHash()) {
        /** @var \Drupal\redirect\Entity\Redirect $local_redirect_entity */
        $local_redirect_entity = reset(\Drupal::entityTypeManager()->getStorage('redirect')->loadByProperties(['hash' => $entity->getHash()]));
        if ($local_redirect_entity && $local_redirect_entity->uuid() !== $entity->uuid()) {
          $this->loggerFactory->get('acquia_contenthub')
            ->debug('An existing redirect entity with the same "hash" was found and overwritten by the one coming from Content Hub. Old UUID = "%old_uuid", New UUID = "%new_uuid", source = "%source", old destination = "%old_destination", new destination = "%new_destination".', [
              '%old_uuid' => $local_redirect_entity->uuid(),
              '%new_uuid' => $entity->uuid(),
              '%source' => $local_redirect_entity->getSourceUrl(),
              '%old_destination' => $local_redirect_entity->getRedirectUrl()
                ->toUriString(),
              '%new_destination' => $entity->getRedirectUrl()->toUriString(),
            ]);
          $local_redirect_entity->delete();
        }
      }

      // Save the entity.
      $entity->save();
      // Remove synchronization flag.
      unset($entity->__contenthub_entity_syncing);

      // Save this entity in the tracking for importing entities.
      $is_new_entity = $this->trackImportedEntity($contenthub_entity);

      // If this is a post-dependency (paragraphs or field collections), then
      // we will need to update the host entity ONLY if we are creating this
      // entity for the first time.
      // If we are updating a paragraph, the reference is already set.
      if ($is_new_entity && $contenthub_entity->isEntityDependent()) {
        $this->updateHostEntity($entity);
      }

      // Do we need to create an path alias?
      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('pathauto')) {
        /** @var \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher */
        $accountSwitcher = \Drupal::service('account_switcher');
        $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadByProperties([
          'is_admin' => TRUE,
        ]);
        $role = reset($roles);
        if ($role) {
          $renderUser = new ContentHubUserSession($role->id());
          $accountSwitcher->switchTo($renderUser);
          try {
            /** @var \Drupal\pathauto\AliasStorageHelperInterface $alias_storage_helper */
            $alias_storage_helper = \Drupal::service('pathauto.alias_storage_helper');
            $languages = $entity->getTranslationLanguages();
            foreach ($languages as $language) {
              $entity = $entity->getTranslation($language->getId());
              if ($entity && $entity->hasField('path')) {
                $path = $entity->get('path')->getValue();
                if ($path = reset($path)) {
                  $path['source'] = empty($path['source']) ? '/' . $entity->toUrl()->getInternalPath() : $path['source'];
                  $path['language'] = isset($path['langcode']) ? $path['langcode'] : $language->getId();
                  $alias_storage_helper->save($path);
                }
              }
            }
          }
          catch (\Exception $e) {
            $this->loggerFactory->get('acquia_contenthub')
              ->debug('Could not generate path alias for (%entity_type, %entity_id). Error message: %message', [
                '%entity_type' => $entity->getEntityTypeId(),
                '%entity_id' => $entity->id(),
                '%message' => $e->getMessage(),
              ]);
          }
          $accountSwitcher->switchBack();
        }
      }

    }
    catch (\Exception $e) {
      $transaction->rollback();
      $this->loggerFactory->get('acquia_contenthub')->error($e->getMessage());
      throw $e;
    }

    $serialized_entity = $this->serializer->normalize($entity, 'json');
    return new JsonResponse($serialized_entity);
  }

  /**
   * Updates the reference in the host entity to point to the dependent entity.
   *
   * In cases of dependent entities (paragraphs and field collections), we need
   * to update the reference in the host entity to point to the dependent entity
   * after the dependent entity has been saved.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A Drupal entity interface.
   */
  private function updateHostEntity(EntityInterface $entity) {
    switch ($entity->getEntityTypeId()) {
      case 'paragraph':
        $host_entity = $entity->getParentEntity();
        // Assuming single parenthood.
        $field_paragraph = $entity->get('parent_field_name')->getString();
        $host_entity->{$field_paragraph}->appendItem($entity);
        // Add synchronization flag.
        $host_entity->__contenthub_entity_syncing = TRUE;
        // Save the host entity.
        $host_entity->save();
        // Remove synchronization flag.
        unset($host_entity->__contenthub_entity_syncing);
        break;
    }
  }

  /**
   * Provides a JSON Response Message.
   *
   * @param string $message
   *   The message to print.
   * @param bool $status
   *   The status message.
   * @param int $status_code
   *   The HTTP Status code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON Response.
   */
  private function jsonErrorResponseMessage($message, $status, $status_code = 400) {
    // If the Entity is not found in Content Hub then return a 404 Not Found.
    $json = [
      'status' => $status,
      'message' => $message,
    ];
    return new JsonResponse($json, $status_code);
  }

  /**
   * Save this entity in the Tracking table.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $contenthub_entity
   *   The Content Hub Entity.
   *
   * @return bool
   *   TRUE if entity is new, FALSE otherwise.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function trackImportedEntity(ContentHubEntityDependency $contenthub_entity) {
    $cdf = (array) $contenthub_entity->getRawEntity();
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByUuid($cdf['uuid']);
    // If already exist, update some fields and exit.
    if ($imported_entity) {
      // Set status to "auto-update" only when the entity is independent.
      if (!$imported_entity->isDependent()) {
        $imported_entity->setAutoUpdate();
      }
      $imported_entity->setModified($cdf['modified']);
      $this->saveImportedEntity();
      return FALSE;
    }
    // If the entity is new, create a new imported entity.
    $entity = $this->entityRepository->loadEntityByUuid($cdf['type'], $cdf['uuid']);
    if ($entity) {
      $this->contentHubEntitiesTracking->setImportedEntity(
        $cdf['type'],
        $entity->id(),
        $cdf['uuid'],
        $cdf['modified'],
        $cdf['origin']
      );
      if ($contenthub_entity->isEntityDependent()) {
        $this->contentHubEntitiesTracking->setDependent();
      }
      $this->saveImportedEntity();
      return TRUE;
    }
    // We should never reach this far.
    $this->loggerFactory->get('acquia_contenthub')->error('Error trying to track imported entity with uuid=%uuid, type=%type. Check if the entity exists and is being tracked properly.', [
      '%type' => $cdf['type'],
      '%uuid' => $cdf['uuid'],
    ]);

    return FALSE;
  }

  /**
   * Save the imported entity.
   */
  private function saveImportedEntity() {
    // Now save the entity.
    $success = $this->contentHubEntitiesTracking->save();

    // Log event.
    if (!$success) {
      $args = [
        '%type' => $this->contentHubEntitiesTracking->getEntityType(),
        '%uuid' => $this->contentHubEntitiesTracking->getUuid(),
      ];
      $message = $this->t('Imported entity type = %type with uuid=%uuid could not be saved in the tracking table.', $args);
    }
    else {
      $args = [
        '%type' => $this->contentHubEntitiesTracking->getEntityType(),
        '%uuid' => $this->contentHubEntitiesTracking->getUuid(),
      ];
      $message = $this->t('Saving %type entity with uuid=%uuid. Tracking imported entity with auto updates.', $args);
    }
    $this->loggerFactory->get('acquia_contenthub')->debug($message);
  }

  /**
   * Act on the entity's update action.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that is being updated.
   */
  public function entityUpdate(EntityInterface $entity) {
    // Early return, if already sync'ing.
    if (!empty($entity->__contenthub_entity_syncing)) {
      return;
    }
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity->getEntityTypeId(), $entity->id());
    // Early return, if not an imported entity or not pending sync.
    if (!$imported_entity || !$imported_entity->isPendingSync()) {
      return;
    }

    // Otherwise, re-import the entity.
    $this->importRemoteEntity($imported_entity->getUuid(), $entity);
  }

  /**
   * Returns the entity's root ancestor's imported entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The current (child) entity.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking|bool
   *   The root ancestor's ContentHubEntitiesTracking object.
   */
  private function findRootAncestorImportEntity(EntityInterface $entity) {
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity->getEntityTypeId(), $entity->id());
    if (!$imported_entity || !$imported_entity->isDependent()) {
      return $imported_entity;
    }

    return $this->findRootAncestorImportEntity($entity->getParentEntity());
  }

  /**
   * Act on the entity's delete action.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that is being deleted.
   */
  public function entityDelete(EntityInterface $entity) {
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity->getEntityTypeId(), $entity->id());
    if (!$imported_entity) {
      return;
    }
    $imported_entity->delete();
  }

  /**
   * Add the UUID to the import queue.
   *
   * @param string $uuid
   *   The UUID of the Entity to save.
   * @param bool $include_dependencies
   *   TRUE if we want to save all its dependencies, FALSE otherwise.
   * @param string $author
   *   The UUID of the author (user) that will own the entity.
   * @param int $status
   *   The publishing status of the entity (Applies to nodes).
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A valid response.
   */
  public function addEntityToImportQueue($uuid, $include_dependencies = TRUE, $author = NULL, $status = 0) {
    $item = (object) ['data' => []];
    $item->data[] = new ImportQueueItem($uuid, $include_dependencies, $author, $status);
    $queue = $this->queue->get('acquia_contenthub_import_queue');

    if ($queue->createItem($item)) {
      return new JsonResponse(['status' => 200, 'message' => $uuid . ' added to the queue'], 200);
    }

    return $this->jsonErrorResponseMessage('Unable to add ' . $uuid . ' to the import queue', FALSE);
  }

}
