<?php

namespace EclipseGc\CommonConsole\Event;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BootstrapEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class BootstrapEvent extends Event {

  /**
   * The platform type to bootstrap.
   *
   * @var string
   */
  protected $platformType;

  /**
   * The console command event which spawned this bootstrap.
   *
   * @var ConsoleCommandEvent
   */
  protected $consoleCommandEvent;

  /**
   * BootstrapEvent constructor.
   *
   * @param string $platformType
   *   The platform type.
   * @param ConsoleCommandEvent $event
   *   The console command event.
   */
  public function __construct(string $platformType, ConsoleCommandEvent $event) {
    $this->platformType = $platformType;
    $this->consoleCommandEvent = $event;
  }

  /**
   * Gets the string representing the platform type.
   *
   * @return string
   */
  public function getPlatformType(): string {
    return $this->platformType;
  }

  /**
   * Gets the console command event.
   *
   * @return ConsoleCommandEvent
   */
  public function getConsoleCommandEvent(): ConsoleCommandEvent {
    return $this->consoleCommandEvent;
  }

}
