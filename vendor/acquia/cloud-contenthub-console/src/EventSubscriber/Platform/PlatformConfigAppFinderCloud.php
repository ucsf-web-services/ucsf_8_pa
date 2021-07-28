<?php

namespace Acquia\Console\Cloud\EventSubscriber\Platform;

use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use AcquiaCloudApi\Endpoints\Environments;
use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\PlatformConfigEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

/**
 * Class PlatformConfigAppFinderCloud.
 *
 * @package Acquia\Console\Cloud\EventSubscriber\Platform
 */
class PlatformConfigAppFinderCloud implements EventSubscriberInterface {

  /**
   * Platform types to handle within this subscriber.
   *
   * @var array
   *   Platform types.
   */
  protected $platformTypes = [
    'Acquia Cloud',
    'Acquia Cloud Multi Site'
  ];

  /**
   * AcquiaCloudClientFactory service instance.
   *
   * @var \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory
   */
  protected $factory;

  /**
   * PlatformConfigAppFinderCloud constructor.
   *
   * @param \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory $factory
   *   ACE Factory.
   */
  public function __construct(AcquiaCloudClientFactory $factory) {
    $this->factory = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[CommonConsoleEvents::PLATFORM_CONFIG] = ['onPlatformConfig', 100];
    return $events;
  }

  /**
   * Writes info about vendor dir paths into the platform config.
   *
   * @param \EclipseGc\CommonConsole\Event\PlatformConfigEvent $event
   *   PlatformConfigEvent instance.
   */
  public function onPlatformConfig(PlatformConfigEvent $event) {
    $output = $event->getOutput();
    $config = $event->getConfig();
    if (!in_array($config->get('platform.type'), $this->platformTypes, TRUE)) {
      return;
    }
    $output->writeln('<info>Console now trying to locate vendor directory within your platform.</info>');
    $paths = $this->locateVendorDirectory($config);
    foreach ($paths as $env_id => $path) {
      if (!$path) {
        $event->addError("<error>Cannot find vendor directory in environment: $env_id.</error>");
      }
    }

    if ($event->hasError()) {
      $event->stopPropagation();
      return;
    }

    $config->set(AcquiaCloudPlatform::ACE_VENDOR_PATHS, $paths);
    $output->writeln('<info>Vendor directory located successfully and saved in your platform configuration.</info>');
  }

  /**
   * Gets info about vendor dir paths for every environment within platform.
   *
   * @param \Consolidation\Config\Config $config
   *   Config instance.
   *
   * @return array
   *   Array containing path information. Key: env_id, value: path.
   */
  protected function locateVendorDirectory(Config $config) {
    $environments = new Environments($this->factory->fromCredentials($config->get('acquia.cloud.api_key'), $config->get('acquia.cloud.api_secret')));
    $env_ids = $this->getEnvironmentIds($config);

    $vendor_paths = [];
    foreach ($env_ids as $env_id) {
      $environment = $environments->get($env_id);
      $sshUrl = $environment->sshUrl;
      [, $url] = explode('@', $sshUrl);
      [$application] = explode('.', $url);
      $executable_path = $this->getPathToExecutable($sshUrl, $application);
      $vendor_paths[$env_id] = substr($executable_path, 0, strpos($executable_path, 'vendor'));
    }

    return $vendor_paths;
  }

  /**
   * Gets environment ids from platform configuration.
   *
   * @param \Consolidation\Config\Config $config
   *   Config instance.
   *
   * @return array
   *   Environment ids.
   */
  protected function getEnvironmentIds(Config $config): array {
    return $config->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS);
  }

  /**
   * Runs command on platform to look for executables.
   *
   * @param string $ssh_url
   *   SSH url of platform site.
   * @param string $application
   *   Application name.
   *
   * @return string
   *   Executable path.
   */
  protected function getPathToExecutable(string $ssh_url, string $application): string {
    $command = "ssh $ssh_url 'cd /var/www/html/$application; find . -executable -type f | grep commoncli'";
    $process = new Process($command);
    $process->run();
    return trim($process->getOutput());
  }

}
