<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\EventDispatcher\Event;

class PlatformDeleteEvent extends Event {

  /**
   * The platform being deleted.
   *
   * @var \EclipseGc\CommonConsole\PlatformInterface
   */
  protected $platform;

  /**
   * @var array
   */
  protected $errors = [];

  /**
   * PlatformDeleteEvent constructor.
   *
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   *   The platform to delete.
   */
  public function __construct(PlatformInterface $platform) {
    $this->platform = $platform;
  }

  /**
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
