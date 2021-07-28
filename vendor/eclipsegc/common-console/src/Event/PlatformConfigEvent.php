<?php

namespace EclipseGc\CommonConsole\Event;

use Consolidation\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PlatformConfigEvent.
 *
 * @package EclipseGc\CommonConsole\Event
 */
class PlatformConfigEvent extends Event {

  /**
   * @var array
   */
  protected $errors = [];

  /**
   * The config being changed.
   *
   * @var \Consolidation\Config\Config
   */
  protected $config;

  /**
   * InputInterface instance.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * OutputInterface instance.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * PlatformConfigEvent constructor.
   *
   * @param \Consolidation\Config\Config $config
   *   Config to modify.
   */
  public function __construct(Config $config, InputInterface $input, OutputInterface $output) {
    $this->config = $config;
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Gets config instance.
   *
   * @return \Consolidation\Config\Config
   */
  public function getConfig() : Config {
    return $this->config;
  }

  /**
   * Gets InputInterface instance.
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * Gets OutputInterface instance.
   *
   * @return \Symfony\Component\Console\Output\OutputInterface
   */
  public function getOutput() {
    return $this->output;
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
