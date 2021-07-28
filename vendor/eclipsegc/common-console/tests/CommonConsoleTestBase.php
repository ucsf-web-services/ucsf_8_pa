<?php

namespace EclipseGc\CommonConsole\Tests;

use EclipseGc\CommonConsole\OutputFormatter\BareOutputFormatter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class CommonConsoleTestBase extends TestCase {

  use ProphecyTrait;

  /**
   * The service container used for testing.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  protected function getContainer(): ContainerBuilder {
    if (empty($this->container)) {
      $this->container = require realpath(__DIR__ . '/../includes/container.php');
    }
    return $this->container;
  }

  /**
   * Get an OutputInterface implementation we can introspect for testing.
   *
   * @return \Symfony\Component\Console\Output\OutputInterface
   */
  protected function getTestOutput(): OutputInterface {
    $output = new class implements OutputInterface {

      protected $messages = [];

      protected $newline = [];

      protected $options = [];

      public function getOutput() : array {
        return [
          'messages' => $this->messages,
          'newline' => $this->newline,
          'options' => $this->options,
        ];
      }

      public function write($messages, $newline = FALSE, $options = 0) {
        $count = count($this->messages);
        $this->messages[$count] = $messages;
        $this->newline[$count] = $newline;
        $this->options[$count] = $options;
      }

      public function writeln($messages, $options = 0) {
        $count = count($this->messages);
        $this->messages[$count] = $messages;
        $this->options[$count] = $options;
      }

      public function setVerbosity($level) {}

      public function getVerbosity() {
        return self::VERBOSITY_QUIET;
      }

      public function isQuiet() {
        return TRUE;
      }

      public function isVerbose() {
        return FALSE;
      }

      public function isVeryVerbose() {
        return FALSE;
      }

      public function isDebug() {
        return FALSE;
      }

      public function setDecorated($decorated) {
        return FALSE;
      }

      public function isDecorated() {
        return FALSE;
      }

      public function setFormatter(OutputFormatterInterface $formatter) {}

      public function getFormatter() {
        return new BareOutputFormatter();
      }
    };
    return $output;
  }

}
