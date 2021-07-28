<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Tests\amp\Functional\BasicTestCaseBase;

/**
 * Test basic functionality of AMP Rdf.
 *
 * @group amp
 */
class AmpRdf extends BasicTestCaseBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rdf',
    'amp',
    'amp_rdf',
    'node',
    'metatag',
    'schema_metatag',
    'token',
    'views',
  ];

}
