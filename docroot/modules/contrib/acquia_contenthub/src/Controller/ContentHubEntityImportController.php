<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\ImportEntityManager;

/**
 * Controller for Content Hub Imported Entities.
 */
class ContentHubEntityImportController extends ControllerBase {

  /**
   * The Content Hub Import Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\ImportEntityManager
   */
  private $importEntityManager;

  /**
   * Content hub configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * Public Constructor.
   *
   * @param \Drupal\acquia_contenthub\ImportEntityManager $import_entity_manager
   *   The Content Hub Import Entity Manager.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The Config Factory.
   */
  public function __construct(ImportEntityManager $import_entity_manager, ConfigFactory $config_factory) {
    $this->importEntityManager = $import_entity_manager;
    $this->config = $config_factory->getEditable('acquia_contenthub.entity_config');
  }

  /**
   * Implements the static interface create method.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acquia_contenthub.import_entity_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Imports a Content Hub Entity local site, given its UUID.
   *
   * @param string $uuid
   *   The UUID of the Entity to save.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON Response.
   */
  public function importEntity($uuid) {
    return $this->importEntityManager->import($uuid);
  }

}
