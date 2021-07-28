<?php

namespace EclipseGc\CommonConsole;

use EclipseGc\CommonConsole\Event\OutputFormatterStyleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

class ProcessRunner {

  /**
   * The input object.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * The output formatters.
   *
   * @var \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface[]
   */
  protected $formatters = [];

  /**
   * ProcessRunner constructor.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   */
  public function __construct(InputInterface $input) {
    $this->input = $input;
  }

  public function getOutputStyles(EventDispatcherInterface $dispatcher) {
    $event = new OutputFormatterStyleEvent();
    $dispatcher->dispatch(CommonConsoleEvents::OUTPUT_FORMATTER_STYLE, $event);
    $this->formatters = $event->getFormatterStyles();
  }

  /**
   * Get the command string which was executed from a given input object.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   *
   * @return string
   *   The command that was executed.
   *
   * @throws \ReflectionException
   */
  public function getCommandString(InputInterface $input) {
    $string = '';
    $string .= implode(' ', $input->getArguments());
    $definition = new \ReflectionProperty($input, 'definition');
    $definition->setAccessible(TRUE);
    /** @var \Symfony\Component\Console\Input\InputDefinition $definition */
    $definition = $definition->getValue($input);
    foreach ($input->getOptions() as $option_name => $option_value) {
      $option = $definition->getOption($option_name);
      if ($option->getDefault() !== $option_value) {
        if ($option->acceptValue()) {
          $string .= " --$option_name=$option_value";
        }
        else {
          $string .= "--$option_name";
        }
      }
    }
    return $string;
  }

  /**
   * Run a process, setup a generic callable for processing STDOUT or STDERR.
   *
   * This will also proxy all STDOUT or STDERR data to the Platform which
   * allows the platform object to inspect messages for specific messages to
   * which it might want to react.
   *
   * @param \Symfony\Component\Process\Process $process
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @param int $timeout
   *   Process timeout.
   */
  public function run(Process $process, PlatformInterface $platform, OutputInterface $output, int $timeout = 600) {
    foreach ($this->formatters as $name => $style) {
      if (!$output->getFormatter()->hasStyle($name)) {
        $output->getFormatter()->setStyle($name, $style);
      }
    }
    $process->setTimeout($timeout);
    return $process->run(function ($type, $buffer) use ($process, $output, $platform) {
      $platform->out($process, $output, $type, $buffer);
      if (Process::ERR === $type) {
        $output->writeln("<error>$buffer</error>");
      } else {
        $output->writeln($buffer);
      }
    });
  }

}
