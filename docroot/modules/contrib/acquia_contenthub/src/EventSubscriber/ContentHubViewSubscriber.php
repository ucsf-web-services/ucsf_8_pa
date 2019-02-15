<?php

namespace Drupal\acquia_contenthub\EventSubscriber;

use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * View subscriber rendering main content render arrays into responses.
 *
 * Additional target rendering formats can be defined by adding another service
 * that implements \Drupal\Core\Render\MainContent\MainContentRendererInterface
 * and tagging it as a @code render.main_content_renderer @endcode, then
 * \Drupal\Core\Render\MainContent\MainContentRenderersPass will detect it and
 * use it when appropriate.
 *
 * @see \Drupal\Core\Render\MainContent\MainContentRendererInterface
 * @see \Drupal\Core\Render\MainContentControllerPass
 */
class ContentHubViewSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * The render user session.
   *
   * @var \Drupal\acquia_contenthub\Session\ContentHubUserSession
   */
  protected $renderAccount;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * ContentHubViewSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The Account Switcher Service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(RouteMatchInterface $route_match, ConfigFactoryInterface $config_factory, AccountSwitcherInterface $account_switcher, LoggerChannelFactoryInterface $logger_factory) {
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
    $this->accountSwitcher = $account_switcher;
    $this->renderAccount = new ContentHubUserSession($this->configFactory->get('acquia_contenthub.entity_config')->get('user_role'));
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Switch to the render Content Hub user.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
   *   The filter controller event.
   */
  public function onKernelController(FilterControllerEvent $event) {
    if ($this->routeMatch->getRouteName() === 'acquia_contenthub.content_entity_display.entity') {
      $this->accountSwitcher->switchTo($this->renderAccount);
    }
  }

  /**
   * Switch back from the render Content Hub user.
   *
   * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
   *   The finish request event.
   */
  public function onKernelFinishRequest(FinishRequestEvent $event) {
    if ($this->routeMatch->getRouteName() === 'acquia_contenthub.content_entity_display.entity') {
      $this->accountSwitcher->switchBack();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::CONTROLLER     => ['onKernelController'],
      KernelEvents::FINISH_REQUEST => ['onKernelFinishRequest'],
    ];
  }

}
