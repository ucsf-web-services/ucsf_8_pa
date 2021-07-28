<?php

namespace EclipseGc\CommonConsole\Event;

use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class OutputFormatterStyleEvent
 *
 * @package EclipseGc\CommonConsole\Event
 */
class OutputFormatterStyleEvent extends Event {

  /**
   * The output formatter styles.
   *
   * @var OutputFormatterStyleInterface[]
   */
  protected $styles = [];

  /**
   * Add output formatter styles to the output.
   *
   * @param string $name
   *   The name of the output formatter.
   * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style
   *   The output formatter style interface.
   *
   * @throws \Exception
   */
  public function addOutputFormatterStyle(string $name, OutputFormatterStyleInterface $style) {
    if (!empty($this->styles[$name])) {
      throw new \Exception(sprintf("The output formatter style of name %s is already set by another event subscriber.", $name));
    }
    $this->styles[$name] = $style;
  }

  /**
   * Gets the set output formatter styles.
   *
   * @return OutputFormatterStyleInterface[]
   */
  public function getFormatterStyles() : array {
    return $this->styles;
  }

}
