<?php

namespace EclipseGc\CommonConsole\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetPlatformTypesEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class GetPlatformTypesEvent extends Event {

  /**
   * @var string[]
   */
  protected $platformTypes;

  /**
   * Add a platform type.
   *
   * @param string $platform_type
   *   The platform type to add.
   */
  public function addPlatformType(string $platform_type) {
    $this->platformTypes[] = $platform_type;
  }

  /**
   * Get the platform types.
   *
   * @return array
   */
  public function getPlatformTypes() : array {
    return $this->platformTypes;
  }

}
