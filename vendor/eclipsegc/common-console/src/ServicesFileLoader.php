<?php

namespace EclipseGc\CommonConsole;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServicesFileLoader extends YamlFileLoader {

  /**
   * Manually invoke private methods on the super class.
   *
   * Many of the methods provided by YamlFileLoader are private, but the public
   * load method only thinks in terms of singular paths found by the locator.
   * We want the locator to retrieve ALL paths that match our criteria. Since
   * the parent class doesn't think in terms of multiple matches, we wrap the
   * existing logic in a foreach loop, and proxy to this method to invoke
   * upstream private calls.
   *
   * @param $method
   * @param mixed ...$args
   *
   * @return mixed
   * @throws \ReflectionException
   */
  protected function getPrivateMethod($method, ...$args)
  {
    $method = new \ReflectionMethod(YamlFileLoader::class, $method);
    $method->setAccessible(TRUE);
    return $method->invoke($this, ...$args);
  }

  /**
   * Manually set private properties on the super class.
   *
   * @param $property
   * @param $value
   *
   * @throws \ReflectionException
   */
  protected function getPrivateProperty($property, $value)
  {
    $property = new \ReflectionProperty(YamlFileLoader::class, $property);
    $property->setAccessible(TRUE);
    $property->setValue($this, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function load($resource, $type = null)
  {
    $paths = $this->locator->locate($resource, NULL, FALSE);
    foreach ($paths as $path) {

      $content = $this->loadFile($path);

      $this->container->fileExists($path);

      // empty file
      if (NULL === $content) {
        return;
      }

      // imports
      $this->getPrivateMethod('parseImports', $content, $path);

      // parameters
      if (isset($content['parameters'])) {
        if (!\is_array($content['parameters'])) {
          throw new InvalidArgumentException(sprintf('The "parameters" key should contain an array in %s. Check your YAML syntax.', $path));
        }

        foreach ($content['parameters'] as $key => $value) {
          $this->container->setParameter($key, $this->getPrivateMethod('resolveServices', $value, $path, TRUE));
        }
      }

      // extensions
      $this->getPrivateMethod('loadFromExtensions', $content);

      // services
      $this->getPrivateProperty('anonymousServicesCount', 0);

      $this->getPrivateProperty('anonymousServicesSuffix', '~' . ContainerBuilder::hash($path));

      $this->setCurrentDir(\dirname($path));
      try {
        $this->getPrivateMethod('parseDefinitions', $content, $path);
      } finally {
        $this->instanceof = [];
        if (method_exists($this, 'registerAliasesForSinglyImplementedInterfaces')) {
          $this->registerAliasesForSinglyImplementedInterfaces();
        }
      }
    }
  }

}
