<?php

use Composer\Autoload\ClassLoader;
use EclipseGc\CommonConsole\CommandCompilerPass;
use EclipseGc\CommonConsole\ServicesFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

/** @var \Composer\Autoload\ClassLoader $autoloader */
[$autoloader, $autoloaderFile] = require __DIR__ . "/autoload.php";

$containerBuilder = new class($autoloader, $autoloaderFile) {

  /**
   * @var \Composer\Autoload\ClassLoader
   */
  protected $autoloader;

  /**
   * @var string
   */
  protected $autoloaderFile;

  /**
   * The absolute path of the vendor directory.
   *
   * @var string
   */
  protected $vendorDir;

  /**
   *  constructor.
   *
   * @param \Composer\Autoload\ClassLoader $autoloader
   *   The class autoloader.
   * @param string $autoloaderFile
   *   The location of the class autoloader file.
   */
  public function __construct(ClassLoader $autoloader, string $autoloaderFile) {
    $this->autoloader = $autoloader;
    $this->autoloaderFile = $autoloaderFile;
    $this->vendorDir = realpath(dirname($autoloaderFile));
    if (!$this->vendorDir) {
      throw new \Exception(sprintf("The autoloader file's vendor directory could not be found as a real path in the file system. Autoloader file was specified as: %s", $autoloaderFile));
    }
  }

  /**
   * Build a working container.
   *
   * This builds the Container, finds commoncli.services.yml service
   * definitions and loads them via the CommandCompilerPass. We also setup the
   * needed elements of event dispatching/subscribing here and then compile and
   * return the built container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  public function getContainer() {
    $container = new ContainerBuilder();
    $locations = array_merge($this->getComposerDirs(), $this->getInstalledDirectories());
    $loader = new ServicesFileLoader($container, new FileLocator($locations));
    $loader->load('commoncli.services.yml');
    $container->setParameter('autoloader', $this->autoloader);
    $container->setParameter('autoload.file', $this->autoloaderFile);
    $container->addCompilerPass(new RegisterListenersPass());
    $container->addCompilerPass(new CommandCompilerPass());
    $container->compile();
    return $container;
  }

  /**
   * Extracts commoncli service definition locations from the autoloader.
   *
   * @return array
   */
  private function getComposerDirs(): array {
    $locations = [];
    // @todo figure out fallbacks.
    $autoloader_locations = array_merge($this->autoloader->getPrefixes(), $this->autoloader->getPrefixesPsr4());
    foreach ($autoloader_locations as $namespace => $directories) {
      foreach ($directories as $directory) {
        if (substr($directory, -4) === DIRECTORY_SEPARATOR . 'src') {
          $directory = substr($directory, 0, -4);
        }
        $directory = realpath($directory);
        $locations[$directory] = $directory;
      }
    }
    return $locations;
  }

  /**
   * Extracts commoncli service definition locations from the installed.json.
   *
   * @return array
   */
  private function getInstalledDirectories(): array {
    $locations = [];
    $installed = file_get_contents("{$this->vendorDir}/composer/installed.json");
    if ($installed) {
      $installed = json_decode($installed);
      $packages = isset($installed->packages) && is_array($installed->packages) ? $installed->packages : $installed;
      foreach ($packages as $package) {
        if (!is_object($package)) {
          continue;
        }
        if (file_exists("{$this->vendorDir}/{$package->name}") && $directory = realpath("{$this->vendorDir}/{$package->name}")) {
          $locations[$directory] = $directory;
        }
      }
    }
    return $locations;
  }

};

return $containerBuilder->getContainer();
