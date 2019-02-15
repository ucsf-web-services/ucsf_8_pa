<?php

namespace Drupal\acquia_contenthub\Normalizer;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\acquia_contenthub\ContentHubSubscription;
use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Extracts the rendered view modes from a given ContentEntity Object.
 */
class ContentEntityViewModesExtractor implements ContentEntityViewModesExtractorInterface {
  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

  /**
   * The current_user service used by this plugin.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The user session used to render a Content Hub content.
   *
   * @var \Drupal\acquia_contenthub\Session\ContentHubUserSession
   */
  protected $renderUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Basic HTTP Kernel to make requests.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $kernel;

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription
   */
  protected $contentHubSubscription;

  /**
   * The Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Block Manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a ContentEntityViewModesExtractor object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current session user.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
   *   The Kernel.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The Account Switcher Service.
   * @param \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription
   *   The Content Hub Subscription.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The Block Manager Interface.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Request Stack.
   */
  public function __construct(AccountProxyInterface $current_user, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, HttpKernelInterface $kernel, AccountSwitcherInterface $account_switcher, ContentHubSubscription $contenthub_subscription, ConfigFactoryInterface $config_factory, BlockManagerInterface $block_manager, RequestStack $request_stack) {
    $this->currentUser = $current_user;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->kernel = $kernel;
    $this->accountSwitcher = $account_switcher;
    $this->contentHubSubscription = $contenthub_subscription;
    $this->config = $config_factory;
    $this->renderUser = new ContentHubUserSession($this->config->get('acquia_contenthub.entity_config')->get('user_role'));
    $this->blockManager = $block_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * Obtains the Configuration entity for the current entity type.
   *
   * @param string $entity_type_id
   *   The Entity Type ID.
   *
   * @return bool|\Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface
   *   The ContentHubEntityType Configuration Entity if exists, FALSE otherwise.
   */
  protected function getContentHubEntityTypeConfigEntity($entity_type_id) {
    /** @var \Drupal\rest\RestResourceConfigInterface $contenthub_entity_config_storage */
    $contenthub_entity_config_storage = $this->entityTypeManager->getStorage('acquia_contenthub_entity_config');

    /** @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface[] $contenthub_entity_config_ids */
    $contenthub_entity_config_ids = $contenthub_entity_config_storage->loadMultiple([$entity_type_id]);
    $contenthub_entity_config_id = isset($contenthub_entity_config_ids[$entity_type_id]) ? $contenthub_entity_config_ids[$entity_type_id] : FALSE;

    return $contenthub_entity_config_id;
  }

  /**
   * Checks whether the given class is supported for normalization.
   *
   * @param mixed $data
   *   Data to normalize.
   *
   * @return bool
   *   TRUE if is child of supported class.
   */
  private function isChildOfSupportedClass($data) {
    // If we aren't dealing with an object that is not supported return
    // now.
    if (!is_object($data)) {
      return FALSE;
    }
    $supported = (array) $this->supportedInterfaceOrClass;

    return (bool) array_filter($supported, function ($name) use ($data) {
      return $data instanceof $name;
    });
  }

  /**
   * Renders all the view modes that are configured to be rendered.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $object
   *   The Content Entity object.
   *
   * @return array|null
   *   The normalized array.
   */
  public function getRenderedViewModes(ContentEntityInterface $object) {
    $normalized = [];

    // Exit if the class does not support normalizing to the given format or
    // the entity is not yet saved in the database (does not have an ID).
    if (!$this->isChildOfSupportedClass($object) || $object->isNew()) {
      return NULL;
    }

    $entity_type_id = $object->getEntityTypeId();
    $entity_bundle_id = $object->bundle();
    $contenthub_entity_config_id = $this->getContentHubEntityTypeConfigEntity($entity_type_id);

    // Stop processing if 'view modes' are not configured for this entity type.
    if (!$contenthub_entity_config_id || $contenthub_entity_config_id->isEnabledViewModes($entity_bundle_id) === FALSE) {
      return NULL;
    }

    // Obtain the list of view modes.
    $configured_view_modes = $contenthub_entity_config_id->getRenderingViewModes($entity_bundle_id);

    // Normalize.
    $view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $entity_bundle_id);

    // Generate preview image URL, if possible.
    $preview_image_url = $this->getPreviewImageUrl($object);

    foreach ($view_modes as $view_mode_id => $view_mode_label) {
      if (!in_array($view_mode_id, $configured_view_modes)) {
        continue;
      }
      $view_mode_label = ($view_mode_label instanceof FormattableMarkup) ? $view_mode_label->render() : $view_mode_label;
      // Generate our URL where the isolated rendered view mode lives.
      // This is the best way to really make sure the content in Content Hub
      // and the content shown to any user is 100% the same.
      $url = Url::fromRoute('acquia_contenthub.content_entity_display.entity', [
        'entity_type' => $object->getEntityTypeId(),
        'entity_id' => $object->id(),
        'view_mode_name' => $view_mode_id,
      ])->getInternalPath();

      $url = '/' . $url;
      $master_request = $this->requestStack->getCurrentRequest();
      $request = Request::create($url, 'GET', [], $master_request->cookies->all(), [], $master_request->server->all());
      $request = $this->contentHubSubscription->setHmacAuthorization($request, TRUE);

      /** @var \Drupal\Core\Render\HtmlResponse $response */
      $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);

      $normalized[$view_mode_id] = [
        'id' => $view_mode_id,
        'preview_image' => $preview_image_url,
        'label' => $view_mode_label,
        'url' => $url,
        'html' => $response->getContent(),
      ];
    }

    return $normalized;
  }

  /**
   * Get preview image URL.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Content Entity object.
   *
   * @return string
   *   Preview image URL.
   */
  protected function getPreviewImageUrl(ContentEntityInterface $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    $contenthub_entity_config_id = $this->getContentHubEntityTypeConfigEntity($entity_type_id);

    // Obtaining preview image field and style from the configuration entity.
    $preview_image_field = $contenthub_entity_config_id->getPreviewImageField($bundle);
    $preview_image_style = $contenthub_entity_config_id->getPreviewImageStyle($bundle);

    // Don't set, if no preview image has been configured.
    if (empty($preview_image_field) || empty($preview_image_style)) {
      return '';
    }

    $preview_image_config_array = explode('->', $preview_image_field);
    foreach ($preview_image_config_array as $field_key) {
      // Don't set, if node has no such field or field has no such entity.
      if (empty($entity->{$field_key}->entity) ||
        method_exists($entity->{$field_key}, 'isEmpty') && $entity->{$field_key}->isEmpty()
      ) {
        return '';
      }
      $entity = $entity->{$field_key}->entity;
    }

    if (!in_array($entity->bundle(), ['image', 'file'])) {
      return '';
    }
    $file_uri = $entity->getFileUri();

    // Process Image style.
    $image_style = $this->entityTypeManager->getStorage('image_style')->load($preview_image_style);
    // Return empty if no such image style.
    if (empty($image_style)) {
      return '';
    }

    // Return preview image URL.
    $preview_image_uri = $image_style->buildUrl($file_uri);
    return file_create_url($preview_image_uri);
  }

  /**
   * Renders all the view modes that are configured to be rendered.
   *
   * In this method we also switch to an user with role defined in the module
   * entity configuration.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $object
   *   The Content Entity Object.
   * @param string $view_mode
   *   The request view mode identifier.
   *
   * @see https://www.drupal.org/node/218104
   *
   * @return array
   *   The render array for the complete page, as minimal as possible.
   */
  public function getViewModeMinimalHtml(ContentEntityInterface $object, $view_mode) {
    // Creating a fake user account to give as context to the normalization.
    $account = $this->renderUser;
    // Checking for entity access permission to this particular account.
    $entity_access = $object->access('view', $account, TRUE);
    if (!$entity_access->isAllowed()) {
      return [];
    }

    $this->accountSwitcher->switchTo($this->renderUser);

    // Render View Mode.
    $entity_type_id = $object->getEntityTypeId();
    $use_block_content_view_builder = $this->config->get('acquia_contenthub.entity_config')->get('use_block_content_view_builder');
    if ($entity_type_id === 'block_content' && $use_block_content_view_builder) {
      $build = $this->getBlockMinimalBuildArray($object, $view_mode);
    }
    else {
      $build = $this->entityTypeManager->getViewBuilder($entity_type_id)
        ->view($object, $view_mode);
    }

    // Add our cacheable dependency. If this config changes, clear the render
    // cache.
    $contenthub_entity_config_id = $this->getContentHubEntityTypeConfigEntity($entity_type_id);
    $this->renderer->addCacheableDependency($build, $contenthub_entity_config_id);

    // Add a role cacheable dependency.
    $render_role_id = $this->config->get('acquia_contenthub.entity_config')->get('user_role');
    $render_role = $this->entityTypeManager->getStorage('user_role')->load($render_role_id);
    $this->renderer->addCacheableDependency($build, $render_role);

    // Restore user account.
    $this->accountSwitcher->switchBack();

    return $build;
  }

  /**
   * Renders block using BlockViewBuilder.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $object
   *   The Content Entity Object.
   * @param string $view_mode
   *   The request view mode identifier.
   *
   * @return array
   *   Render array for the block.
   */
  private function getBlockMinimalBuildArray(ContentEntityInterface $object, $view_mode) {
    /** @var \Drupal\entity_block\Plugin\Block\EntityBlock $block */
    $block = $this->blockManager->createInstance('block_content:' . $object->uuid());

    $build = [
      '#theme' => 'block',
      '#attributes' => [],
      '#contextual_links' => [],
      '#weight' => 0,
      '#configuration' => $block->getConfiguration(),
      '#plugin_id' => $block->getPluginId(),
      '#base_plugin_id' => $block->getBaseId(),
      '#derivative_plugin_id' => $block->getDerivativeId(),
    ];

    // Label controlled by the block__block_content__acquia_contenthub template
    // (hidden by default). Override the template in your theme to render a
    // block label.
    if ($build['#configuration']['label'] === '') {
      $build['#configuration']['label'] = $block->label();
    }
    // Block entity itself doesn't have configuration.
    $block->setConfigurationValue('view_mode', $view_mode);
    $build['#configuration']['view_mode'] = $view_mode;

    // See \Drupal\block\BlockViewBuilder::preRender() for reference.
    $content = $block->build();
    if ($content !== NULL && !Element::isEmpty($content)) {
      foreach (['#attributes', '#contextual_links'] as $property) {
        if (isset($content[$property])) {
          $build[$property] += $content[$property];
          unset($content[$property]);
        }
      }
    }

    $build['content'] = $content;

    return $build;
  }

}
