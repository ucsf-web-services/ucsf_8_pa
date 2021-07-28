<?php

namespace EclipseGc\CommonConsole\EventSubscriber\Bootstrap;

use Composer\Autoload\ClassLoader;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Drupal
 *
 * Used in the context of a Drupal site instance to bootstrap Drupal to a
 * functional state.
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\Bootstrap
 */
class Drupal implements EventSubscriberInterface {
  /**
   * The Composer class loader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  protected $loader;

  /**
   * ContentHubAudit constructor.
   *
   * @param \Composer\Autoload\ClassLoader $autoloader
   *   The composer class loader.
   * @param string $autoloadFile
   *   The location of the autoload file.
   */
  public function __construct(ClassLoader $autoloader) {
    $this->loader = $autoloader;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::PLATFORM_BOOTSTRAP] = 'onPlatformBootstrap';
    return $events;
  }

  /**
   * Identifies drupal8 platforms and attempts to bootstrap them.
   *
   * @param \EclipseGc\CommonConsole\Event\BootstrapEvent $bootstrapEvent
   *   The bootstrap event.
   */
  public function onPlatformBootstrap(BootstrapEvent $bootstrapEvent) {
    if ($bootstrapEvent->getPlatformType() === 'drupal8') {
      if (!class_exists(DrupalKernel::class)) {
        $consoleEvent = $bootstrapEvent->getConsoleCommandEvent();
        $consoleEvent->getOutput()->writeln("<warning>No Drupal installation was found. This command must be run against a Drupal installation.</warning>");
        $consoleEvent->disableCommand();
        return;
      }

      $input = $bootstrapEvent->getConsoleCommandEvent()->getInput();
      $uri = $input->getOption('uri');
      // Set up super globals before creating a request.
      if ($uri) {
        $this->setServerGlobals($uri);
      }
      $request = Request::createFromGlobals();
      $kernel = new DrupalKernel('prod', $this->loader, FALSE);
      chdir($kernel->getAppRoot());
      $kernel->handle($request);
    }
  }

  /**
   * Set up the $_SERVER globals so that Drupal will see the same values
   * that it does when serving pages via the web server.
   */
  protected function setServerGlobals($uri) {
    // Fake the necessary HTTP headers that Drupal needs:
    if ($uri) {
      $drupal_base_url = parse_url($uri);
      // If there's no url scheme set, add http:// and re-parse the url
      // so the host and path values are set accurately.
      if (!array_key_exists('scheme', $drupal_base_url)) {
        $uri = 'http://' . $uri;
        $drupal_base_url = parse_url($uri);
      }
      // Fill in defaults.
      $drupal_base_url += array(
        'path' => '',
        'host' => NULL,
        'port' => NULL,
      );
      $_SERVER['HTTP_HOST'] = $drupal_base_url['host'];

      if ($drupal_base_url['scheme'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
      }

      if ($drupal_base_url['port']) {
        $_SERVER['HTTP_HOST'] .= ':' . $drupal_base_url['port'];
      }
      $_SERVER['SERVER_PORT'] = $drupal_base_url['port'];

      $_SERVER['REQUEST_URI'] = $drupal_base_url['path'] . '/';
    }
    else {
      $_SERVER['HTTP_HOST'] = 'default';
      $_SERVER['REQUEST_URI'] = '/';
    }

    $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'] . 'index.php';
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'];
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_METHOD']  = 'GET';

    $_SERVER['SERVER_SOFTWARE'] = NULL;
    $_SERVER['HTTP_USER_AGENT'] = NULL;
  }

}
