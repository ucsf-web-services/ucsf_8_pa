<?php

namespace Drupal\domain_site_settings\Configuration;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Overrides the config with the saved domain specific settings.
 *
 * @package Drupal\domain_site_settings
 */
class DomainConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a DomainSourcePathProcessor object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   */
  public function loadOverrides($names = []) {
    $overrides = [];
    if (in_array('system.site', $names)) {
      /** @var \Drupal\domain\DomainNegotiator $negotiator */
      $negotiator = \Drupal::service('domain.negotiator');
      $domain = $negotiator->getActiveDomain();
      if (!empty($domain)) {
        $domain_key = $domain->id();
        $configFactory = $this->configFactory->get('domain_site_settings.domainconfigsettings');
        if ($configFactory->get($domain_key) !== NULL) {
          $site_name = $configFactory->get($domain_key . '.site_name');
          $site_slogan = $configFactory->get($domain_key . '.site_slogan');
          $site_mail = $configFactory->get($domain_key . '.site_mail');
          $site_403 = $configFactory->get($domain_key . '.site_403');
          $site_404 = $configFactory->get($domain_key . '.site_404');
          $site_front = $configFactory->get($domain_key . '.site_frontpage');
          $front = ($site_front !== \NULL) ? $site_front : '/node';

          // Create the new settings array to override the configuration.
          $overrides['system.site'] = [
            'name' => $site_name,
            'slogan' => $site_slogan,
            'mail' => $site_mail,
            'page' => [
              '403' => $site_403,
              '404' => $site_404,
              'front' => $front,
            ],
          ];
        }
      }
    }
    return $overrides;
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheSuffix() {
    return 'MultiSiteConfigurationOverrider';
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritDoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
