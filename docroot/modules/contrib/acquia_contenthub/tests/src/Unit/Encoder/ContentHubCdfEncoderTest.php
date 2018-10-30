<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Encoder;

use Drupal\acquia_contenthub\Encoder\ContentHubCdfEncoder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\acquia_contenthub\Encoder\ContentHubCdfEncoder
 * @group acquia_contenthub
 */
class ContentHubCdfEncoderTest extends UnitTestCase {

  /**
   * Tests the supportsEncoding() method.
   */
  public function testSupportsEncoding() {
    $encoder = new ContentHubCdfEncoder();

    $this->assertTrue($encoder->supportsEncoding('acquia_contenthub_cdf'));
  }

}
