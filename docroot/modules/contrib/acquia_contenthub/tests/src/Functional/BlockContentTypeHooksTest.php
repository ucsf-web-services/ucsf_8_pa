<?php

namespace Drupal\Tests\acquia_contenthub\Functional;

/**
 * Create a block and test block markup by attempting to view the altered block.
 *
 * @group acquia_contenthub_no_test
 */
class BlockContentTypeHooksTest extends BlockContentTypeTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'block_content',
    'acquia_contenthub',
    'ch_block_content_test',
  ];

  /**
   * Checks the acquia-contenthub block hooks functionality.
   */
  public function testBlockContentView() {
    $this->drupalLogin($this->adminUser);
    $block = $this->createBlockContent();
    $block->set('body', 'block_body_value');
    $block->save();

    // Attempt to view the block.
    $this->drupalGet('acquia-contenthub/display/block_content/' . $block->id() . '/default');

    $this->assertResponse(200, "Assert response was '200' and not '403 Access denied'");

    $this->assertRaw($block->get('body')->getString(), 'Body field is present.');

    $this->assertNoRaw($block->label(), 'Label field is absent.');

    $this->removeWhiteSpace();

    $expected = <<<HTML
</head><body><div class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-hidden field__item"><p>block_body_value</p></div>
      [hook_block_content_view][hook_block_content_view_alter]



    <div data-content-barrier-exclude="true"></div></body></html>
HTML;
    $this->assertRaw($expected, 'Altered block have no extra markup.');
  }

}
