<?php

namespace Drupal\Tests\acquia_contenthub\Functional;

use Drupal\block_content\Tests\BlockContentTestBase;

/**
 * Create a block and test block markup by attempting to view the block.
 *
 * @group acquia_contenthub_no_test
 */
class BlockContentTypeTest extends BlockContentTestBase {

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
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'administer blocks',
    'administer acquia content hub',
  ];

  /**
   * Checks the acquia-contenthub block view functionality.
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

    $expected = '</head><body><div class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-hidden field__item"><p>block_body_value</p></div><div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'The block--block-content--acquia-contenthub.html.twig template have no extra markup.');
  }

}
