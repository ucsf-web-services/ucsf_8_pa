<?php

namespace EclipseGc\CommonConsole;

use Consolidation\Config\ConfigInterface;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface PlatformDependencyInjectionInterface.
 *
 * @package EclipseGc\CommonConsole
 */
interface PlatformDependencyInjectionInterface {

  /**
   * Returns this object using the container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container service.
   * @param \Consolidation\Config\ConfigInterface $config
   *   The configuration object used for platform instantiation.
   * @param \EclipseGc\CommonConsole\ProcessRunner $runner
   *   The process runner service.
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage service.
   *
   * @return static
   *   New instance of this class.
   */
  public static function create(ContainerInterface $container, ConfigInterface $config, ProcessRunner $runner, PlatformStorage $storage): self;

}
