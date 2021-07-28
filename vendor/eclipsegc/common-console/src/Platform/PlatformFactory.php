<?php

namespace EclipseGc\CommonConsole\Platform;

use Consolidation\Config\ConfigInterface;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;
use EclipseGc\CommonConsole\PlatformDependencyInjectionInterface;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\ProcessRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

/**
 * Class PlatformFactory
 *
 * This class converts Consolidation\Config\ConfigInterface objects into
 * PlatformInterface objects. For saved data, use ::getPlatform() which will
 * load the proper PlatformInterface object with the config data in it.
 *
 * For data that has not yet been saved, use ::getMockPlatformFromConfig().
 * This will create a mock PlatformInterface implementation which can be saved
 * by the PlatformStorage::save() method.
 *
 * @package EclipseGc\CommonConsole\Platform
 */
class PlatformFactory {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The process runner.
   *
   * @var \EclipseGc\CommonConsole\ProcessRunner
   */
  protected $runner;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  public function __construct(EventDispatcherInterface $dispatcher, ProcessRunner $runner, ContainerInterface $container) {
    $this->dispatcher = $dispatcher;
    $this->runner = $runner;
    $this->container = $container;
  }

  /**
   * Instantiates a platform via its factory or through a naive new statement.
   *
   * @param \Consolidation\Config\ConfigInterface $config
   *   The configuration values to use for instantiating the platform.
   *
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function getPlatform(ConfigInterface $config, PlatformStorage $storage) : PlatformInterface {
    $event = new GetPlatformTypeEvent($config->get(PlatformInterface::PLATFORM_TYPE_KEY));
    $this->dispatcher->dispatch(CommonConsoleEvents::GET_PLATFORM_TYPE, $event);

    $class = $event->getClass();
    if (!$class || !class_exists($class)) {
      throw new LogicException("Platform definition is missing for '{$config->get('platform.alias')}'!");
    }

    if (in_array(PlatformDependencyInjectionInterface::class, class_implements($class))) {
      return $class::create($this->container, $config, $this->runner, $storage);
    }
    
    return new $class($config, $this->runner, $storage);
  }

  /**
   * Creates a mock Platform object from provided configuration.
   *
   * PlatformStorage can save mock objects. When it does, it will automatically
   * identify the actual platform type, and instantiate and return the platform
   * class that is appropriate. However since you need a platform to save a
   * platform in the first place, this method provides a handy way of getting a
   * platform object when all you have is a ConfigInterface object.
   *
   * Mostly this is only useful during initial creation of a platform. At all
   * other times, you will have a fully instantiated platform object.
   *
   * @param \Consolidation\Config\ConfigInterface $config
   *   The config for the mock platform.
   *
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   */
  public function getMockPlatformFromConfig(ConfigInterface $config, PlatformStorage $storage) : PlatformInterface {
    // Create a mock platform object to save
    return new class($config, $storage) implements PlatformInterface {

      protected $config = [];
      protected $storage;

      public function __construct(ConfigInterface $config, PlatformStorage $storage) {
        $this->config = $config;
        $this->storage = $storage;
      }
      public function getAlias(): string {
        return $this->config->get(PlatformInterface::PLATFORM_ALIAS_KEY);
      }
      public static function getQuestions() {}
      public static function getPlatformId(): string {}
      public function execute(Command $command, InputInterface $input, OutputInterface $output): int {}
      public function out(Process $process, OutputInterface $output, string $type, string $buffer): void {}
      public function get(string $key) {
        return $this->config->get($key);
      }
      public function set(string $key, $value) : self {
        $this->config->set($key, $value);
        return $this;
      }
      public function export() : array {
        return $this->config->export();
      }
      public function save() : PlatformInterface {
        return $this->storage->save($this);
      }
    };
  }

}
