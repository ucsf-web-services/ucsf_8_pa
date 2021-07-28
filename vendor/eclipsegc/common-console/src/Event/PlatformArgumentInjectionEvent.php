<?php

namespace EclipseGc\CommonConsole\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PlatformArgumentInjectionEvent.
 *
 * Dispatched right before a command is executed on a platform.
 *
 * @see \EclipseGc\CommonConsole\Event\Traits\PlatformArgumentInjectionTrait
 *
 * @package EclipseGc\CommonConsole\Event
 */
class PlatformArgumentInjectionEvent extends Event {

  /**
   * The command's input object.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * The list of sites in a platform.
   *
   * @var array
   */
  protected $sites;

  /**
   * The current command.
   *
   * @var string
   */
  protected $command;

  /**
   * Decorated input containing the mapping between sites and their args.
   *
   * @var array
   */
  protected $decoratedInput;

  /**
   * PlatformArgumentInjectionEvent constructor.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The current command's input object.
   * @param array $sites
   *   The list of sites.
   * @param \Symfony\Component\Console\Command\Command $command
   *   The current command.
   */
  public function __construct(InputInterface $input, array $sites, Command $command) {
    $this->input = $input;
    $this->sites = $sites;
    $this->command = $command;

    // Set a default value.
    $this->setDecoratedInput(array_fill_keys($sites, NULL));
  }

  /**
   * The available sites for the given platform.
   *
   * @return array
   *   The list of sites. Should be used as key.
   */
  public function getSites(): array {
    return $this->sites;
  }

  /**
   * Returns the name of the current command.
   *
   * @return string
   *   The name of the command.
   */
  public function getCommandName(): string {
    return $this->command->getName();
  }

  /**
   * Returns the input in hand.
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   *   The current command's input object.
   */
  public function getInput(): InputInterface {
    return $this->input;
  }

  /**
   * Returns the decorated input.
   *
   * @return array
   *   The list of inputs mapped to platform sites.
   */
  public function getDecoratedInput(): array {
    return $this->decoratedInput;
  }

  /**
   * Maps customized arguments to sites derived from a platform.
   *
   * Example:
   * ```php
   *   [
   *     'site_name' => [
   *       '--option' => 'option_value',
   *       'some_argument' => 'arg_value',
   *     ],
   *   ]
   * ```
   *
   * @param array $arguments
   *   An associative array containing the sites and the customized input.
   *
   * @throws \Exception
   */
  public function setDecoratedInput(array $arguments): void {
    $args = $this->input->getArguments();
    $options = $this->input->getOptions();
    $definition = $this->command->getDefinition();
    foreach ($options as $key => $val) {
      $option = $definition->getOption($key);
      // Option value must be TRUE so it can be qualified as a used option.
      if ($option->acceptValue() && (bool) $val === TRUE) {
        $options["--$key"] = $val;
      }
      else {
        // Insert the option into the array IF it was actually used.
        if ((bool) $val === TRUE) {
          $options["--$key"] = NULL;
        }
      }

      unset($options[$key]);
    }

    $res = [];
    foreach ($arguments as $site => $params) {
      if (!in_array($site, $this->sites, TRUE)) {
        throw new \Exception("No such site: $site");
      }

      if ($params !== NULL) {
        foreach ($params as $arg => $val) {
          if ($this->isOption($arg)) {
            $options[$arg] = $val;
            continue;
          }

          $args[$arg] = $val;
        }
      }

      $res[$site] = new ArrayInput(array_merge(
        ['command' => $this->getCommandName()] + $args + ['--uri' => $site],
        $options
      ));
    }

    $this->decoratedInput = $res;
  }

  /**
   * Checks if the provided parameter is an option.
   *
   * @param string $arg
   *   The parameter to check.
   *
   * @return bool
   *   TRUE if it is an option, in other words, it starts with --/-
   */
  protected function isOption(string $arg): bool {
    return (bool) preg_match('/-(-)?\w.*/', $arg);
  }

}
