<?php

namespace Drupal\Tests\image\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests Image update path.
 *
 * @group image
 * @group legacy
 */
class ImageUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests image_post_update_image_style_dependencies().
   *
   * @see image_post_update_image_style_dependencies()
   */
  public function testPostUpdateImageStylesDependencies() {
    $view = 'core.entity_view_display.node.article.default';
    $form = 'core.entity_form_display.node.article.default';

    // Check that view display 'node.article.default' doesn't depend on image
    // style 'image.style.large'.
    $dependencies = $this->config($view)->get('dependencies.config');
    $this->assertFalse(in_array('image.style.large', $dependencies));
    // Check that form display 'node.article.default' doesn't depend on image
    // style 'image.style.thumbnail'.
    $dependencies = $this->config($form)->get('dependencies.config');
    $this->assertFalse(in_array('image.style.thumbnail', $dependencies));

    // Run updates.
    $this->runUpdates();

    // Check that view display 'node.article.default' depend on image style
    // 'image.style.large'.
    $dependencies = $this->config($view)->get('dependencies.config');
    $this->assertTrue(in_array('image.style.large', $dependencies));
    // Check that form display 'node.article.default' depend on image style
    // 'image.style.thumbnail'.
    $dependencies = $this->config($view)->get('dependencies.config');
    $this->assertTrue(in_array('image.style.large', $dependencies));
  }

  /**
   * Tests image_post_update_enable_filter_image_style().
   *
   * @see image_post_update_enable_filter_image_style()
   */
  public function testPostUpdateFilterImageStyle() {
    $config_trail = 'filters.filter_html.settings.allowed_html';
    // Add a disabled filter_html filter to full_html text format.
    include __DIR__ . '/../../../fixtures/update/test_enable_filter_image_style.php';

    // A format with an enabled filter_html filter.
    $basic_html = $this->config('filter.format.basic_html');
    // A format with a disabled filter_html filter.
    $full_html = $this->config('filter.format.full_html');
    // A format without a filter_html filter.
    $plain_text = $this->config('filter.format.plain_text');

    // Check that 'basic_html' text format has an enabled 'filter_html' filter,
    // whose 'allowed_html' setting contains an <img ...> tag that is missing
    // the 'data-image-style' attribute.
    $this->assertTrue($basic_html->get('filters.filter_html.status'));
    $this->assertNotContains('data-image-style', $basic_html->get($config_trail));

    // Check that 'full_html' text format has an disabled 'filter_html' filter,
    // whose 'allowed_html' setting contains an <img ...> tag that is missing
    // the 'data-image-style' attribute.
    $this->assertFalse($full_html->get('filters.filter_html.status'));
    $this->assertNotContains('data-image-style', $full_html->get($config_trail));

    // Check that 'plain_text' text format is missing an 'filter_html' filter.
    $this->assertNull($plain_text->get('filters.filter_html'));

    // Run updates.
    $this->runUpdates();

    $basic_html = $this->config('filter.format.basic_html');
    $full_html = $this->config('filter.format.full_html');
    $plain_text = $this->config('filter.format.plain_text');

    // Check that 'basic_html' text format 'filter_html' filter was updated.
    $this->assertContains('data-image-style', $basic_html->get($config_trail));

    // Check that 'full_html' text format 'filter_html' filter was not updated.
    $this->assertNotContains('data-image-style', $full_html->get($config_trail));

    // Check that 'plain_text' text format is missing the 'filter_html' filter.
    $this->assertNull($plain_text->get('filters.filter_html'));
  }

}
