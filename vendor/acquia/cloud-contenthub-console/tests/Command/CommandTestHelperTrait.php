<?php

namespace Acquia\Console\Cloud\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Trait CommandTestHelperTrait.
 *
 * @package Acquia\Console\Cloud\Tests\Command
 */
trait CommandTestHelperTrait {

  /**
   * Initiate a command tester.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   Command instance.
   *
   * @return \Symfony\Component\Console\Tester\CommandTester
   *   Command tester instance.
   */
  public function getCommandTester(Command $command): CommandTester {
    $helper = new QuestionHelper();
    $command->setHelperSet(new HelperSet(['question' => $helper]));
    return new CommandTester($command);
  }

  /**
   * Runs command.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   Command instance.
   * @param array $input
   *   Question inputs.
   * @param array $args
   *   Command arguments.
   *
   * @return object
   *   Command tester instance after calling execute.
   */
  public function doRunCommand(Command $command, array $input, array $args = []): object {
    if (!$command->getDefinition()->hasArgument('alias')) {
      $command->addArgument('alias', InputArgument::OPTIONAL, '', '');
    }

    $command_tester = $this->getCommandTester($command);
    $command_tester->setInputs($input);
    $command_tester->execute($args);

    return $command_tester;
  }

}
