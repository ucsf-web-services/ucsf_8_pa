<?php

namespace Drupal\socialfeed\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InstagramAuthController.
 *
 * @package Drupal\socialfeed\Controller
 */
class InstagramAuthController extends ControllerBase {

  /**
   * InstagramAuthController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Get an Instagram access token.
   */
  public function accessToken() {
    $code = \Drupal::request()->query->get('code');

    $message = 'Something went wrong. The access token could not be created.';
    $token = '';

    if ($code) {
      $config = $this->configFactory->getEditable('socialfeed.instagramsettings');

      $instagram = new InstagramBasicDisplay([
        'appId' => $config->get('client_id'),
        'appSecret' => $config->get('app_secret'),
        'redirectUri' => \Drupal::request()->getSchemeAndHttpHost() . Url::fromRoute('socialfeed.instagram_auth')->toString(),
      ]);

      // Get the short lived access token (valid for 1 hour)
      $token = $instagram->getOAuthToken($code, TRUE);

      // Exchange this token for a long lived token (valid for 60 days)
      if ($token) {
        $token = $instagram->getLongLivedToken($token, TRUE);
        $config->set('access_token', $token);
        $config->set('access_token_date', time());
        $config->save();

        $message = 'Your Access Token has been generated and saved.';
      }
    }

    $build = [
      '#markup' => $this->t($message) . ' ' . $token,
    ];

    return $build;
  }

}
