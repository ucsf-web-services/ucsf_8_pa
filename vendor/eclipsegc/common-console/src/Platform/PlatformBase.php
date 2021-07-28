<?php

namespace EclipseGc\CommonConsole\Platform;

use Consolidation\Config\ConfigInterface;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\ProcessRunner;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class PlatformBase
 *
 * @package EclipseGc\CommonConsole\Platform
 */
abstract class PlatformBase implements PlatformInterface {

  /**
   * The platform configuration.
   *
   * @var \Consolidation\Config\ConfigInterface
   */
  protected $config;

  /**
   * The process runner.
   *
   * @var \EclipseGc\CommonConsole\ProcessRunner
   */
  protected $runner;

  /**
   * The platform storage.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $storage;

  /**
   * SshPlatform constructor.
   *
   * @param \Consolidation\Config\ConfigInterface $config
   *   The configuration for this platform.
   * @param \EclipseGc\CommonConsole\ProcessRunner $runner
   *   The process runner.
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage.
   */
  public function __construct(ConfigInterface $config, ProcessRunner $runner, PlatformStorage $storage) {
    $this->config = $config;
    $this->runner = $runner;
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlias(): string {
    return $this->get(PlatformInterface::PLATFORM_ALIAS_KEY);
  }

  /**
   * {@inheritdoc}
   */
  public function out(Process $process, OutputInterface $output, string $type, string $buffer) : void {}

  /**
   * {@inheritdoc}
   */
  public function get(string $key) {
    return $this->config->get($key);
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $key, $value) : self {
    $this->config->set($key, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function export() : array {
    return $this->config->export();
  }

  /**
   * {@inheritdoc}
   */
  public function save() : PlatformInterface {
    return $this->storage->save($this);
  }

}
