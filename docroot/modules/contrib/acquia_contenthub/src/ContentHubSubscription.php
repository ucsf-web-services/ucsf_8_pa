<?php

namespace Drupal\acquia_contenthub;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles operations on the Acquia Content Hub Subscription.
 */
class ContentHubSubscription {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager
   */
  protected $clientManager;

  /**
   * The Subscription Settings.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $settings;

  /**
   * The Content Hub Admin Settings Simple Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Drupal State.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('acquia_contenthub.client_manager'),
      $container->get('state')
    );
  }

  /**
   * Constructs an ContentEntityNormalizer object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state class.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, ClientManagerInterface $client_manager, StateInterface $state) {
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $config_factory;
    $this->clientManager = $client_manager;
    // Get the content hub config settings.
    $this->config = $this->configFactory->getEditable('acquia_contenthub.admin_settings');

    $this->state = $state;
  }

  /**
   * Obtains the Content Hub Subscription Settings.
   *
   * @return \Acquia\ContentHubClient\Settings|bool
   *   The Settings of the Content Hub Subscription if all is fine, FALSE
   *   otherwise.
   */
  public function getSettings() {
    if ($this->settings = $this->clientManager->createRequest('getSettings')) {
      $shared_secret = $this->settings->getSharedSecret();
      $this->state->set('acquia_contenthub.shared_secret', $shared_secret);
      return $this->settings;
    }
    return FALSE;
  }

  /**
   * Get Subscription's UUID.
   *
   * @return string
   *   The subscription's UUID.
   */
  public function getUuid() {
    if ($this->settings) {
      return $this->settings->getUuid();
    }
    else {
      if ($settings = $this->clientManager->createRequest('getSettings')) {
        return $settings->getUuid();
      }
      return FALSE;
    }
  }

  /**
   * Obtains the "created" flag for this subscription.
   *
   * @return string
   *   The date when the subscription was created.
   */
  public function getCreated() {
    if ($this->settings) {
      return $this->settings->getCreated();
    }
    else {
      if ($settings = $this->clientManager->createRequest('getSettings')) {
        return $settings->getCreated();
      }
      return FALSE;
    }
  }

  /**
   * Returns the date when this subscription was last modified.
   *
   * @return mixed
   *   The date when the subscription was modified. FALSE otherwise.
   */
  public function getModified() {
    if ($this->settings) {
      return $this->settings->getModified();
    }
    else {
      if ($settings = $this->clientManager->createRequest('getSettings')) {
        return $settings->getModified();
      }
      return FALSE;
    }
  }

  /**
   * Returns all Clients attached to this subscription.
   *
   * @return array|bool
   *   An array of Client's data: (uuid, name) pairs, FALSE otherwise.
   */
  public function getClients() {
    if ($this->settings) {
      return $this->settings->getClients();
    }
    else {
      if ($settings = $this->clientManager->createRequest('getSettings')) {
        return $settings->getClients();
      }
      return FALSE;
    }
  }

  /**
   * Returns the Subscription's Shared Secret, used for Webhook verification.
   *
   * @return bool|string
   *   The shared secret, FALSE otherwise.
   */
  public function getSharedSecret() {
    if ($shared_secret = $this->state->get('acquia_contenthub.shared_secret')) {
      return $shared_secret;
    }
    else {
      if ($this->settings) {
        return $this->settings->getSharedSecret();
      }
      else {
        if ($settings = $this->clientManager->createRequest('getSettings')) {
          return $settings->getSharedSecret();
        }
        return FALSE;
      }
    }
  }

  /**
   * Regenerates the Subscription's Shared Secret.
   *
   * @return bool|string
   *   The new shared secret if successful, FALSE otherwise.
   */
  public function regenerateSharedSecret() {
    if ($response = $this->clientManager->createRequest('regenerateSharedSecret')) {
      if (isset($response['success']) && $response['success'] == 1) {
        $this->getSettings();
        return $this->getSharedSecret();
      }
    }
    return FALSE;
  }

  /**
   * Registers a client to Acquia Content Hub.
   *
   * It also sets up the Drupal variables with the client registration info.
   *
   * @param string $client_name
   *   The client name to register.
   *
   * @return bool
   *   TRUE if succeeds, FALSE otherwise.
   */
  public function registerClient($client_name) {
    if ($site = $this->clientManager->createRequest('register', [$client_name])) {
      // Resetting the origin now that we have one.
      $origin = $site['uuid'];

      // Registration successful. Setting up the origin and client name.
      $this->config->set('origin', $origin);
      $this->config->set('client_name', $client_name);
      $this->config->save();

      drupal_set_message(t('Successful Client registration with name "@name" (UUID = @uuid)', [
        '@name' => $client_name,
        '@uuid' => $origin,
      ]), 'status');
      $message = new FormattableMarkup('Successful Client registration with name "@name" (UUID = @uuid)', [
        '@name' => $client_name,
        '@uuid' => $origin,
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Updates the locally stored shared secret.
   *
   * If the locally stored shared does not match the value stored in the Content
   * Hub, then we need to update it.
   */
  public function updateSharedSecret() {
    if ($this->clientManager->isConnected()) {
      if ($this->getSharedSecret() !== $this->clientManager->createRequest('getSettings')->getSharedSecret()) {
        // If this condition is met, then the locally stored shared secret is
        // outdated. We need to update the value from the Hub.
        $this->getSettings();
        $message = new FormattableMarkup('The site has been recovered from having a stale shared secret, which prevented webhooks verification.');
        $this->loggerFactory->get('acquia_contenthub')->debug($message);
      }
    }
  }

  /**
   * Registers a Webhook to Content Hub.
   *
   * Note that this method only sends the request to register a webhook but
   * it is also required for this endpoint ($webhook_url) to provide an
   * appropriate response to Content Hub when it tries to verify the
   * authenticity of the registration request.
   *
   * @param string $webhook_url
   *   The webhook URL.
   *
   * @return bool
   *   TRUE if successful registration, FALSE otherwise.
   */
  public function registerWebhook($webhook_url) {
    $success = FALSE;
    if ($webhook = $this->clientManager->createRequest('addWebhook', [$webhook_url])) {
      $this->config->set('webhook_uuid', $webhook['uuid']);
      $this->config->set('webhook_url', $webhook['url']);
      $this->config->save();
      drupal_set_message(t('Webhooks have been enabled. This site will now receive updates from Content Hub.'), 'status');
      $success = TRUE;
      $message = new FormattableMarkup('Successful registration of Webhook URL = @URL', [
        '@URL' => $webhook['url'],
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
    }
    return $success;
  }

  /**
   * Checks whether we have a registered webhook in this site.
   *
   * @return bool
   *   TRUE if we have a registered webhook in this site, FALSE otherwise.
   */
  public function isWebhookSet() {
    $webhook_uuid = $this->config->get('webhook_uuid');
    $webhook_url = $this->config->get('webhook_url');
    if ($settings = $this->clientManager->createRequest('getSettings')) {
      if ($webhook = $settings->getWebhook($webhook_url)) {
        return $webhook['uuid'] == $webhook_uuid;
      }
    }
    return FALSE;
  }

  /**
   * Unregisters a Webhook from Content Hub.
   *
   * @param string $webhook_url
   *   The webhook URL.
   *
   * @return bool
   *   TRUE if successful unregistration, FALSE otherwise.
   */
  public function unregisterWebhook($webhook_url) {
    if ($settings = $this->clientManager->createRequest('getSettings')) {
      if ($webhook = $settings->getWebhook($webhook_url)) {
        if ($response = $this->clientManager->createRequest('deleteWebhook', [$webhook['uuid'], $webhook['url']])) {
          $success = json_decode($response->getBody(), TRUE);
          if (isset($success['success']) && $success['success'] == TRUE) {
            drupal_set_message(t('Webhooks have been <b>disabled</b>. This site will no longer receive updates from Content Hub.', [
              '@URL' => $webhook['url'],
            ]), 'warning');
            $this->config->clear('webhook_uuid')->clear('webhook_url')->save();
            return TRUE;
          }
        }
      }
      else {
        // If the webhook was not found in the Subscription settings but the
        // variables are still set, we should delete the variables to be in
        // sync with the subscription settings.
        $this->config->clear('webhook_uuid')->clear('webhook_url')->save();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Disconnects the client from the Content Hub.
   */
  public function disconnectClient() {
    $webhook_register = (bool) $this->config->get('webhook_uuid');
    $webhook_url = $this->config->get('webhook_url');
    // Un-register the webhook.
    if ($webhook_register) {
      $this->unregisterWebhook($webhook_url);
    }

    $this->config->delete();

    // Clear the cache for suggested client name after disconnecting the client.
    // @TODO: Use dependency injection for accessing the cache.
    $cache = \Drupal::cache('acquia_contenthub');
    $cache->delete("suggested_client_name");
    return FALSE;
  }

  /**
   * Lists Entities from the Content Hub.
   *
   * Example of how to structure the $options parameter:
   *
   * @param array $options
   *   The options array to search.
   *
   * @codingStandardsIgnoreStart
   *
   * $options = [
   *     'limit'  => 20,
   *     'type'   => 'node',
   *     'origin' => '11111111-1111-1111-1111-111111111111',
   *     'fields' => 'status,title,body,field_tags,description',
   *     'filters' => [
   *         'status' => 1,
   *         'title' => 'New*',
   *         'body' => '/Boston/',
   *     ],
   * ];
   *
   * @codingStandardsIgnoreEnd
   *
   * @return array|bool
   *   The result array or FALSE otherwise.
   */
  public function listEntities(array $options) {
    if ($entities = $this->clientManager->createRequest('listEntities', [$options])) {
      return $entities;
    }
    return FALSE;
  }

  /**
   * Purge ALL Entities in the Content Hub.
   *
   * Warning: This function has to be used with care because it has destructive
   * consequences to all data attached to the current subscription.
   *
   * @return string|bool
   *   Returns the json data of the entities list or FALSE if fails.
   */
  public function purgeEntities() {
    if ($list = $this->clientManager->createRequest('purge')) {
      return $list;
    }
    return FALSE;
  }

  /**
   * Wraps a request using HMAC authentication.
   *
   * If the current site is connected to Content Hub it wraps the request using
   * HMAC algorithm. If not connected, it just returns the same request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request to wrap using HMAC authentication.
   * @param bool|true $use_shared_secret
   *   Whether to use shared_secret or secret_key.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The HMAC wrapped request.
   */
  public function setHmacAuthorization(Request $request, $use_shared_secret = TRUE) {
    if ($this->clientManager->isConnected()) {
      $request->headers->set('Date', gmdate('D, d M Y H:i:s T'), TRUE);
      $secret = $use_shared_secret ? $this->getSharedSecret() : $this->config->get('secret_key');
      $signature = $this->clientManager->getRequestSignature($request, $secret);
      $request->headers->set('Authorization', 'Acquia ContentHub:' . $signature, TRUE);
    }
    return $request;
  }

}
