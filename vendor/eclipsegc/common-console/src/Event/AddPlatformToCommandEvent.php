<?php

namespace EclipseGc\CommonConsole\Event;

use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AddPlatformToCommandEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class AddPlatformToCommandEvent extends Event {

  /**
   * The expectation string.
   *
   * This could be a class name, an interface, a simple constant. Any string we
   * can make a comparison against to determine if the platform is valid for
   * the target command.
   *
   * @var string
   */
  protected $expectation;

  /**
   * The incoming platform object.
   *
   * @var \EclipseGc\CommonConsole\PlatformInterface
   */
  protected $platform;

  /**
   * The alias used to load the platform.
   *
   * @var string
   */
  protected $alias;

  /**
   * Whether or not the platform matches the expectation.
   *
   * Defaults to false.
   *
   * @var bool
   */
  protected $platformMatchesExpectation = FALSE;

  /**
   * AddPlatformToCommandEvent constructor.
   *
   * @param string $expectation
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   * @param string $alias
   */
  public function __construct(string $expectation, PlatformInterface $platform, string $alias) {
    $this->expectation = $expectation;
    $this->platform = $platform;
    $this->alias = $alias;
  }

  /**
   * @return string
   */
  public function getExpectation(): string {
    return $this->expectation;
  }

  /**
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function getPlatform(): PlatformInterface {
    return $this->platform;
  }

  /**
   * @return string
   */
  public function getAlias(): string {
    return $this->alias;
  }

  /**
   * @param bool $value
   */
  public function setPlatformExpectationMatch(bool $value): void {
    $this->platformMatchesExpectation = $value;
  }

  /**
   * @return bool
   */
  public function platformMatchesExpectation(): bool {
    return $this->platformMatchesExpectation;
  }

}
