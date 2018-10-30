<?php

namespace Drupal\acquia_contenthub_diagnostic;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an ContentHubRequirementManager plugin manager.
 */
class ContentHubRequirementManager extends DefaultPluginManager {

  /**
   * Constructs a new ContentHubRequirementManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ContentHubRequirement',
      $namespaces,
      $module_handler,
      'Drupal\acquia_contenthub_diagnostic\ContentHubRequirementInterface',
      'Drupal\acquia_contenthub_diagnostic\Annotation\ContentHubRequirement'
    );
    $this->setCacheBackend($cache_backend, 'contenthub_requirement_plugins');
  }

  /**
   * Creates pre-configured instances of all plugins.
   *
   * @return \Drupal\acquia_contenthub_diagnostic\ContentHubRequirementInterface[]
   *   An array of fully configured plugin instances.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If an instance cannot be created, such as if the ID is invalid.
   */
  public function createInstanceMultiple() {
    $instances = [];
    $definitions = $this->getDefinitions();
    foreach ($definitions as $id => $definition) {
      $instances[$id] = $this->createInstance($id);
    }
    return $instances;
  }

}
