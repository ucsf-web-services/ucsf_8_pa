<?php

namespace EclipseGc\CommonConsole;

use Consolidation\Config\ConfigInterface;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;

/**
 * Interface PlatformFactoryInterface
 *
 * @package EclipseGc\CommonConsole
 */
interface PlatformFactoryInterface {

  /**
   * Custom instantiation of PlatformInterface objects.
   *
   * PlatformInterface object sometimes require services in order to be
   * instantiated. When this is true, a PlatformFactory facilitates this.
   *
   * @param \EclipseGc\CommonConsole\Event\GetPlatformTypeEvent $event
   * @param \Consolidation\Config\ConfigInterface $config
   * @param \EclipseGc\CommonConsole\ProcessRunner $runner
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function create(GetPlatformTypeEvent $event, ConfigInterface $config, ProcessRunner $runner) : PlatformInterface ;

}
