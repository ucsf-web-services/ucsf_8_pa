<?php

namespace Drupal\acquia_contenthub\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\acquia_contenthub\EntityManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines Acquia Content Hub Dynamic REST routes.
 */
class ResourceRoutes {

  /**
   * The content hub entity manager.
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
   * Constructs a ResourceRoutes object.
   *
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The entity manager for Content Hub.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityManager $entity_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Generates Content Hub REST resource routes for every eligible entity type.
   */
  public function routes() {
    $collection = new RouteCollection();
    $entity_type_ids = $this->entityManager->getContentHubEnabledEntityTypeIds();

    foreach ($entity_type_ids as $entity_type_id) {
      // Match the behavior of \Drupal\rest\Plugin\rest\resource\EntityResource:
      // use the entity type's canonical link template if it has one, otherwise
      // use EntityResource's generic alternative.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $canonical_path = $entity_type->hasLinkTemplate('canonical')
        ? $entity_type->getLinkTemplate('canonical')
        : '/entity/' . $entity_type_id . '/{' . $entity_type_id . '}';

      $content_hub_path = '/acquia-contenthub-cdf' . $canonical_path;

      $route = new Route($content_hub_path, [
        '_controller' => '\Drupal\acquia_contenthub\Controller\ContentHubEntityRequestHandler::handle',
        // @see \Drupal\acquia_contenthub\Controller\ContentHubEntityRequestHandler
        // @todo Remove this when https://www.drupal.org/node/2822201 lands, and this module is able to require Drupal 8.3.x.
        '_acquia_content_hub_rest_resource_plugin_id' => 'entity:' . $entity_type_id,
      ]);
      $route->setOption('parameters', [
        $entity_type_id => [
          'type' => 'entity:' . $entity_type_id,
        ],
      ]);
      // Only allow the Acquia Content Hub CDF format.
      $route->setRequirement('_format', 'acquia_contenthub_cdf|html|json');
      // Only allow access to the CDF if the request is coming from a logged
      // in user with 'Administer Acquia Content Hub' permission or if it
      // is coming from Acquia Content Hub (validates the HMAC signature).
      $route->setRequirement('_contenthub_access', 'TRUE');
      // Only allow GET.
      $route->setMethods(['GET']);

      $collection->add('acquia_contenthub.entity.' . $entity_type_id . '.GET.acquia_contenthub_cdf', $route);
    }

    return $collection;
  }

}
