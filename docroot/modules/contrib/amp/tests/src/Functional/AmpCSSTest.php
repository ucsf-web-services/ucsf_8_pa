<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP view mode.
 *
 * @group amp
 */
class AmpCSSTest extends AmpTestBase {

  /**
   * Test the CSS gets correctly rendered.
   */
  public function testCss() {

    // Create a node to test css.
    $this->createAmpNode();
    $text = $this->node->get('body')->value;
    $title = $this->node->get('title')->value;

    // Check the CSS of the full display mode.
    $this->drupalGet($this->nodeUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($text);
    $this->assertSession()->responseContains($title);

    $relative_css = 'url(../../../../../../core/misc/icons/e29700/warning.svg)';
    $absolute_css = 'url(' . base_path() . 'core/themes/stable/images/core/icons/e29700/warning.svg)';

    // Check the CSS of the AMP display mode.
    $this->drupalGet($this->nodeAmpUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($text);
    $this->assertSession()->responseContains($title);
    $this->assertSession()->responseNotContains($relative_css);
    $this->assertSession()->responseContains($absolute_css);

    $relative_css = 'url(../../../images/core/throbber-active.gif)';
    $absolute_css = 'url(' . base_path() . 'core/themes/stable/images/core/throbber-active.gif)';

    // Check the CSS of the AMP display mode.
    $this->drupalGet($this->nodeAmpUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($text);
    $this->assertSession()->responseContains($title);
    $this->assertSession()->responseNotContains($relative_css);
    $this->assertSession()->responseContains($absolute_css);

  }

}
