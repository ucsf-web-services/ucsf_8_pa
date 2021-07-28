<?php

namespace EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\FindAliasEvent;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AliasFinder
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand
 */
class AliasFinder implements EventSubscriberInterface {

  const ALIAS_PATTERN = '%^@([a-zA-Z0-9_-]+)(\.[a-zA-Z0-9_-]+)?(\.[a-zA-Z0-9_-]+)?$%';

  /**
   * AliasFinder constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  protected $dispatcher;

  /**
   * AliasFinder constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConsoleEvents::COMMAND] = ['onConsoleCommand', 100];
    return $events;
  }

  /**
   * Finds alias platforms on which to invoke commands.
   *
   * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
   *   The console command event.
   */
  public function onConsoleCommand(ConsoleCommandEvent $event) {
    /** @var \Symfony\Component\Console\Input\InputInterface $input */
    $input = $event->getInput();
    $output = $event->getOutput();
    $command = $event->getCommand();
    $platform_input = $this->getPlatformInput($input);
    foreach ($input->getArguments() as $name => $argument) {
      if (preg_match(self::ALIAS_PATTERN, $argument)) {
        $alias = substr($argument, 1);
        $findAliasEvent = new FindAliasEvent($alias, $event);
        $this->dispatcher->dispatch(CommonConsoleEvents::ALIAS_FIND, $findAliasEvent);
        if (!$findAliasEvent->getPlatform()) {
          $output->writeln("<error>" . sprintf("Alias by name %s not found. Please check your available aliases and try again.", $alias) . "</error>");
          $event->disableCommand();
          $event->stopPropagation();
          return;
        }
        elseif ($command instanceof PlatformCommandInterface) {
          $command->addPlatform($alias, $findAliasEvent->getPlatform());
          continue;
        }
        else {
          $findAliasEvent->getPlatform()->execute($command, $platform_input, $output);
          $event->disableCommand();
          $event->stopPropagation();
        }
      }
    }
  }

  protected function getPlatformInput(InputInterface $input) {
    $property = new \ReflectionProperty($input, 'definition');
    $property->setAccessible(TRUE);
    /** @var \Symfony\Component\Console\Input\InputDefinition $definition */
    $definition = $property->getValue($input);
    $arrayInput = ['command' => $input->getArgument('command')];
    foreach ($input->getArguments() as $key => $argument) {
      if (preg_match(self::ALIAS_PATTERN, $argument)) {
        continue;
      }
      $arrayInput[$key] = $argument;
    }
    foreach ($input->getOptions() as $key => $option_value) {
      $option = $definition->getOption($key);
      if ($option->getDefault() !== $option_value) {
        if ($option->acceptValue()) {
          $arrayInput["--$key"] = $option_value;
        }
        else {
          $arrayInput["--$key"] = NULL;
        }
      }
    }
    $arrayInput["--bare"] = NULL;
    return new ArrayInput($arrayInput, $definition);
  }

}
