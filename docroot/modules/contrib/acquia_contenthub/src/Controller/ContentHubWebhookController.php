<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Acquia\ContentHubClient\hmacv1\ResponseSigner;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\acquia_contenthub\ContentHubSubscription;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Controller for Content Hub Imported Entities.
 */
class ContentHubWebhookController extends ControllerBase {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Current Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */

  protected $configFactory;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManagerInterface
   */
  protected $clientManager;

  /**
   * Drupal Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription
   */
  protected $contentHubSubscription;

  /**
   * The Drupal Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * WebhooksSettingsForm constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Drupal Module Handler.
   * @param \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription
   *   The Content Hub Subscription.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, Request $current_request, ConfigFactoryInterface $config_factory, ClientManagerInterface $client_manager, ModuleHandlerInterface $module_handler, ContentHubSubscription $contenthub_subscription) {
    $this->loggerFactory = $logger_factory;
    $this->request = $current_request;
    $this->configFactory = $config_factory;
    $this->clientManager = $client_manager;
    $this->moduleHandler = $module_handler;
    $this->contentHubSubscription = $contenthub_subscription;
    // Get the content hub config settings.
    $this->config = $this->configFactory->get('acquia_contenthub.admin_settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory */
    $logger_factory = $container->get('logger.factory');
    /** @var \Symfony\Component\HttpFoundation\Request $current_request */
    $current_request = $container->get('request_stack')->getCurrentRequest();
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager */
    $client_manager = $container->get('acquia_contenthub.client_manager');
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription */
    $contenthub_subscription = $container->get('acquia_contenthub.acquia_contenthub_subscription');

    return new static(
      $logger_factory,
      $current_request,
      $config_factory,
      $client_manager,
      $module_handler,
      $contenthub_subscription
    );
  }

  /**
   * Process a webhook.
   *
   * @return \Acquia\ContentHubClient\ResponseSigner|\Symfony\Component\HttpFoundation\Response
   *   The response object.
   */
  public function receiveWebhook() {
    // Obtain the headers.
    $request = Request::createFromGlobals();
    $webhook = $request->getContent();
    $response = NULL;

    if ($this->validateWebhookSignature($request)) {
      // Notify about the arrival of the webhook request.
      $args = [
        '@whook' => print_r($webhook, TRUE),
      ];
      $message = new FormattableMarkup('Webhook landing: @whook', $args);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);

      if ($webhook = Json::decode($webhook)) {
        // Verification process successful!
        // Now we can process the webhook.
        if (isset($webhook['status'])) {
          switch ($webhook['status']) {
            case 'successful':
              $response = $this->processWebhook($webhook);
              break;

            case 'pending':
              $response = $this->registerWebhook($webhook);
              break;

            case 'shared_secret_regenerated':
              $response = $this->updateSharedSecret($webhook);
              break;

            default:
              // If any other webhook we are not processing then just display
              // the response.
              $response = new Response('');
              break;

          }
        }
      }

    }
    else {
      $ip_address = $this->request->getClientIp();
      $message = new FormattableMarkup('Webhook [from IP = @IP] rejected (Signatures do not match): @whook', [
        '@IP' => $ip_address,
        '@whook' => print_r($webhook, TRUE),
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
      $response = new Response('');
    }

    return $response;
  }

  /**
   * Validates a webhook signature.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Webhook Request.
   *
   * @return bool
   *   TRUE if signature verification passes, FALSE otherwise.
   */
  public function validateWebhookSignature(Request $request) {
    $headers = array_map('current', $request->headers->all());
    $webhook = $request->getContent();

    // Quick validation to make sure we are not replaying a request
    // from the past.
    $request_date = isset($headers['date']) ? $headers['date'] : "1970";
    $request_timestamp = strtotime($request_date);
    $timestamp = time();
    // Due to networking delays and mismatched clocks, we are making the request
    // accepting window 60s.
    if (abs($request_timestamp - $timestamp) > 900) {
      $message = new FormattableMarkup('The Webhook request seems that was issued in the past [Request timestamp = @t1, server timestamp = @t2]: rejected: @whook', [
        '@t1' => $request_timestamp,
        '@t2' => $timestamp,
        '@whook' => print_r($webhook, TRUE),
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
      return FALSE;
    }

    // Reading webhook endpoint:
    $path = $request->getBasePath() . $request->getPathInfo();
    $webhook_url = $this->config->get('webhook_url') ?: $path;
    $url = parse_url($webhook_url);
    $webhook_path = $url['path'];
    $webhook_path .= isset($url['query']) ? '?' . $url['query'] : '';

    $authorization_header = isset($headers['authorization']) ? $headers['authorization'] : '';
    // Reading type of webhook request.
    $webhook_array = Json::decode($webhook);
    $status = $webhook_array['status'];
    $authorization = '';

    // Constructing the message to sign.
    switch ($status) {
      case 'shared_secret_regenerated':
        $this->contentHubSubscription->getSettings();
        $secret_key = $this->contentHubSubscription->getSharedSecret();
        $signature = $this->clientManager->getRequestSignature($request, $secret_key);
        $authorization = 'Acquia Webhook:' . $signature;
        $this->loggerFactory->get('acquia_contenthub')->debug('Received Webhook for shared secret regeneration. Settings updated.');
        break;

      case 'successful':
      case 'processing':
      case 'in-queue':
      case 'failed':
        $secret_key = $this->contentHubSubscription->getSharedSecret();
        $signature = $this->clientManager->getRequestSignature($request, $secret_key);
        $authorization = 'Acquia Webhook:' . $signature;
        break;

      case 'pending':
        $api = $this->config->get('api_key');
        $secret_key = $this->config->get('secret_key');
        $signature = $this->clientManager->getRequestSignature($request, $secret_key);

        $authorization = "Acquia $api:" . $signature;
        break;

    }

    // Log debug information if validation fails.
    if ($authorization !== $authorization_header) {
      $message = new FormattableMarkup('The Webhook request failed HMAC validation. [authorization = %authorization]. [authorization_header = %authorization_header]', [
        '%authorization' => $authorization,
        '%authorization_header' => $authorization_header,
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);

    }

    return (bool) ($authorization === $authorization_header);
  }

  /**
   * Enables other modules to process the webhook.
   *
   * @param array $webhook
   *   The webhook sent by the Content Hub.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The Response Object.
   */
  public function processWebhook(array $webhook) {
    // Process Reindex Webhook.
    if ($webhook['crud'] === 'reindex') {
      $this->processReindexWebhook($webhook);
      return new Response('');
    }
    $assets = isset($webhook['assets']) ? $webhook['assets'] : FALSE;
    if (count($assets) > 0) {
      $this->moduleHandler->alter('acquia_contenthub_process_webhook', $webhook);
    }
    else {
      $message = new FormattableMarkup('Error processing Webhook (It contains no assets): @whook', [
        '@whook' => print_r($webhook, TRUE),
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
    }
    return new Response('');
  }

  /**
   * Processing the registration of a webhook.
   *
   * @param array $webhook
   *   The webhook coming from Plexus.
   *
   * @return \Acquia\ContentHubClient\ResponseSigner|\Symfony\Component\HttpFoundation\Response
   *   The Response.
   */
  public function registerWebhook(array $webhook) {
    $uuid = isset($webhook['uuid']) ? $webhook['uuid'] : FALSE;
    $origin = $this->config->get('origin');
    $api_key = $this->config->get('api_key');

    if ($uuid && $webhook['initiator'] == $origin && $webhook['publickey'] == $api_key) {
      $secret = $this->config->get('secret_key');

      // Creating a response.
      $response = new ResponseSigner($api_key, $secret);
      $response->setContent('{}');
      $response->setResource('');
      $response->setStatusCode(ResponseSigner::HTTP_OK);
      $response->signWithCustomHeaders(FALSE);
      $response->signResponse();
      return $response;
    }
    else {
      $ip_address = $this->request->getClientIp();
      $message = new FormattableMarkup('Webhook [from IP = @IP] rejected (initiator and/or publickey do not match local settings): @whook', [
        '@IP' => $ip_address,
        '@whook' => print_r($webhook, TRUE),
      ]);
      $this->loggerFactory->get('acquia_contenthub')->debug($message);
      return new Response('');
    }
  }

  /**
   * Process a Reindex Webhook.
   *
   * @param array $webhook
   *   The webhook array.
   */
  private function processReindexWebhook(array $webhook) {
    // Update the Reindex State Variable.
    /** @var \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex */
    $reindex = \Drupal::service('acquia_contenthub.acquia_contenthub_reindex');
    if ($reindex->isReindexSent()) {
      $reindex->setReindexStateFinished();
    }
  }

}
