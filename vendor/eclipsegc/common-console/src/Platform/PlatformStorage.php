<?php

namespace EclipseGc\CommonConsole\Platform;

use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\PlatformDeleteEvent;
use EclipseGc\CommonConsole\Event\PlatformWriteEvent;
use EclipseGc\CommonConsole\Exception\MissingPlatformException;
use EclipseGc\CommonConsole\PlatformInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PlatformStorage
 *
 * @package EclipseGc\CommonConsole\Platform
 */
class PlatformStorage {

  const PLATFORM_LOCATION = [
    '.commonconsole',
    'platforms',
  ];

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The platform factory.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformFactory
   */
  protected $factory;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * DefaultFinder constructor.
   *
   * @param \Symfony\Component\Filesystem\Filesystem $filesystem
   *   The file system object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \EclipseGc\CommonConsole\Platform\PlatformFactory $factory
   *   The platform factory.
   */
  public function __construct(Filesystem $filesystem, EventDispatcherInterface $dispatcher, PlatformFactory $factory, LoggerInterface $logger) {
    $this->filesystem = $filesystem;
    $this->dispatcher = $dispatcher;
    $this->factory = $factory;
    $this->logger = $logger;
  }

  /**
   * Loads a platform object by alias.
   *
   * @param string $alias
   *   The alias of the platform to load.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface|null
   *   The platform object on success. NULL if it doesn't exist.
   */
  public function load(string $alias) : ?PlatformInterface {
    $directory = $this->ensureDirectory();
    $alias_file = implode(DIRECTORY_SEPARATOR, [$directory, "$alias.yml"]);
    if (!$this->filesystem->exists($alias_file)) {
      throw new MissingPlatformException(sprintf("Alias by name %s not found. Please check your available aliases and try again.", $alias));
    }
    $config = new Config(Yaml::parse(file_get_contents($alias_file)));
    return $this->getPlatformFactory()->getPlatform($config, $this);
  }

  /**
   * Returns all available platforms.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface[]
   *   The list of platforms.
   */
  public function loadAll(): array {
    $dir = $this->ensureDirectory();
    $iterator = new \DirectoryIterator($dir);
    $platforms = [];
    foreach ($iterator as $item) {
      if ($item->getExtension() !== 'yml') {
        continue;
      }
      $config = new Config(Yaml::parse(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dir, $item->getFilename()]))));
      try {
        $platforms[] = $this->getPlatformFactory()->getPlatform($config, $this);
      }
      catch (LogicException $e) {
        $this->logger->error($e->getMessage());
      }
    }

    return $platforms;
  }

  /**
   * Checks to see if a specific platform exists.
   *
   * @param string $name
   *   The platform to check to see if it exists.
   *
   * @return bool
   */
  public function exists(string $name) : bool {
    $directory = $this->ensureDirectory();
    $alias_file = implode(DIRECTORY_SEPARATOR, [$directory, "$name.yml"]);
    return $this->filesystem->exists($alias_file);
  }

  /**
   * Save a platform object.
   *
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   *   The platform to save.
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   *   Returns a fully loaded platform object on success. Null on failure.
   */
  public function save(PlatformInterface $platform) : PlatformInterface {
    $directory = $this->ensureDirectory();
    $alias = $platform->getAlias();
    $alias_file = implode(DIRECTORY_SEPARATOR, [$directory, "{$alias}.yml"]);
    $event = new PlatformWriteEvent($platform);
    $this->dispatcher->dispatch(CommonConsoleEvents::PLATFORM_WRITE, $event);
    if ($event->hasError()) {
      throw new \Exception(sprintf("Save aborted! An unexpected error occurred during save.\nERROR: %s", implode("\n", $event->getErrors())));
    }
    $this->filesystem->dumpFile($alias_file, Yaml::dump($platform->export(), 7));
    return $this->load($alias);
  }

  /**
   * Delete a platform.
   *
   * @param \EclipseGc\CommonConsole\PlatformInterface $platform
   *   The platform to delete.
   *
   * @throws \Exception
   */
  public function delete(PlatformInterface $platform) : void {
    $directory = $this->ensureDirectory();
    $alias_file = implode(DIRECTORY_SEPARATOR, [$directory, "{$platform->getAlias()}.yml"]);
    if (!$this->filesystem->exists($alias_file)) {
      throw new MissingPlatformException(sprintf("The expected alias file was missing from: %s.", $alias_file));
    }
    $event = new PlatformDeleteEvent($platform);
    $this->dispatcher->dispatch(CommonConsoleEvents::PLATFORM_DELETE, $event);
    if ($event->hasError()) {
      throw new \Exception(sprintf("Deletion aborted! An unexpected error occurred during deletion.\nERROR: %s", implode("\n", $event->getErrors())));
    }
    $this->filesystem->remove($alias_file);
  }

  /**
   * Ensure that the platform directory exists.
   *
   * @return string
   *   The location of the platform directory.
   */
  protected function ensureDirectory() : string {
    $dir_parts = static::PLATFORM_LOCATION;
    array_unshift($dir_parts, getenv('HOME'));
    $directory = implode(DIRECTORY_SEPARATOR, $dir_parts);
    if (!$this->filesystem->exists($directory)) {
      $this->filesystem->mkdir($directory);
    }
    return $directory;
  }

  /**
   * Gets the platform factory object to create platforms.
   *
   * @return \EclipseGc\CommonConsole\Platform\PlatformFactory
   */
  protected function getPlatformFactory() : PlatformFactory {
    return $this->factory;
  }

}
