<?php

namespace Acquia\Console\Cloud\Command;

use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use EclipseGc\CommonConsole\Command\PlatformBootStrapCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudMultiSites.
 *
 * @package Acquia\Console\Cloud\Command
 */
class AcquiaCloudMultiSites extends Command implements PlatformBootStrapCommandInterface {

  use PlatformCmdOutputFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:multi:sites';

  /**
   * {@inheritdoc}
   */
  public function getPlatformBootstrapType(): string {
    return 'drupal8';
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Gathers sites URIs from multi site environment.');
    $this->setHidden(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $uris = $this->getMultiSiteUris();

    if (!empty($uris)) {
      $output->writeln($this->toJsonSuccess([
        'sites' => $uris,
      ]));
    }

    return 0;
  }

  /**
   * Returns keys of $sites array from sites.php.
   *
   * @return array
   *   Site URIs.
   */
  protected function getMultiSiteUris(): array {
    $kernel = \Drupal::service('kernel');
    $directories = [
      $kernel->getAppRoot(),
      "{$kernel->getAppRoot()}/{$kernel->getSitePath()}",
    ];

    $sites = [];

    foreach ($directories as $directory) {
      if (file_exists("$directory/sites/sites.php")) {
        include "$directory/sites/sites.php";
        return $sites ?? [];
      }
    }

    return [];
  }

}
