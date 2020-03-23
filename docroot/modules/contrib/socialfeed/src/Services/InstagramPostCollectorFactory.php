<?php

namespace Drupal\socialfeed\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;

/**
 * Class InstagramPostCollectorFactory.
 *
 * @package Drupal\socialfeed
 */
class InstagramPostCollectorFactory {

  /**
   * Default Instagram application api key.
   *
   * @var string
   */
  protected $defaultApiKey;

  /**
   * Default Instagram application api secret.
   *
   * @var string
   */
  protected $defaultApiSecret;

  /**
   * Default Instagram redirect URI.
   *
   * @var string
   */
  protected $defaultRedirectUri;

  /**
   * Default Instagram application access token.
   *
   * @var string
   */
  protected $defaultAccessToken;

  /**
   * InstagramPostCollectorFactory constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('socialfeed.instagramsettings');
    $this->defaultApiKey = $config->get('client_id');
    $this->defaultApiSecret = $config->get('api_secret');
    $this->defaultRedirectUri = $config->get('redirect_uri');
    $this->defaultAccessToken = $config->get('access_token');
  }

  /**
   * Factory method for the InstagramPostCollector class.
   *
   * @param string|null $apiKey
   *   $apiKey.
   * @param string|null $apiSecret
   *   $apiSecret.
   * @param string|null $accessToken
   *   $accessToken.
   *
   * @return \Drupal\socialfeed\Services\InstagramPostCollector
   *   InstagramPostCollector.
   *
   * @throws \Exception
   */
  public function createInstance($apiKey, $apiSecret, $redirectUri, $accessToken) {
    return new InstagramPostCollector(
      $apiKey ?: $this->defaultApiKey,
      $apiSecret ?: $this->defaultApiSecret,
      $redirectUri ?: $this->defaultRedirectUri,
      $accessToken ?: $this->defaultAccessToken
    );
  }

}
