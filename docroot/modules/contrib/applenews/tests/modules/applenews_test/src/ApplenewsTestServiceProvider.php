<?php

namespace Drupal\applenews_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Replaces the Apple News publisher class with a testing version.
 */
class ApplenewsTestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    $container->getDefinition('applenews.publisher')
      ->setClass(Publisher::class);
  }

}
