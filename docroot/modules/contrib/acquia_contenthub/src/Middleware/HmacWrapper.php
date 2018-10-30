<?php

namespace Drupal\acquia_contenthub\Middleware;

use Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacBase;
use Drupal\Core\Config\ConfigFactoryInterface;

class HmacWrapper extends MiddlewareHmacBase implements MiddlewareHmacInterface {

  /**
   * The Drupal Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  public function __construct(ConfigFactoryInterface $config_factory) {

    $configFactory = $config_factory;

    // Get the content hub config settings.
    $this->config = $configFactory->get('acquia_contenthub.admin_settings');

    if (!$this->apiKey) {
      $this->apiKey = $this->config->get('api_key');
    }
    if (!$this->secretKey) {
      $this->secretKey = $this->config->get('secret_key');
    }
  }

  /**
   * @return mixed
   */
  public function getMiddleware() {
    // When HMAC V2 is supported, grab the configured version below.
    // $version = $config->get('hmac_version');
    $version = 'V1';
    $class = "\Acquia\ContentHubClient\Middleware\MiddlewareHmac" . $version;
    $middleware = new $class($this->apiKey, $this->secretKey, $version);
    return $middleware->getMiddleware();
  }

}
