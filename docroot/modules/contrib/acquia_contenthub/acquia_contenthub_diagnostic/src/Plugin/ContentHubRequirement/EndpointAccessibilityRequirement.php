<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an endpoint accessibility requirement.
 *
 * @ContentHubRequirement(
 *   id = "endpoint_accessibility",
 *   title = @Translation("Endpoint accessibility"),
 * )
 */
class EndpointAccessibilityRequirement extends ContentHubRequirementBase {

  /**
   * The request options required to ensure an appropriate request.
   */
  const REQUEST_OPTIONS = [
    'verify' => FALSE,
    'cookies' => FALSE,
    'allow_redirects' => FALSE,
    'http_errors' => FALSE,
  ];

  /**
   * The description to be shown to the user.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The domain which will be calculated and used for requests.
   *
   * @var string
   */
  protected $domain = '';

  /**
   * The Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManagerInterface
   */
  protected $clientManager;

  /**
   * Verify the accessibility of the content hub endpoints.
   */
  public function verify() {
    // If Content Hub is not installed there is no reason to run this test.
    if (!$this->moduleHandler->moduleExists('acquia_contenthub')) {
      return REQUIREMENT_OK;
    }

    $this->clientManager = \Drupal::service('acquia_contenthub.client_manager');
    if (!$this->clientManager->isConnected()) {
      $this->setValue($this->t('Endpoint validation failed'));
      $this->setDescription($this->t('Client Manager not connected. Configure Content Hub and try again.'));
      return REQUIREMENT_ERROR;
    }

    $this->domain = $this->getDomain();

    try {
      $this->testWebhookEndpoint();
      $this->testContentViewEndpoint();

      if ($this->description) {
        $this->setValue($this->t('Endpoint validation failed'));
        $this->setDescription($this->description);
        return REQUIREMENT_ERROR;
      }
    }
    catch (\Exception $e) {
      $this->setValue($this->t('Cannot validate endpoints'));
      $this->setDescription($this->t('Verify your server and site configuration and try again. Error: @error', [
        '@error' => $e->getMessage(),
      ]));
      return REQUIREMENT_ERROR;
    }

    return REQUIREMENT_OK;
  }

  /**
   * Test the webhook endpoint.
   */
  protected function testWebhookEndpoint() {
    $endpoint_name = 'Webhook Endpoint';
    $endpoint_url = '/acquia-contenthub/webhook';
    $client = \Drupal::httpClient();
    $response = $client->get($this->domain . $endpoint_url, static::REQUEST_OPTIONS);
    $status_code = $response->getStatusCode();
    $this->description .= $this->parseStatusCode($status_code, $endpoint_name, $endpoint_url);
  }

  /**
   * Test the content view endpoint.
   */
  protected function testContentViewEndpoint() {
    $endpoint_name = 'Content View Endpoint';
    $test_nid = $this->getTestNid();
    if ($test_nid) {
      $endpoint_url = $this->domain . '/acquia-contenthub/display/node/' . $test_nid . '/default';

      $authorization = $this->getAuthorizationHeader($endpoint_url);

      // HTTP Client only accepts Guzzle Requests.
      $request = new GuzzleRequest('GET',
        $endpoint_url,
        ['authorization' => $authorization]
      );
      $client = \Drupal::httpClient();
      $response = $client->send($request, static::REQUEST_OPTIONS);
      $status_code = $response->getStatusCode();
      $this->description .= $this->parseStatusCode($status_code, $endpoint_name, $endpoint_url);
    }
    else {
      $endpoint_url = '/acquia-contenthub/display/node/{entity_id}/default';
      $this->description .= $this->t("@endpoint_name (@endpoint_url) requires a node to exist on the site in order to test. Create a node and try again.", [
        '@endpoint_name' => $endpoint_name,
        '@endpoint_url' => $endpoint_url,
      ]);
    }
  }

  /**
   * Get the Nid of a published node on the site.
   *
   * @return int|bool
   *   Nid of a published node. FALSE if none exist.
   */
  protected function getTestNid() {
    $nodes = \Drupal::entityQuery('node')
      ->condition('status', 1)->range(0, 1)
      ->execute();

    if (count($nodes)) {
      return array_pop($nodes);
    }
    return FALSE;
  }

  /**
   * Parse the status code and return the error, if applicable.
   *
   * @param int $status_code
   *   Status code of response.
   * @param string $endpoint_name
   *   Human readable name of endpoint.
   * @param string $endpoint_url
   *   Url of endpoint.
   *
   * @return string
   *   Error to be added to the description.
   */
  protected function parseStatusCode($status_code, $endpoint_name, $endpoint_url) {
    // 200 response is the only successful response.
    if ($status_code == 200) {
      return '';
    }
    // 30x response is a redirect. Plexus will not follow redirects.
    elseif (substr($status_code, 0, 1) == 3) {
      return $this->t("@endpoint_name (@endpoint_url) received a @status_code response indicating a redirect. The Content Hub service will not follow redirects.\n", [
        '@endpoint_name' => $endpoint_name,
        '@endpoint_url' => $endpoint_url,
        '@status_code' => $status_code,
      ]);
    }
    // 401 response is unauthorized. Shield or HTTP Auth is in the way.
    elseif ($status_code == 401) {
      return $this->t("@endpoint_name (@endpoint_url) received a 401 response indicating that credentials are required. Disable the Shield module or any other authentication method being used on the endpoint.\n", [
        '@endpoint_name' => $endpoint_name,
        '@endpoint_url' => $endpoint_url,
      ]);
    }
    // 404 not found. The route cannot be reached.
    elseif ($status_code == 404) {
      return $this->t("@endpoint_name (@endpoint_url) received a 404 response indicating that the endpoint cannot be reached.\n", [
        '@endpoint_name' => $endpoint_name,
        '@endpoint_url' => $endpoint_url,
      ]);
      // Give a generic error for all other responses.
    }
    else {
      return $this->t("@endpoint_name (@endpoint_url) received a @status_code response. Please ensure a 200 response is sent.\n", [
        '@endpoint_name' => $endpoint_name,
        '@endpoint_url' => $endpoint_url,
        '@status_code' => $status_code,
      ]);
    }
  }

  /**
   * Gets the authorization header for a given endpoint and method.
   *
   * @param string $endpoint
   *   Url of endpoint.
   * @param string $method
   *   Method of the request. (e.g GET or POST).
   *
   * @return string
   *   The authorization header.
   */
  protected function getAuthorizationHeader($endpoint, $method = 'GET') {
    $request = Request::create($endpoint, $method);
    $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');
    $shared_secret = $subscription->getSharedSecret();
    $signature = $this->clientManager->getRequestSignature($request, $shared_secret);
    return 'Acquia ContentHub:' . $signature;
  }

}
