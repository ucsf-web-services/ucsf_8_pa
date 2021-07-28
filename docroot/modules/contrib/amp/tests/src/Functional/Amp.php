<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Tests\amp\Functional\BasicTestCaseBase;

/**
 * Test basic functionality of AMP.
 *
 * @group amp
 */
class Amp extends BasicTestCaseBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'amp',
    'node',
    'metatag',
    'schema_metatag',
    'token',
    'views',
  ];

}
