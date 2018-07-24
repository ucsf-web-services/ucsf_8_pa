<?php

namespace Drupal\Tests\acquia_contenthub\Functional;

/**
 * Create a block and test block markup by attempting to view the altered block.
 *
 * @group acquia_contenthub_no_test
 */
class BlockContentTypeTemplatesTest extends BlockContentTypeTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'block_content',
    'acquia_contenthub',
  ];

  /**
   * Checks the acquia-contenthub block templates functionality.
   */
  public function testBlockContentView() {
    \Drupal::service('theme_handler')->install([
      'test_basetheme',
      'ch_test_subtheme',
      'ch_test_subtheme_advanced',
    ]);

    // Make ch_test_subtheme theme default.
    $this->config('system.theme')
      ->set('default', 'ch_test_subtheme')
      ->save();
    $this->assertEqual($this->config('system.theme')->get('default'), 'ch_test_subtheme');

    $this->drupalLogin($this->adminUser);
    $block = $this->createBlockContent();
    $block->set('body', 'block_body_value');
    $block->save();

    // Attempt to view the block.
    $this->drupalGet('acquia-contenthub/display/block_content/' . $block->id() . '/default');

    $this->removeWhiteSpace();

    $expected = '</head><body><div><p>block_body_value</p></div><div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'The block.html.twig and the block--block-content.html.twig are not used.');

    // Make ch_test_subtheme_advanced theme default.
    $this->config('system.theme')
      ->set('default', 'ch_test_subtheme_advanced')
      ->save();
    $this->assertEqual($this->config('system.theme')->get('default'), 'ch_test_subtheme_advanced');

    $this->drupalGet('acquia-contenthub/display/block_content/' . $block->id() . '/default');

    $this->removeWhiteSpace();

    $expected = '</head><body><div>block--block-content--acquia-contenthub.html.twig<h2>' . $block->label() . '</h2><div><p>block_body_value</p></div></div><div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'The block--block-content--acquia-contenthub.html.twig is used.');
  }

}
