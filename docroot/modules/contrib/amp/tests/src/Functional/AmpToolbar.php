<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Tests\amp\Functional\BasicTestCaseBase;

/**
 * Test basic functionality of AMP Toolbar.
 *
 * @group amp
 */
class AmpToolbarWorks extends BasicTestCaseBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'toolbar',
    'amp',
    'amp_toolbar',
    'node',
    'metatag',
    'schema_metatag',
    'token',
    'views',
  ];

}
