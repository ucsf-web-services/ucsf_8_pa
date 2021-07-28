<?php

namespace Acquia\Console\Cloud\Tests;

/**
 * Trait TestFixtureHelperTrait.
 *
 * @package Acquia\Console\Cloud\Tests
 */
trait TestFixtureHelperTrait {

  /**
   * Fixture path.
   *
   * @var string
   */
  protected static $fixtures = __DIR__ . '/Fixtures/';

  /**
   * Returns a fixture.
   *
   * @param string $file_name
   *   File name.
   *
   * @return mixed
   *   The value of the fixture.
   */
  protected function getFixture(string $file_name) {
    return include self::$fixtures . $file_name;
  }

  /**
   * Returns the contents of the fixture.
   *
   * @param string $file_name
   *   File name.
   *
   * @return false|string
   *   The contents of the file.
   */
  protected function getFixtureContents(string $file_name) {
    return file_get_contents(self::$fixtures . $file_name);
  }

}
