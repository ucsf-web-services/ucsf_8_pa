<?php

namespace Drupal\acquia_contenthub;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\acquia_contenthub\Session\ContentHubUserSession;

/**
 * Provides a method to do internal sub-requests to obtain an entity CDF.
 */
class ContentHubInternalRequest {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The Basic HTTP Kernel to make requests.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $kernel;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription
   */
  protected $contentHubSubscription;

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * The renderer.
   *
   * @var \Drupal\acquia_contenthub\Session\ContentHubUserSession
   */
  protected $renderUser;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Public Constructor.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
   *   The HttpKernel.
   * @param \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription
   *   The Content Hub Subscription.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The Account Switcher Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Request Stack.
   */
  public function __construct(HttpKernelInterface $kernel, ContentHubSubscription $contenthub_subscription, AccountSwitcherInterface $account_switcher, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, RequestStack $request_stack) {
    $this->kernel = $kernel;
    $this->contentHubSubscription = $contenthub_subscription;
    $this->accountSwitcher = $account_switcher;
    $this->loggerFactory = $logger_factory;
    $this->renderUser = new ContentHubUserSession($config_factory->get('acquia_contenthub.entity_config')->get('user_role'));
    $this->requestStack = $request_stack;
  }

  /**
   * Implements the static interface create method.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_kernel.basic'),
      $container->get('acquia_contenthub.acquia_contenthub_subscription'),
      $container->get('account_switcher'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * Makes an internal HMAC-authenticated request to the site to obtain CDF.
   *
   * @param string $entity_type
   *   The Entity type.
   * @param string $entity_id
   *   The Entity ID.
   * @param bool $include_references
   *   Whether to include referenced entities in the CDF.
   *
   * @return array
   *   The CDF array.
   */
  public function getEntityCdfByInternalRequest($entity_type, $entity_id, $include_references = TRUE) {
    global $base_path;

    // Creating a fake user account to give as context to the normalization.
    $this->accountSwitcher->switchTo($this->renderUser);

    try {
      $params = [
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        $entity_type => $entity_id,
        '_format' => 'acquia_contenthub_cdf',
      ];
      if ($include_references) {
        $params['include_references'] = 'true';
      }
      $url = Url::fromRoute('acquia_contenthub.entity.' . $entity_type . '.GET.acquia_contenthub_cdf', $params)->toString();
      $url = str_replace($base_path, '/', $url);

      // Creating an internal HMAC-signed request.
      $master_request = $this->requestStack->getCurrentRequest();
      $request = Request::create($url, 'GET', [], $master_request->cookies->all(), [], $master_request->server->all());
      $request = $this->contentHubSubscription->setHmacAuthorization($request, TRUE);

      /** @var \Drupal\Core\Render\HtmlResponse $response */
      $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      $entity_cdf_json = $response->getContent();
      $bulk_cdf = Json::decode($entity_cdf_json);
    }
    catch (\Exception $e) {
      // Do nothing, route does not exist, just log a message about it.
      $this->loggerFactory->get('acquia_contenthub')->debug('Exception: %msg', ['%msg' => $e->getMessage()]);
      $bulk_cdf = [];
    }

    $this->accountSwitcher->switchBack();

    // Test to make sure CDF returned an entities array.
    if (isset($bulk_cdf['message'])) {
      $this->loggerFactory->get('acquia_contenthub')->debug('Exception: %msg', ['%msg' => $bulk_cdf['message']]);
      $bulk_cdf = [];
    }
    // Return CDF.
    return empty($bulk_cdf) ? ['entities' => []] : $bulk_cdf;
  }

}
