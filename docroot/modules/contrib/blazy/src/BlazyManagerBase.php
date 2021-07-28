<?php

namespace Drupal\blazy;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\NestedArray;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements BlazyManagerInterface.
 */
abstract class BlazyManagerBase implements BlazyManagerInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a BlazyManager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, RendererInterface $renderer, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler     = $module_handler;
    $this->renderer          = $renderer;
    $this->configFactory     = $config_factory;
    $this->cache             = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('renderer'),
      $container->get('config.factory'),
      $container->get('cache.default')
    );
  }

  /**
   * Returns the entity type manager.
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * Returns the module handler.
   */
  public function getModuleHandler() {
    return $this->moduleHandler;
  }

  /**
   * Returns the renderer.
   */
  public function getRenderer() {
    return $this->renderer;
  }

  /**
   * Returns the config factory.
   */
  public function getConfigFactory() {
    return $this->configFactory;
  }

  /**
   * Returns the cache.
   */
  public function getCache() {
    return $this->cache;
  }

  /**
   * Returns any config, or keyed by the $setting_name.
   */
  public function configLoad($setting_name = '', $settings = 'blazy.settings') {
    $config  = $this->configFactory->get($settings);
    $configs = $config->get();
    unset($configs['_core']);
    return empty($setting_name) ? $configs : $config->get($setting_name);
  }

  /**
   * Returns a shortcut for loading a config entity: image_style, slick, etc.
   */
  public function entityLoad($id, $entity_type = 'image_style') {
    return $this->entityTypeManager->getStorage($entity_type)->load($id);
  }

  /**
   * Returns a shortcut for loading multiple configuration entities.
   */
  public function entityLoadMultiple($entity_type = 'image_style', $ids = NULL) {
    return $this->entityTypeManager->getStorage($entity_type)->loadMultiple($ids);
  }

  /**
   * Returns array of needed assets suitable for #attached property.
   */
  public function attach($attach = []) {
    $load   = [];
    $switch = empty($attach['media_switch']) ? '' : $attach['media_switch'];

    if ($switch && $switch != 'content') {
      $attach[$switch] = $switch;

      if (in_array($switch, $this->getLightboxes())) {
        $load['library'][] = 'blazy/lightbox';
      }
    }

    // Allow both variants of grid or column to co-exist for different fields.
    if (!empty($attach['style'])) {
      foreach (['column', 'grid'] as $grid) {
        $attach[$grid] = $attach['style'];
      }
    }

    foreach (['column', 'grid', 'media', 'photobox', 'ratio'] as $component) {
      if (!empty($attach[$component])) {
        $load['library'][] = 'blazy/' . $component;
      }
    }

    // Core Blazy libraries.
    if (!empty($attach['blazy'])) {
      $load['library'][] = 'blazy/load';
      $load['drupalSettings']['blazy'] = $this->configLoad()['blazy'];
    }

    $this->moduleHandler->alter('blazy_attach', $load, $attach);
    return $load;
  }

  /**
   * Collects defined skins as registered via hook_MODULE_NAME_skins_info().
   */
  public function buildSkins($namespace, $skin_class, $methods = []) {
    $cid = $namespace . ':skins';

    if ($cache = $this->cache->get($cid)) {
      return $cache->data;
    }

    $classes = $this->moduleHandler->invokeAll($namespace . '_skins_info');
    $classes = array_merge([$skin_class], $classes);
    $items   = $skins = [];
    foreach ($classes as $class) {
      if (class_exists($class)) {
        $reflection = new \ReflectionClass($class);
        if ($reflection->implementsInterface($skin_class . 'Interface')) {
          $skin = new $class();
          if (empty($methods) && method_exists($skin, 'skins')) {
            $items = $skin->skins();
          }
          else {
            foreach ($methods as $method) {
              $items[$method] = method_exists($skin, $method) ? $skin->{$method}() : [];
            }
          }
        }
      }
      $skins = NestedArray::mergeDeep($skins, $items);
    }

    $count = isset($items['skins']) ? count($items['skins']) : count($items);
    $tags  = Cache::buildTags($cid, ['count:' . $count]);

    $this->cache->set($cid, $skins, Cache::PERMANENT, $tags);

    return $skins;
  }

  /**
   * Returns the common settings inherited down to each item.
   */
  public function getCommonSettings() {
    return array_intersect_key($this->configLoad('', 'blazy.settings'), BlazyDefault::uiSettings());
  }

  /**
   * Gets the supported lightboxes.
   *
   * @return array
   *   The supported lightboxes.
   */
  public function getLightboxes() {
    $lightboxes = [];
    foreach (['colorbox', 'photobox'] as $lightbox) {
      if (function_exists($lightbox . '_theme')) {
        $lightboxes[] = $lightbox;
      }
    }

    // Checks only needed for tests.
    if (\Drupal::hasService('app.root') && is_file(\Drupal::root() . '/libraries/photobox/photobox/jquery.photobox.js')) {
      $lightboxes[] = 'photobox';
    }

    $this->moduleHandler->alter('blazy_lightboxes', $lightboxes);
    return array_unique($lightboxes);
  }

  /**
   * Return the cache metadata common for all blazy-related modules.
   */
  public function getCacheMetadata(array $build = []) {
    $settings          = $build['settings'];
    $max_age           = $this->configLoad('cache.page.max_age', 'system.performance');
    $max_age           = empty($settings['cache']) ? $max_age : $settings['cache'];
    $id                = isset($settings['id']) ? $settings['id'] : Blazy::getHtmlId($settings['namespace']);
    $suffixes[]        = empty($settings['count']) ? count(array_filter($settings)) : $settings['count'];
    $cache['tags']     = Cache::buildTags($settings['namespace'] . ':' . $id, $suffixes, '.');
    $cache['contexts'] = ['languages'];
    $cache['max-age']  = $max_age;
    $cache['keys']     = isset($settings['cache_metadata']['keys']) ? $settings['cache_metadata']['keys'] : [$id];

    if (!empty($settings['cache_tags'])) {
      $cache['tags'] = Cache::mergeTags($cache['tags'], $settings['cache_tags']);
    }

    return $cache;
  }

}
