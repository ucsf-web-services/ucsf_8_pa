<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\Event;

class FilterPlatformSites extends Event {

  /**
   * @var \Symfony\Component\Console\Command\Command
   */
  protected $command;

  /**
   * @var \EclipseGc\CommonConsole\PlatformInterface
   */
  protected $platform;

  /**
   * @var array
   */
  protected $sites;

  /**
   * FilterPlatformSites constructor.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   * @param array $sites
   */
  public function __construct(Command $command, PlatformInterface $platform, array $sites) {
    $this->command = $command;
    $this->platform = $platform;
    $this->sites = $sites;
  }

  /**
   * @return \Symfony\Component\Console\Command\Command
   */
  public function getCommand(): \Symfony\Component\Console\Command\Command {
    return $this->command;
  }

  /**
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function getPlatform(): \EclipseGc\CommonConsole\PlatformInterface {
    return $this->platform;
  }

  /**
   * @return array
   */
  public function getPlatformSites(): array {
    return $this->sites;
  }

  /**
   * @param array $sites
   */
  public function setPlatformSites(array $sites): void {
    $this->sites = $sites;
  }

}
