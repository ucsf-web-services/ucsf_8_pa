<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class FindAliasEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class FindAliasEvent extends Event {

  /**
   * The alias of the platform.
   *
   * @var string
   */
  protected $alias;

  /**
   * The console command event we interrupted.
   *
   * @var \Symfony\Component\Console\Event\ConsoleCommandEvent
   */
  protected $event;

  /**
   * The platform the alias represents.
   *
   * @var \EclipseGc\CommonConsole\PlatformInterface
   */
  protected $platform;

  public function __construct(string $alias, ConsoleCommandEvent $event) {
    $this->alias = $alias;
    $this->event = $event;
  }

  /**
   * Get the alias string name.
   *
   * @return string
   */
  public function getAlias() : string {
    return $this->alias;
  }

  /**
   * Get the ConsoleCommandEvent that was intercepted for alias identification.
   *
   * @return \Symfony\Component\Console\Event\ConsoleCommandEvent
   */
  public function getEvent() : ConsoleCommandEvent {
    return $this->event;
  }

  /**
   * Allows event subscribers to set a platform object for the alias.
   *
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   *   The platform object to use for the alias.
   */
  public function setPlatform(PlatformInterface $platform) {
    $this->platform = $platform;
  }

  /**
   * Get the platform for this alias if any is available.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface|null
   */
  public function getPlatform() : ?PlatformInterface {
    return $this->platform;
  }

}
