<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformFactoryInterface;
use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetPlatformTypeEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class GetPlatformTypeEvent extends Event {

  /**
   * @var string
   */
  protected $platformType;

  /**
   * @var string
   */
  protected $class;

  /**
   * @var string
   */
  protected $factory = '';

  /**
   * GetPlatformTypeEvent constructor.
   *
   * @param string $platformType
   *   The platform type.
   */
  public function __construct(string $platformType) {
    $this->platformType = $platformType;
  }

  /**
   * Get the platform type intended to be instantiated.
   *
   * @return string
   */
  public function getPlatformType() : string {
    return $this->platformType;
  }

  /**
   * Add a class to represent the platform.
   *
   * @param string $class
   *   The class that represents the platform.
   *
   * @throws \Exception
   */
  public function addClass(string $class) {
    if (!in_array(PlatformInterface::class, class_implements($class))) {
      throw new \Exception(sprintf("Invalid Platform class. Must implement %s.", PlatformInterface::class));
    }
    $this->class = $class;
  }

  /**
   * Get the class to instantiate for the platform.
   *
   * @return string
   */
  public function getClass() {
    return $this->class;
  }

}
