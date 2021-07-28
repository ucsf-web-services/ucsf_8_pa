<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PlatformWriteEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class PlatformWriteEvent extends Event {

  /**
   * @var \EclipseGc\CommonConsole\PlatformInterface
   */
  protected $platform;

  /**
   * @var array
   */
  protected $errors = [];

  /**
   * PlatformWriteEvent constructor.
   *
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   *   The platform to be written.
   */
  public function __construct(PlatformInterface $platform) {
    $this->platform = $platform;
  }

  /**
   * Get the alias string for the platform.
   *
   * @return string
   */
  public function getAlias() : string {
    // @todo be better.
    return $this->platform->getAlias();
  }

  /**
   * Get the platform to be saved.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function getPlatform() : PlatformInterface {
    return $this->platform;
  }

  /**
   * Adds errors encountered during the event.
   *
   * @param string $error
   *   The specific error message.
   */
  public function addError(string $error) : void {
    $this->errors[] = $error;
  }

  /**
   * Whether or not any errors were encountered during the event.
   *
   * @return bool
   */
  public function hasError() : bool {
    return (bool) $this->errors;
  }

  /**
   * Gets any logged errors encountered by the event.
   *
   * @return array
   */
  public function getErrors() : array {
    return $this->errors;
  }

}
