<?php

namespace EclipseGc\CommonConsole\Command;

/**
 * Interface PlatformBootStrapCommandInterface
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand
 */
interface PlatformBootStrapCommandInterface {

  /**
   * The type of bootstrap to perform on the platform.
   *
   * This can be for local or remote commands. The return value should be a
   * simple string that other event subscribers can use to identify your
   * bootstrap requirement and react accordingly.
   *
   * @return string
   */
  public function getPlatformBootstrapType() : string ;

}