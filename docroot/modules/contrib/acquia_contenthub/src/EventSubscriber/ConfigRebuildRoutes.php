<?php

namespace Drupal\acquia_contenthub\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * A subscriber to rebuild routes whenever there is a change in configuration.
 *
 * Class ConfigRebuildRoutes.
 *
 * @package Drupal\acquia_contenthub\EventSubscriber
 */
class ConfigRebuildRoutes implements EventSubscriberInterface {

  protected $routeBuilder;

  /**
   * Public constructor.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The Route Builder service.
   */
  public function __construct(RouteBuilderInterface $route_builder) {
    $this->routeBuilder = $route_builder;
  }

  /**
   * Rebuild routes whenever we save new configuration.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    // Changing the content hub configuration entities that control the entity
    // types that are "exportable" to content hub requires that we rebuild
    // the routes to make those new routes available for the format
    // 'acquia_contenthub_cdf'.
    // We do not need to invalidate tags because any request coming to the
    // routes provided by acquia_contenthub are added the 'cache tag' of the
    // configuration entity that provided the response. Then if that config
    // entity is saved, its cache tag is automatically invalidated and thus
    // the response to the format 'acquia_contenthub_cdf' of the entity that
    // is served through it.
    if (strpos($event->getConfig()->getName(), 'acquia_contenthub.entity.') !== FALSE) {
      $this->routeBuilder->setRebuildNeeded();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
