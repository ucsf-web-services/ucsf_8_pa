<?php

namespace EclipseGc\CommonConsole\Tests\Platform;

use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FooPlatform implements PlatformInterface {

  /**
   * @var array
   */
  protected $values;

  /**
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $storage;

  public function __construct(array $values, PlatformStorage $storage) {
    $this->values = $values;
    $this->storage = $storage;
  }

  public static function getQuestions() {
    return [];
  }

  public static function getPlatformId(): string {
    return 'FOOTEST';
  }

  public function getAlias(): string {
    return $this->get(PlatformInterface::PLATFORM_ALIAS_KEY);
  }

  public function execute(Command $command, InputInterface $input, OutputInterface $output): int {
    // TODO: Implement execute() method.
  }

  public function out(Process $process, OutputInterface $output, string $type, string $buffer): void {
    // TODO: Implement out() method.
  }

  public function get(string $key) {
    return $this->values[$key];
  }

  public function set(string $key, $value) {
    $this->values[$key] = $value;
  }

  public function export(): array {
    return $this->values;
  }

  public function save(): PlatformInterface {
    return $this->storage->save($this);
  }

}
