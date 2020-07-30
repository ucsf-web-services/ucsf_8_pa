<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Tests\amp\Functional\BasicTestCaseBase;

/**
 * Test basic functionality of AMP Adsense.
 *
 * @group amp
 */
class AmpAdSense extends BasicTestCaseBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'adsense',
    'amp',
    'node',
    'schema_metatag',
    'token',
    'views',
  ];

}
