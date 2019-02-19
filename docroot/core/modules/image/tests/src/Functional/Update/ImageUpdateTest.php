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
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8-rc1.bare.standard.php.gz',
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
    $this->assertNotContains('image.style.large', $dependencies);
    // Check that form display 'node.article.default' doesn't depend on image
    // style 'image.style.thumbnail'.
    $dependencies = $this->config($form)->get('dependencies.config');
    $this->assertNotContains('image.style.thumbnail', $dependencies);

    // Run updates.
    $this->runUpdates();

    // Check that view display 'node.article.default' depend on image style
    // 'image.style.large'.
    $dependencies = $this->config($view)->get('dependencies.config');
    $this->assertContains('image.style.large', $dependencies);
    // Check that form display 'node.article.default' depend on image style
    // 'image.style.thumbnail'.
    $dependencies = $this->config($view)->get('dependencies.config');
    $this->assertContains('image.style.large', $dependencies);
  }

  /**
   * Tests image_post_update_enable_filter_image_style().
   *
   * @see image_post_update_enable_filter_image_style()
   */
  public function testPostUpdateFilterImageStyle() {
    // Check that the 'basic_html' and 'full_html' formats do not have the image
    // style filter before starting the update.
    $config_factory = \Drupal::configFactory();
    $basic_html_data = $config_factory->get('filter.format.basic_html');
    $this->assertNull($basic_html_data->get('filters.filter_image_style'));
    $full_html_data = $config_factory->get('filter.format.full_html');
    $this->assertNull($full_html_data->get('filters.filter_image_style'));

    // Run updates.
    $this->runUpdates();

    // Check that the filter_format entities have been updated.
    $basic_html_data = $config_factory->get('filter.format.basic_html');
    $this->assertNotNull($basic_html_data->get('filters.filter_image_style'));
    // Full HTML doesn't have filter_html configured, so it isn't updated.
    $full_html_data = $config_factory->get('filter.format.full_html');
    $this->assertNull($full_html_data->get('filters.filter_image_style'));
  }

}
