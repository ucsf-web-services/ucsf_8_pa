<?php

namespace EclipseGc\CommonConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

interface PlatformInterface extends CommandQuestionInterface {

  public const PLATFORM_TYPE_KEY = 'platform.type';

  public const PLATFORM_NAME_KEY = 'platform.name';

  public const PLATFORM_ALIAS_KEY = 'platform.alias';

  /**
   * Gets the platform id.
   *
   * @return string
   */
  public static function getPlatformId() : string ;

  /**
   * Extracts the alias string from a platform.
   *
   * @return string
   */
  public function getAlias() : string ;

  /**
   * Execute a command on the defined platform.
   *
   * This should perform the necessary steps to execute the command remotely on
   * the defined platform. They could be done, for example, by passing commands
   * inline across an ssh connections.
   *
   * Platforms are given the ProcessRunner as part of their instantiation. It
   * is suggested that all execute methods use the ProcessRunner to run their
   * processes.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   The command to execute.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output object.
   * 
   * @return int
   *   The process exit code.
   */
  public function execute(Command $command, InputInterface $input, OutputInterface $output) : int ;

  /**
   * ProcessRunner proxy method to allow a to response to specific output.
   *
   * @param \Symfony\Component\Process\Process $process
   *   The running process object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   * @param string $type
   *   The type of message from the process object.
   * @param string $buffer
   *   The message from the process object.
   */
  public function out(Process $process, OutputInterface $output, string $type, string $buffer) : void ;

  /**
   * Gets the configuration value.
   *
   * @param string $key
   *   The configuration key name.
   *
   * @return mixed
   *   The configuration value associated with the key name.
   *
   * @see \Consolidation\Config\ConfigInterface::get()
   */
  public function get(string $key);

  /**
   * Sets the configuration value.
   *
   * @param string $key
   *   The configuration key name.
   * @param mixed $value
   *   The value to associate to the key.
   *
   * @return $this
   *
   * @see \Consolidation\Config\ConfigInterface::set()
   */
  public function set(string $key, $value);

  /**
   * Export the config values as an array.
   *
   * @return array
   *
   * @see \Consolidation\Config\ConfigInterface::export()
   */
  public function export() : array ;

  /**
   * Saves the platform object.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function save() : PlatformInterface ;

}
