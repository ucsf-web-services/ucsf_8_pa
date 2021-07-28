<?php

namespace EclipseGc\CommonConsole\Platform;

use EclipseGc\CommonConsole\PlatformInterface;

/**
 * Interface PlatformSitesInterface
 *
 * @package Acquia\ContentHubCli\Platform
 */
interface PlatformSitesInterface extends PlatformInterface {

  /**
   * Get a list of sites associated to the platform.
   *
   * @return array
   */
  public function getPlatformSites(): array ;

}
