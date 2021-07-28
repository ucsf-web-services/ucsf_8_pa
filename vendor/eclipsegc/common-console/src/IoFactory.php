<?php

namespace EclipseGc\CommonConsole;

use EclipseGc\CommonConsole\Event\CreateApplicationEvent;
use EclipseGc\CommonConsole\Event\OutputFormatterStyleEvent;
use EclipseGc\CommonConsole\OutputFormatter\BareOutputFormatter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class OutputFactory
 *
 * @package EclipseGc\CommonConsole
 */
class IoFactory {

  /**
   * Create the application.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher
   *
   * @return \Symfony\Component\Console\Application
   */
  public static function createApplication(EventDispatcherInterface $dispatcher) : Application {
    $application = new Application('CommonConsole', '0.0.1');
    $event = new CreateApplicationEvent($application);
    $dispatcher->dispatch(CommonConsoleEvents::CREATE_APPLICATION, $event);
    return $application;
  }

  /**
   * Creates a new ConsoleOutput object and applies formatter styles.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   *
   * @return \Symfony\Component\Console\Output\ConsoleOutput
   *   The ConsoleOutput with full formatter styling applied.
   */
  public static function createOutput(EventDispatcherInterface $dispatcher, InputInterface $input) {
    if ($input->hasParameterOption('--bare')) {
      $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, FALSE, new BareOutputFormatter());
      return $output;
    }
    else {
      $output = new ConsoleOutput();
    }
    $event = new OutputFormatterStyleEvent();
    $dispatcher->dispatch(CommonConsoleEvents::OUTPUT_FORMATTER_STYLE, $event);
    foreach ($event->getFormatterStyles() as $name => $style) {
      if (!$output->getFormatter()->hasStyle($name)) {
        $output->getFormatter()->setStyle($name, $style);
      }
    }
    return $output;
  }

}
