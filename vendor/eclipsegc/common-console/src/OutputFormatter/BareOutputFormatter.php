<?php

namespace EclipseGc\CommonConsole\OutputFormatter;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class BareOutputFormatter implements OutputFormatterInterface {

  public function setDecorated($decorated) {
    // TODO: Implement setDecorated() method.
  }

  public function isDecorated() {
    return FALSE;
  }

  public function setStyle($name, OutputFormatterStyleInterface $style) {
    // TODO: Implement setStyle() method.
  }

  public function hasStyle($name) {
    return FALSE;
  }

  public function getStyle($name) {
    throw new InvalidArgumentException(sprintf('Undefined style: "%s".', $name));
  }

  public function format($message) {
    return $message;
  }


}
