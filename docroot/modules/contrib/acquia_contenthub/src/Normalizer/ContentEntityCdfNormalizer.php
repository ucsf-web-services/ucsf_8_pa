<?php

namespace Drupal\acquia_contenthub\Normalizer;

use Drupal\acquia_contenthub\ContentHubEntityEmbedHandler;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Acquia\ContentHubClient\Asset;
use Acquia\ContentHubClient\Attribute;
use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\path\Plugin\Field\FieldType\PathFieldItemList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\acquia_contenthub\ContentHubException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityInterface;
use Acquia\ContentHubClient\Entity as ContentHubEntity;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Component\Uuid\Uuid;
use Drupal\acquia_contenthub\EntityManager;
use Drupal\acquia_contenthub\ContentHubInternalRequest;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Entity\EntityInterface;
use Drupal\acquia_contenthub\ContentHubEntityLinkFieldHandler;

/**
 * Converts the Drupal entity object to a Acquia Content Hub CDF array.
 */
class ContentEntityCdfNormalizer extends NormalizerBase {

  use StringTranslationTrait;

  /**
   * The format that the Normalizer can handle.
   *
   * @var string
   */
  protected $format = 'acquia_contenthub_cdf';

  /**
   * Base url.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

  /**
   * The Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The content entity view modes normalizer.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractor
   */
  protected $contentEntityViewModesNormalizer;

  /**
   * The module handler service to create alter hooks.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected  $entityRepository;

  /**
   * Base root path of the application.
   *
   * @var string
   */
  protected $baseRoot;

  /**
   * The Basic HTTP Kernel to make requests.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $kernel;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The entity manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The account switcher service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubInternalRequest
   */
  protected $internalRequest;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Translation Manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('acquia_contenthub.normalizer.content_entity_view_modes_extractor'),
      $container->get('module_handler'),
      $container->get('entity.repository'),
      $container->get('http_kernel.basic'),
      $container->get('renderer'),
      $container->get('acquia_contenthub.entity_manager'),
      $container->get('entity_type.manager'),
      $container->get('acquia_contenthub.internal_request'),
      $container->get('language_manager')
    );
  }

  /**
   * Constructs an ContentEntityNormalizer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractorInterface $content_entity_view_modes_normalizer
   *   The content entity view modes normalizer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to create alter hooks.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
   *   The Kernel Interface.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer Interface.
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\acquia_contenthub\ContentHubInternalRequest $internal_request
   *   The Content Hub Internal Request Service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The Language Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ContentEntityViewModesExtractorInterface $content_entity_view_modes_normalizer, ModuleHandlerInterface $module_handler, EntityRepositoryInterface $entity_repository, HttpKernelInterface $kernel, RendererInterface $renderer, EntityManager $entity_manager, EntityTypeManagerInterface $entity_type_manager, ContentHubInternalRequest $internal_request, LanguageManagerInterface $language_manager) {
    global $base_url;
    $this->baseUrl = $base_url;
    $this->config = $config_factory;
    $this->contentEntityViewModesNormalizer = $content_entity_view_modes_normalizer;
    $this->moduleHandler = $module_handler;
    $this->entityRepository = $entity_repository;
    $this->kernel = $kernel;
    $this->renderer = $renderer;
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->internalRequest = $internal_request;
    $this->languageManager = $language_manager;

    // Setting this property only if content_translation is enabled.
    if ($this->moduleHandler->moduleExists('content_translation')) {
      $this->translationManager = \Drupal::getContainer()->get("content_translation.manager");
    }
  }

  /**
   * Return the global base_root variable that is defined by Drupal.
   *
   * We set this to a function so it can be overridden in a PHPUnit test.
   *
   * @return string
   *   Return global base_root variable.
   */
  public function getBaseRoot() {
    if (isset($GLOBALS['base_root'])) {
      return $GLOBALS['base_root'];
    }
    return '';
  }

  /**
   * Normalizes an object into a set of arrays/scalars.
   *
   * @param object $entity
   *   Object to normalize. Due to the constraints of the class, we know that
   *   the object will be of the ContentEntityInterface type.
   * @param string $format
   *   The format that the normalization result will be encoded as.
   * @param array $context
   *   Context options for the normalizer.
   *
   * @return array|string|bool|int|float|null
   *   Return normalized data.
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    // Exit if the class does not support normalizing to the given format.
    if (!$this->supportsNormalization($entity, $format)) {
      return NULL;
    }

    // Creating a fake user account to give as context to the normalization.
    $account = new ContentHubUserSession($this->config->get('acquia_contenthub.entity_config')->get('user_role'));
    $context += ['account' => $account];

    // Checking for entity access permission to this particular account.
    $entity_access = $entity->access('view', $account, TRUE);
    if (!$entity_access->isAllowed()) {
      return NULL;
    }

    // By executing the rendering here with this cache contexts, we are bubbling
    // it up to the dynamic page cache so that it varies by the query param
    // include_references. Do not remove.
    $cache = ['#cache' => ['contexts' => ['url.query_args:include_references']]];
    $this->renderer->renderPlain($cache);

    // Add query params to the context.
    $current_uri = \Drupal::request()->getRequestUri();
    $uri = UrlHelper::parse($current_uri);
    $context += ['query_params' => $uri['query']];

    // Set our required CDF properties.
    $entity_type_id = $context['entity_type'] = $entity->getEntityTypeId();
    $entity_uuid = $entity->uuid();
    $origin = $this->config->get('acquia_contenthub.admin_settings')->get('origin');

    // Allow other modules to intercept and do changes to the drupal entity
    // before it is converted to CDF format.
    $this->moduleHandler->alter('acquia_contenthub_drupal_to_cdf', $entity_type_id, $entity);

    // Required Created field.
    if ($entity->hasField('created') && $entity->get('created')) {
      $created = date('c', $entity->get('created')->getValue()[0]['value']);
    }
    else {
      $created = date('c');
    }

    // Required Modified field.
    if ($entity->hasField('changed') && $entity->get('changed')) {
      $modified = date('c', $entity->get('changed')->getValue()[0]['value']);
    }
    else {
      $modified = date('c');
    }

    // Base Root Path.
    $base_root = $this->getBaseRoot();

    // Initialize Content Hub entity.
    $contenthub_entity = new ContentHubEntity();
    $contenthub_entity
      ->setUuid($entity_uuid)
      ->setType($entity_type_id)
      ->setOrigin($origin)
      ->setCreated($created)
      ->setModified($modified);

    if ($view_modes = $this->contentEntityViewModesNormalizer->getRenderedViewModes($entity)) {
      $contenthub_entity->setMetadata([
        'base_root' => $base_root,
        'view_modes' => $view_modes,
      ]);
    }

    // We have to iterate over the entity translations and add all the
    // translations versions.
    $languages = $entity->getTranslationLanguages();
    foreach ($languages as $language) {
      $langcode = $language->getId();
      $localized_entity = $entity->getTranslation($langcode);

      // If content_translation is enabled, then check whether the current
      // translation revision of the content has been published.
      if (!empty($this->translationManager) && $this->translationManager->isEnabled($entity_type_id, $entity->bundle())) {
        /** @var \Drupal\content_translation\ContentTranslationMetadataWrapperInterface $translation_metadata */
        $translation_metadata = $this->translationManager->getTranslationMetadata($localized_entity);
        if (!$translation_metadata->isPublished()) {
          continue;
        }
      }

      $contenthub_entity = $this->addFieldsToContentHubEntity($contenthub_entity, $localized_entity, $langcode, $context);
    }

    // Allow other modules to intercept and modify the CDF entity after it has
    // been normalized and before it is sent to Content Hub.
    $this->moduleHandler->alter('acquia_contenthub_cdf_from_drupal', $contenthub_entity);

    // Create the array of normalized fields, starting with the URI.
    $normalized = [
      'entities' => [$contenthub_entity],
    ];

    // Add all references to it if the include_references is true.
    if (!empty($context['query_params']['include_references']) && $context['query_params']['include_references'] == 'true') {

      $referenced_entities = [];
      $referenced_entities = $this->getMultilevelReferencedFields($entity, $referenced_entities, $context);
      $referenced_entities = array_values($referenced_entities);

      foreach ($referenced_entities as $entity) {
        // Only proceed to add the dependency if:
        // - Entity is not a node and it is not translatable.
        // - Entity is a node, its not translatable and it is published.
        // - Entity is translatable and has at least one published translation.
        if (!$this->entityManager->isPublished($entity)) {
          continue;
        }

        // Generate our URL where the isolated rendered view mode lives.
        // This is the best way to really make sure the content in Content Hub
        // and the content shown to any user is 100% the same.
        try {
          // Obtain the Entity CDF by making an hmac-signed internal request.
          $context['query_params']['include_references'] = 'false';
          $referenced_entity_list_cdf = $this->normalize($entity, $format, $context);
          $referenced_entity_list_cdf = array_pop($referenced_entity_list_cdf);
          if (is_array($referenced_entity_list_cdf)) {
            foreach ($referenced_entity_list_cdf as $referenced_entity_cdf) {
              $normalized['entities'][] = $referenced_entity_cdf;
            }
          }
        }
        catch (\Exception $e) {
          // Do nothing, route does not exist.
        }
      }
    }

    return $normalized;

  }

  /**
   * Get fields from given entity.
   *
   * Get the fields from a given entity and add them to the given content hub
   * entity object.
   *
   * @param \Acquia\ContentHubClient\Entity $contenthub_entity
   *   The Content Hub Entity that will contain all the Drupal entity fields.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Drupal Entity.
   * @param string $langcode
   *   The language that we are parsing.
   * @param array $context
   *   Additional Context such as the account.
   *
   * @return \Acquia\ContentHubClient\Entity
   *   The Content Hub Entity with all the data in it.
   *
   * @throws \Drupal\acquia_contenthub\ContentHubException
   *   The Exception will be thrown if something is going awol.
   */
  protected function addFieldsToContentHubEntity(ContentHubEntity $contenthub_entity, ContentEntityInterface $entity, $langcode = 'und', array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemListInterface[] $fields */
    $fields = $entity->getFields();

    // Get our field mapping. This maps drupal field types to Content Hub
    // attribute types.
    $type_mapping = $this->getFieldTypeMapping($entity);

    // Ignore the entity ID and revision ID.
    // Excluded comes here.
    $excluded_fields = $this->excludedProperties($entity);
    foreach ($fields as $name => $field) {
      // Continue if this is an excluded field or the current user does not
      // have access to view it.
      if (in_array($field->getFieldDefinition()->getName(), $excluded_fields) || !$field->access('view', $context['account'])) {
        continue;
      }

      // Get the plain version of the field in regular json.
      if ($name === 'metatag') {
        $serialized_field = $this->serializer->normalize($field, 'json', $context);
      }
      else {
        $serialized_field = $field->getValue();
      }
      $items = $serialized_field;

      // Given that vocabularies are configuration entities, they are not
      // supported in Content Hub. Instead we use the vocabulary machine name
      // as mechanism to syndicate and import them in the right vocabulary.
      if ($name === 'vid' && $entity->getEntityTypeId() === 'taxonomy_term') {
        // Initialize vocabulary attribute if it doesn't exist yet.
        if (!$contenthub_entity->getAttribute('vocabulary')) {
          $attribute = new Attribute(Attribute::TYPE_STRING);
          $attribute->setValue($items[0]['target_id'], $langcode);
          $contenthub_entity->setAttribute('vocabulary', $attribute);
        }
        else {
          $contenthub_entity->setAttributeValue('vocabulary', $items[0]['target_id'], $langcode);
        }
        continue;
      }

      // To make it work with Paragraphs, we are converting the field
      // 'parent_id' to 'parent_uuid' because Content Hub cannot deal with
      // entity_id information.
      if ($name === 'parent_id' && $entity->getEntityTypeId() === 'paragraph') {
        $attribute = new Attribute(Attribute::TYPE_STRING);
        $parent_id = $items[0]['value'];
        $parent_type = $fields['parent_type']->getValue()[0]['value'];
        $parent = $this->entityTypeManager->getStorage($parent_type)->load($parent_id);
        $parent_uuid = $parent->uuid();
        $attribute->setValue($parent_uuid, $langcode);
        $contenthub_entity->setAttribute('parent_uuid', $attribute);
        continue;
      }

      if ($name == 'bundle' && $entity->getEntityTypeId() === 'media') {
        $attribute = new Attribute(Attribute::TYPE_ARRAY_STRING);
        $attribute->setValue([$entity->bundle()], $langcode);
        $contenthub_entity->setAttribute('bundle', $attribute);
        continue;
      }

      // Try to map it to a known field type.
      $field_type = $field->getFieldDefinition()->getType();
      // Go to the fallback data type when the field type is not known.
      $type = $type_mapping['fallback'];
      if (isset($type_mapping[$name])) {
        $type = $type_mapping[$name];
      }
      elseif (isset($type_mapping[$field_type])) {
        // Set it to the fallback type which is string.
        $type = $type_mapping[$field_type];
      }

      if ($type == NULL) {
        continue;
      }

      $values = [];
      if ($field instanceof EntityReferenceFieldItemListInterface) {

        // Get taxonomy parent terms.
        if ($name === 'parent' && $entity->getEntityTypeId() === 'taxonomy_term') {
          $storage = $this->entityTypeManager->getStorage('taxonomy_term');
          $referenced_entities = $storage->loadParents($entity->id());
        }
        else {
          /** @var \Drupal\Core\Entity\EntityInterface[] $referenced_entities */
          $referenced_entities = $field->referencedEntities();
        }

        foreach ($referenced_entities as $key => $referenced_entity) {
          // In the case of images/files, etc... we need to add the assets.
          $file_types = [
            'image',
            'file',
            'video',
          ];
          $type_names = [
            'type',
            'bundle',
          ];

          // Special case for type as we do not want the reference for the
          // bundle. In additional to the type field a media entity has a
          // bundle field which stores a media bundle configuration entity UUID.
          if (in_array($name, $type_names, TRUE) && $referenced_entity instanceof ConfigEntityBase) {
            $values[$langcode][] = $referenced_entity->id();
          }
          elseif (in_array($field_type, $file_types)) {
            // If this is a file type, then add the asset to the CDF.
            $uuid_token = '[' . $referenced_entity->uuid() . ']';
            $asset_url = file_create_url($referenced_entity->getFileUri());
            $asset = new Asset();
            $asset->setUrl($asset_url);
            $asset->setReplaceToken($uuid_token);
            $contenthub_entity->addAsset($asset);

            // Now add the value.
            // Notice that we are including the "alt" and "title" attributes
            // from the file entity in the field data.
            $data = [
              'alt' => isset($items[$key]['alt']) ? $items[$key]['alt'] : '',
              'title' => isset($items[$key]['title']) ? $items[$key]['title'] : '',
              'target_uuid' => $uuid_token,
            ];
            $values[$langcode][] = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
          }
          else {
            $values[$langcode][] = $referenced_entity->uuid();
          }
        }
      }
      else {
        // If there's nothing in this field, just set it to NULL.
        if ($items == NULL) {
          $values[$langcode] = NULL;
        }
        else {
          // Only if it is a link type.
          if ($link_field = ContentHubEntityLinkFieldHandler::load($field)->validate()) {
            $items = $link_field->normalizeItems($items);
          }

          // Loop over the items to get the values for each field.
          foreach ($items as $item) {
            // Hotfix.
            // @TODO: Find a better solution for this.
            if (isset($item['_attributes'])) {
              unset($item['_attributes']);
            }
            $keys = is_array($item) ? array_keys($item) : [];
            if (count($keys) == 1 && isset($item['value'])) {
              $value = $item['value'];
            }
            else {
              if ($field instanceof PathFieldItemList) {
                $item = $field->first()->getValue();
                $item['pid'] = "";
                $item['source'] = "";
              }
              $value = json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            }
            $values[$langcode][] = $value;
          }
        }
      }
      try {
        $attribute = new Attribute($type);
      }
      catch (\Exception $e) {
        $args['%type'] = $type;
        $message = new FormattableMarkup('No type could be registered for %type.', $args);
        throw new ContentHubException($message);
      }

      if (strstr($type, 'array')) {
        $attribute->setValues($values);
      }
      else {
        $value = array_pop($values[$langcode]);
        $attribute->setValue($value, $langcode);
      }

      // If attribute exists already, append to the existing values.
      if (!empty($contenthub_entity->getAttribute($name))) {
        $existing_attribute = $contenthub_entity->getAttribute($name);
        $this->appendToAttribute($existing_attribute, $attribute->getValues());
        $attribute = $existing_attribute;
      }

      // Add it to our contenthub entity.
      $contenthub_entity->setAttribute($name, $attribute);
    }

    // Allow alterations of the CDF to happen.
    $context['entity'] = $entity;
    $context['langcode'] = $langcode;
    $this->moduleHandler->alter('acquia_contenthub_cdf', $contenthub_entity, $context);

    // Adds the entity URL to CDF.
    $value = NULL;
    if (empty($contenthub_entity->getAttribute('url'))) {
      global $base_path;
      switch ($entity->getEntityTypeId()) {
        case 'file':
          $value = file_create_url($entity->getFileUri());
          $filepath_attribute = new Attribute(Attribute::TYPE_STRING);
          $contenthub_entity->setAttribute('_filepath', $filepath_attribute->setValue($entity->getFileUri()));
          break;

        default:
          // Get entity URL.
          if (!$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
            $url = $entity->toUrl();
            $url->setAbsolute(TRUE);
            $value = $url->toString();
          }
          break;
      }
      if (isset($value)) {
        $url_attribute = new Attribute(Attribute::TYPE_STRING);
        $contenthub_entity->setAttribute('url', $url_attribute->setValue($value, $langcode));
      }
    }

    return $contenthub_entity;
  }

  /**
   * Get entity reference fields.
   *
   * Get the fields from a given entity and add them to the given content hub
   * entity object.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Drupal Entity.
   * @param array $context
   *   Additional Context such as the account.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   All referenced entities.
   */
  public function getReferencedFields(ContentEntityInterface $entity, array $context = []) {
    /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig[] $content_hub_entity_type_ids */
    $content_hub_entity_type_ids = $this->entityManager->getContentHubEntityTypeConfigurationEntities();
    $bundle_key = $this->entityTypeManager->getDefinition($entity->getEntityTypeId())->getKey('bundle');

    // Check if 'entity_embed' exists.
    $exists_entity_embed = \Drupal::moduleHandler()->moduleExists('entity_embed');

    $referenced_entities = [];

    // If it is a taxonomy term, check for parent information.
    // We are considering the parent does not change per translation.
    // https://www.drupal.org/node/2543726
    if ($entity->getEntityTypeId() === 'taxonomy_term') {
      $parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadParents($entity->id());
      foreach ($parents as $key => $parent) {
        if ($this->entityManager->isEligibleDependency($parent)) {
          $referenced_entities[$parent->uuid()] = $parent;
        }
      }
    }

    // Ignore the entity ID and revision ID.
    // Excluded comes here.
    $excluded_fields = $this->excludedProperties($entity);

    // Find all languages for the current entity.
    $languages = $entity->getTranslationLanguages();

    // Go through all the languages.
    // We have to iterate over the entity translations and add all the
    // references that are included per translation.
    foreach ($languages as $language) {
      $langcode = $language->getId();
      $localized_entity = $entity->getTranslation($langcode);

      /** @var \Drupal\Core\Field\FieldItemListInterface[] $fields */
      $fields = $localized_entity->getFields();
      foreach ($fields as $name => $field) {
        // Continue if this is an excluded field or the current user does not
        // have access to view it.
        $context['account'] = isset($context['account']) ? $context['account'] : NULL;
        if (in_array($field->getFieldDefinition()->getName(), $excluded_fields) || !$field->access('view', $context['account']) || $name === $bundle_key) {
          continue;
        }

        if ($exists_entity_embed) {
          $entity_embed_handler = new ContentHubEntityEmbedHandler($field);
          if ($entity_embed_handler->isProcessable()) {
            $embed_entities = $entity_embed_handler->getReferencedEntities();
            foreach ($embed_entities as $uuid => $embedded_entity) {
              $referenced_entities[$uuid] = $embedded_entity;
            }
          }
        }

        if ($link_field = ContentHubEntityLinkFieldHandler::load($field)->validate()) {
          $link_entities = $link_field->getReferencedEntities($field->getValue());
          foreach ($link_entities as $link_entity) {
            $referenced_entities[$link_entity->uuid()] = $link_entity;
          }
        }

        if ($field instanceof EntityReferenceFieldItemListInterface && !$field->isEmpty()) {
          // Before checking each individual target entity, verify if we can skip
          // all of them at once by checking if none of the target bundles are set
          // to be exported in Content Hub configuration.
          $skip_entities = FALSE;
          $settings = $field->getFieldDefinition()->getSettings();
          $target_type = isset($settings['target_type']) ? $settings['target_type'] : NULL;
          if (isset($settings['handler_settings']['target_bundles'])) {
            $target_bundles = $settings['handler_settings']['target_bundles'];
          }
          else {
            // Certain field types such as file or user do not have target
            // bundles. In this case, we have to inspect each referenced entity
            // and collect all their bundles.
            $target_bundles = [];
            $field_entities = $field->referencedEntities();
            foreach ($field_entities as $field_entity) {
              if ($field_entity instanceof EntityInterface) {
                $target_bundles[] = $field_entity->bundle();
              }
            }
            $target_bundles = array_unique($target_bundles);
            if (empty($target_bundles)) {
              // In cases where there is no bundle defined, the default bundle
              // name is the same as the entity type, e.g. 'file', 'user'.
              $target_bundles = [$target_type];
            }
          }
          // Compare the list of bundles with the bundles set to be exportable in
          // the Content Hub Entity configuration form.
          if (!empty($target_type)) {
            $skip_entities = TRUE;
            foreach ($target_bundles as $target_bundle) {
              // If there is at least one bundle set to be exportable, it means
              // this field cannot be skipped.
              if (isset($content_hub_entity_type_ids[$target_type]) && $content_hub_entity_type_ids[$target_type]->isEnableIndex($target_bundle)) {
                $skip_entities = FALSE;
                break;
              }
            }
          }

          // Check each referenced entity to see if it should be exported.
          if (!$skip_entities) {
            $field_entities = $field->referencedEntities();
            foreach ($field_entities as $key => $field_entity) {
              if ($this->entityManager->isEligibleDependency($field_entity)) {
                /** @var \Drupal\Core\Entity\EntityInterface[] $referenced_entities */
                $referenced_entities[$field_entity->uuid()] = $field_entity;
              }
            }
          }
        }
      }
    }

    return $referenced_entities;
  }

  /**
   * Get multilevel entity reference fields.
   *
   * Get the fields from a given entity and add them to the given content hub
   * entity object. This also includes dependencies of the dependencies.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Drupal Entity.
   * @param array $referenced_entities
   *   The list of Multilevel referenced entities. This must be passed as an
   *   initialized array.
   * @param array $context
   *   Additional Context such as the account.
   * @param int $depth
   *   The depth of the referenced entity (levels down from main entity).
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   All referenced entities.
   */
  public function getMultilevelReferencedFields(ContentEntityInterface $entity, array &$referenced_entities, array $context = [], $depth = 0) {
    $depth++;
    $maximum_depth = $this->config->get('acquia_contenthub.entity_config')->get('dependency_depth');
    $maximum_depth = is_int($maximum_depth) ? $maximum_depth : 3;

    // Collecting all referenced_entities UUIDs.
    $uuids = array_keys($referenced_entities);

    // Obtaining all the referenced entities for the current entity.
    $ref_entities = $this->getReferencedFields($entity, $context);
    foreach ($ref_entities as $uuid => $entity) {
      if (!in_array($uuid, $uuids)) {
        // @TODO: This if-condition is a hack to avoid Vocabulary entities.
        if ($entity instanceof ContentEntityInterface) {
          $referenced_entities[$uuid] = $entity;

          // Only search for dependencies if we are below the maximum depth
          // configured by the admin. If not set, a default of 3 will be used.
          if ($depth < $maximum_depth) {
            $this->getMultilevelReferencedFields($entity, $referenced_entities, $context, $depth);
          }
        }
      }
    }

    return $referenced_entities;

  }

  /**
   * Adds Content Hub Data to Drupal Entity Fields.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Drupal Entity.
   * @param \Acquia\ContentHubClient\Entity $contenthub_entity
   *   The Content Hub Entity.
   * @param string $langcode
   *   The language code.
   * @param array $context
   *   Context.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The Drupal Entity after integrating data from Content Hub.
   */
  protected function addFieldsToDrupalEntity(ContentEntityInterface $entity, ContentHubEntity $contenthub_entity, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, array $context = []) {
    /** @var \Drupal\Core\Field\FieldItemListInterface[] $fields */
    $fields = $entity->getFields();

    // Ignore the entity ID and revision ID.
    // Excluded comes here.
    $excluded_fields = $this->excludedProperties($entity);

    // Add the bundle key to be ignored as it should have already been assigned.
    $bundle_key = $this->entityTypeManager->getDefinition($entity->getEntityTypeId())->getKey('bundle');
    $excluded_fields[] = $bundle_key;

    // We ignore `langcode` selectively because und i.e LANGCODE_NOT_SPECIFIED
    // and zxx i.e LANGCODE_NOT_APPLICABLE content requires `langcode` field
    // to *not* be excluded for such content to be importable.
    if ($entity->hasTranslation($langcode)) {
      $excluded_fields[] = 'langcode';
    }

    // Iterate over all attributes.
    foreach ($contenthub_entity->getAttributes() as $name => $attribute) {

      $attribute = (array) $attribute;
      // If it is an excluded property, then skip it.
      if (in_array($name, $excluded_fields)) {
        continue;
      }

      // In the case of images/files, etc... we need to add the assets.
      $file_types = [
        'image',
        'file',
        'video',
      ];

      $field = isset($fields[$name]) ? $fields[$name] : NULL;
      if (isset($field)) {
        // Try to map it to a known field type.
        $field_type = $field->getFieldDefinition()->getType();
        $settings = $field->getFieldDefinition()->getSettings();
        $value = $attribute['value'][$langcode];
        $field->setValue([]);
        $field->setLangcode($langcode);

        if ($field instanceof EntityReferenceFieldItemListInterface) {
          $entity_type = $settings['target_type'];
          $field_item = NULL;
          foreach ($value as $item) {
            $item = json_decode($item, TRUE) ?: $item;
            if (in_array($field_type, $file_types)) {
              if (is_array($item) && isset($item['target_uuid'])) {
                $uuid = $this->removeBracketsUuid($item['target_uuid']);
                $referenced_entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
              }
              else {
                $uuid = $this->removeBracketsUuid($item);
                $referenced_entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
              }
              $field_item = $referenced_entity ? [
                'alt' => isset($item['alt']) ? $item['alt'] : ($settings['alt_field_required'] ? $referenced_entity->label() : ''),
                'title' => isset($item['title']) ? $item['title'] : ($settings['title_field_required'] ? $referenced_entity->label() : ''),
                'target_id' => $referenced_entity->id(),
              ] : NULL;
            }
            else {
              $uuid = $item;
              $referenced_entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
              $field_item = $referenced_entity;
            }
            if ($field_item) {
              $field->appendItem($field_item);
            }
          }
        }
        else {
          if ($field instanceof FieldItemListInterface && is_array($value)) {
            foreach ($value as $item) {
              // Only decode $item if it is a valid json string, otherwise just
              // assign the value as it comes.
              if (is_string($item) && isset($item[0]) && $item[0] === '{') {
                $decoded = json_decode($item, TRUE);
                if (json_last_error() === JSON_ERROR_NONE) {
                  $item = $decoded;
                  // Only do this if we are dealing with a link field type.
                  if ($link_field = ContentHubEntityLinkFieldHandler::load($field)->validate()) {
                    $item = $link_field->denormalizeItem($item);
                  }
                }
              }
              $field->appendItem($item);
            }
          }
          else {
            $field->setValue($value);
          }
        }
      }
    }

    return $entity;
  }

  /**
   * Append to existing values of Content Hub Attribute.
   *
   * @param \Acquia\ContentHubClient\Attribute $attribute
   *   The attribute.
   * @param array $values
   *   The attribute's values.
   */
  public function appendToAttribute(Attribute $attribute, array $values) {
    $old_values = $attribute->getValues();
    $values = array_merge($old_values, $values);
    $attribute->setValues($values);
  }

  /**
   * Retrieves the mapping for known data types to Content Hub's internal types.
   *
   * Inspired by the getFieldTypeMapping in search_api.
   *
   * Search API uses the complex data format to normalize the data into a
   * document-structure suitable for search engines. However, since content hub
   * for Drupal 8 just got started, it focusses on the field types for now
   * instead of on the complex data types. Changing this architecture would
   * mean that we have to adopt a very similar structure as can be seen in the
   * Utility class of Search API. That would also mean we no longer have to
   * explicitly support certain field types as they map back to the known
   * complex data types such as string, uri that are known in Drupal Core.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity to map fields to.
   *
   * @return string[]
   *   An array mapping all known (and supported) Drupal field types to their
   *   corresponding Content Hub data types. Empty values mean that fields of
   *   that type should be ignored by the Content Hub.
   *
   * @see hook_acquia_contenthub_field_type_mapping_alter()
   */
  public function getFieldTypeMapping(ContentEntityInterface $entity) {
    // Getting the bundle key.
    $bundle_key = $this->entityTypeManager->getDefinition($entity->getEntityTypeId())->getKey('bundle');
    $langcode_key = $this->entityTypeManager->getDefinition($entity->getEntityTypeId())->getKey('langcode');

    $mapping = [];
    // It's easier to write and understand this array in the form of
    // $default_mapping => [$data_types] and flip it below.
    $default_mapping = [
      'string' => [
        // These are special field names that we do not want to parse as
        // arrays.
        'title',
        $bundle_key,
        $langcode_key,
        // This is a special field that we will want to parse as string for now.
        // @TODO: Replace this to work with taxonomy_vocabulary entities.
        'vid',
      ],
      'array<string>' => [
        'fallback',
        'text_with_summary',
        'image',
        'file',
        'video',
      ],
      'array<reference>' => [
        'entity_reference',
        'entity_reference_revisions',
      ],
      'array<integer>' => [
        'integer',
        'timespan',
        'timestamp',
      ],
      'array<number>' => [
        'decimal',
        'float',
      ],
      // Types we know about but want/have to ignore.
      NULL => [
        'password',
      ],
      'array<boolean>' => [
        'boolean',
      ],
    ];

    foreach ($default_mapping as $contenthub_type => $data_types) {
      foreach ($data_types as $data_type) {
        $mapping[$data_type] = $contenthub_type;
      }
    }

    // Allow other modules to intercept and define what default type they want
    // to use for their data type.
    $this->moduleHandler->alter('acquia_contenthub_field_type_mapping', $mapping);

    return $mapping;
  }

  /**
   * Provides a list of entity properties that will be excluded from the CDF.
   *
   * When building the CDF entity for the Content Hub we are exporting Drupal
   * entities that will be imported by other Drupal sites, so nids, tids, fids,
   * etc. should not be transferred, as they will be different in different
   * Drupal sites. We are relying in Drupal <uuid>'s as the entity identifier.
   * So <uuid>'s will persist through the different sites.
   * (We will need to verify this claim!)
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   *
   * @return array
   *   An array of excluded properties.
   */
  protected function excludedProperties(ContentEntityInterface $entity) {
    $excluded_fields = [
      // Globally excluded fields (for all entity types).
      'global' => [
        // The following properties are always included in constructor, so we do
        // not need to check them again.
        'created',
        'changed',
        'uri',
        'uid',

        // Getting rid of workflow fields.
        'status',

        // Do not send revisions.
        'revision_uid',
        'revision_translation_affected',
        'revision_timestamp',

        // Translation fields.
        'content_translation_uid',

        // Do not include comments.
        'comment',
        'comment_count',
        'comment_count_new',

        // Do not include moderation state.
        'moderation_state',
      ],

      // Excluded fields for nodes.
      'node' => [
        // Getting rid of workflow fields.
        'sticky',
        'promote',
      ],
      'file' => [
        'url',
      ],
    ];

    $entity_type_id = $entity->getEntityTypeId();
    $entity_keys = $entity->getEntityType()->getKeys();

    // Ignore specific properties based on the entity type keys.
    $ignored_keys = ['uid', 'id', 'revision', 'uuid'];
    $excluded_keys = array_values(array_intersect_key($entity_keys, array_flip($ignored_keys)));

    // Provide default excluded properties per entity type.
    if (!isset($excluded_fields[$entity_type_id])) {
      $excluded_fields[$entity_type_id] = [];
    }
    $excluded = array_merge($excluded_fields['global'], $excluded_fields[$entity_type_id], $excluded_keys);

    $excluded_to_alter = [];

    // Allow users to define more excluded properties.
    // Allow other modules to intercept and define what default type they want
    // to use for their data type.
    $this->moduleHandler->alter('acquia_contenthub_exclude_fields', $excluded_to_alter, $entity);
    $excluded = array_merge($excluded, $excluded_to_alter);
    return array_filter($excluded);
  }

  /**
   * Denormalizes data back into an object of the given class.
   *
   * @param mixed $data
   *   Data to restore.
   * @param string $class
   *   The expected class to instantiate.
   * @param string $format
   *   Format the given data was extracted from.
   * @param array $context
   *   Options available to the denormalizer.
   *
   * @return array
   *   Returns denormalized data.
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $context += ['account' => NULL];

    // Exit if the class does not support denormalization of the given data,
    // class and format.
    if (!$this->supportsDenormalization($data, $class, $format)) {
      return NULL;
    }

    $contenthub_entity = new ContentHubEntity($data);

    // Allow other modules to intercept and do changes to the Content Hub CDF
    // before it is denormalized to a Drupal Entity.
    $this->moduleHandler->alter('acquia_contenthub_cdf_from_hub', $contenthub_entity);

    $entity_type = $contenthub_entity->getType();
    $bundle_key = $this->entityTypeManager->getDefinition($entity_type)->getKey('bundle');
    $bundle = $contenthub_entity->getAttribute($bundle_key) ? reset($contenthub_entity->getAttribute($bundle_key)['value']) : NULL;
    $langcodes = !empty($contenthub_entity->getAttribute('default_langcode')['value']) ? array_keys($contenthub_entity->getAttribute('default_langcode')['value']) : [$this->languageManager->getDefaultLanguage()->getId()];
    // Get default langcode and remove from attributes.
    if (!empty($contenthub_entity->getAttribute('default_langcode')['value'])) {
      foreach ($contenthub_entity->getAttribute('default_langcode')['value'] as $key => $value) {
        if ($value[0] == TRUE) {
          $default_langcode = $key;
          continue;
        }
      }
    }
    else {
      if ($entity_type == 'file') {
        $default_langcode = key($data['attributes']['url']['value']);
        $langcodes = array_keys($data['attributes']['url']['value']);
      }
      else {
        $default_langcode = $this->languageManager->getDefaultLanguage()->getId();
      }
    }
    // Default Langcode is only used for initial entity creation. Remove now.
    $contenthub_entity->removeAttribute('langcode');
    // Store the translation source outside the CDF.
    $content_translation_source = $contenthub_entity->getAttribute('content_translation_source');
    $contenthub_entity->removeAttribute('content_translation_source');

    // Does this entity exist in this site already?
    $source_entity = $this->entityRepository->loadEntityByUuid($entity_type, $contenthub_entity->getUuid());
    if ($source_entity == NULL) {

      // Transforming Content Hub Entity into a Drupal Entity.
      $values = [
        'uuid' => $contenthub_entity->getUuid(),
      ];
      if ($bundle) {
        $values[$bundle_key] = $bundle;
      }

      // Set the content_translation source of whatever the default langcode says.
      $values['content_translation_source'] = $content_translation_source['value'][$default_langcode][0];

      // Set the default langcode of the parent entity.
      $values['default_langcode'] = $default_langcode;
      // Special treatment according to entity types.
      switch ($entity_type) {
        case 'node':
          $author = $contenthub_entity->getAttribute('author') ? $contenthub_entity->getAttribute('author')['value'][$default_langcode] : FALSE;
          $user = Uuid::isValid($author) ? $this->entityRepository->loadEntityByUuid('user', $author) : \Drupal::currentUser();
          $values['uid'] = $user->id() ? $user->id() : 0;

          // Set a status for the default language entity.
          $status = $contenthub_entity->getAttribute('status') ? $contenthub_entity->getAttribute('status')['value'][$default_langcode] : 0;
          $values['status'] = $status ? $status : 0;

          // Check if Workbench Moderation is enabled.
          $workbench_moderation_enabled = \Drupal::moduleHandler()->moduleExists('workbench_moderation');
          if ($workbench_moderation_enabled) {
            $values['moderation_state'] = 'published';
          }
          break;

        case 'media':
          $attribute = $contenthub_entity->getAttribute($bundle_key);
          foreach ($langcodes as $lang) {
            if (isset($attribute['value'][$lang])) {
              $value = reset($attribute['value'][$lang]);
              // Media entity didn't import by previous version of the module.
              $values[$bundle_key] = $value;
            }
          }
          // Remove an attribute to avoid the 'Error reading entity with
          // UUID="image" from Content Hub' error.
          if (!empty($values[$bundle_key])) {
            $contenthub_entity->removeAttribute($bundle_key);
          }
          break;

        case 'file':
          // If this is a file, then download the asset (image) locally.
          $attribute = $contenthub_entity->getAttribute('url');
          foreach ($langcodes as $lang) {
            if (isset($attribute['value'][$lang])) {
              $remote_uri = is_array($attribute['value'][$lang]) ? array_values($attribute['value'][$lang])[0] : $attribute['value'][$lang];
              $filepath = $this->getFilePath($contenthub_entity);
              if ($file_drupal_path = system_retrieve_file($remote_uri, $filepath, FALSE)) {
                $values['uri'] = $file_drupal_path;
              }
              else {
                // If the file URL is not publicly accessible, then this file
                // entity cannot be created. There is no point in trying to
                // complete the creation of this entity because it will fail
                // to be saved in the system.
                // Return a NULL entity and deal with it afterwards.
                $logger = \Drupal::getContainer()->get('logger.factory');
                $message = $this->t('File Entity with UUID = "%uuid" cannot be created: The remote resource %uri could not be downloaded into the system. Make sure this resource has a publicly accessible URL.', [
                  '%uuid' => $values['uuid'],
                  '%uri' => $remote_uri,
                ]);
                $logger->get('acquia_contenthub')->error($message);
                drupal_set_message($message, 'error');
                return NULL;
              }
            }
          }
          break;

        case 'taxonomy_term':
          // If it is a taxonomy_term, assing the vocabulary.
          // @TODO: This is a hack. It should work with vocabulary entities.
          $attribute = $contenthub_entity->getAttribute('vocabulary');
          foreach ($langcodes as $lang) {
            $vocabulary_machine_name = $attribute['value'][$lang];
            $vocabulary = $this->getVocabularyByName($vocabulary_machine_name);
            if (isset($vocabulary)) {
              $values['vid'] = $vocabulary->getOriginalId();
            }
          }
          break;

        case 'paragraph':
          // In case of paragraphs, we need to strip out the parent_uuid and
          // change it for parent_id.
          // Fix a bug happening during export where parent_uuid only includes
          // one language, which will fail during multilingual import. Extract
          // the parent UUID and replicate it into all the languages.
          $parent_uuid = current(array_filter($contenthub_entity->getAttribute('parent_uuid')['value']));
          $parent_type = current(current(array_filter($contenthub_entity->getAttribute('parent_type')['value'])));
          $parent_entity = $this->entityRepository->loadEntityByUuid($parent_type, $parent_uuid);

          $parent_id_attribute = new Attribute(Attribute::TYPE_ARRAY_STRING);
          foreach ($langcodes as $lang) {
            $parent_id_attribute->setValue([$parent_entity->id()], $lang);
          }

          // Add parent_id attribute to entity and remove parent_uuid.
          $attributes = $contenthub_entity->getAttributes();
          $attributes['parent_id'] = (array) $parent_id_attribute;
          $contenthub_entity->setAttributes($attributes);
          $contenthub_entity->removeAttribute('parent_uuid');
          break;
      }

      $langcode_key = $this->entityTypeManager->getDefinition($entity_type)->getKey('langcode');
      $values[$langcode_key] = [$default_langcode];
      $source_entity = $this->entityTypeManager->getStorage($entity_type)->create($values);
    }
    else {
      $contenthub_entity->removeAttribute('default_langcode');
    }


    $entity = $source_entity;
    foreach ($langcodes as $langcode) {
      // Make sure the entity language is one of the language contained in the
      // Content Hub Entity.
      if ($source_entity->hasTranslation($langcode)) {
        $localized_entity = $source_entity->getTranslation($langcode);
        $entity = $this->addFieldsToDrupalEntity($localized_entity, $contenthub_entity, $langcode, $context);
      }
      else {
        if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED || $langcode == LanguageInterface::LANGCODE_NOT_APPLICABLE) {
          $entity = $this->addFieldsToDrupalEntity($source_entity, $contenthub_entity, $langcode, $context);
        }
        else {
          $localized_entity = $source_entity->addTranslation($langcode, $source_entity->toArray());
          $localized_entity->content_translation_source = $content_translation_source['value'][$langcode][0];

          // Grab status for the language.
          $status = $contenthub_entity->getAttribute('status') ? $contenthub_entity->getAttribute('status')['value'][$langcode] : 0;
          $localized_entity->status = $status ? $status : 0;

          $entity = $this->addFieldsToDrupalEntity($localized_entity, $contenthub_entity, $langcode, $context);
        }
      }
    }

    // Allow other modules to intercept and do changes to the Drupal entity
    // after it has been denormalized from a Content Hub CDF.
    $this->moduleHandler->alter('acquia_contenthub_drupal_from_cdf', $entity_type, $entity);

    return $entity;
  }

  /**
   * Remove brackets from the Uuid.
   *
   * @param string $uuid_with_brakets
   *   A [UUID] enclosed within brackets.
   *
   * @return mixed
   *   The UUID without brackets, FALSE otherwise.
   */
  protected function removeBracketsUuid($uuid_with_brakets) {
    preg_match('#\[(.*)\]#', $uuid_with_brakets, $match);
    $uuid = isset($match[1]) ? $match[1] : '';
    if (Uuid::isValid($uuid)) {
      return $uuid;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Returns a vocabulary object which matches the given name.
   *
   * Will return null if no such vocabulary exists.
   *
   * @param string $vocabulary_name
   *   This is the name of the section which is required.
   *
   * @return Object
   *   This is the vocabulary object with the name or null if no such vocabulary
   *   exists.
   */
  private function getVocabularyByName($vocabulary_name) {
    $vocabs = Vocabulary::loadMultiple(NULL);
    foreach ($vocabs as $vocab_object) {
      /* @var $vocab_object \Drupal\taxonomy\Entity\Vocabulary  */
      if ($vocab_object->getOriginalId() == $vocabulary_name) {
        return $vocab_object;
      }
    }
    return NULL;
  }

  /**
   * Extracts the filepath and creates directories so that files can be stored.
   *
   * @param \Acquia\ContentHubClient\Entity $contenthub_entity
   *   The Content Hub Entity.
   *
   * @return null|string
   *   The File URI if the directories can be created, NULL otherwise.
   */
  private function getFilePath(ContentHubEntity $contenthub_entity) {
    if ($contenthub_entity->getType() !== 'file') {
      return NULL;
    }
    if (!$attribute_filepath = $contenthub_entity->getAttribute('_filepath')) {
      return NULL;
    }
    $uri = isset($attribute_filepath['value'][LanguageInterface::LANGCODE_NOT_SPECIFIED]) ? $attribute_filepath['value'][LanguageInterface::LANGCODE_NOT_SPECIFIED] : NULL;
    if (substr($uri, 0, 9) !== 'public://') {
      return NULL;
    }
    $file_uri = $uri;
    // Create directories.
    $path = pathinfo($file_uri);
    $filepath = $path['dirname'];
    if (!is_dir($filepath) || !is_writable($filepath)) {
      if (!file_prepare_directory($filepath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
        // Log that directory could not be created.
        \Drupal::logger('acquia_contenthub')
          ->error('Cannot create files subdirectory "!dir". Please check filesystem permissions.', [
            '!dir' => $filepath,
          ]);
        $file_uri = NULL;
      }
    }
    return $file_uri;
  }
}
