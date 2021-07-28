<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Core\Url;

/**
 * Tests AMP view mode.
 *
 * @group amp
 */
class AmpViewModeTest extends AmpTestBase {

  /**
   * Test the AMP view mode is viewable and contains basic metadata.
   */
  public function testAmpViewMode() {

    // Create a node to test AMP field formatters.
    $this->createAmpNode();
    $text = $this->node->get('body')->value;
    $title = $this->node->get('title')->value;

    // Check that the AMP view mode is available.
    $view_modes_url = Url::fromRoute('entity.entity_view_mode.collection')->toString();
    $this->drupalGet($view_modes_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('AMP');

    // Check the metadata of the full display mode.
    $this->drupalGet($this->nodeUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($text);
    $this->assertSession()->responseContains($title);
    $this->assertSession()->responseContains('link rel="amphtml" href="' . $this->nodeAmpUrl() . '"');
    $this->assertSession()->responseHeaderEquals('Link', '<' . $this->nodeAmpUrl() . '> rel="amphtml"');

    // Check the metadata of the AMP display mode.
    $this->drupalGet($this->nodeAmpUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($text);
    $this->assertSession()->responseContains($title);
    $this->assertSession()->responseContains('link rel="canonical" href="' . $this->nodeUrl() . '"');

  }

}
