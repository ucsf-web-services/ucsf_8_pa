<?php

namespace Drupal\acquia_contenthub\Plugin\DisplayVariant;

use Drupal\Core\Render\Plugin\DisplayVariant\SimplePageVariant;

/**
 * Provides a page display variant that simply renders the main content.
 *
 * @PageDisplayVariant(
 *   id = "contenthub_page",
 *   admin_label = @Translation("Content Hub page")
 * )
 */
class ContentHubPageVariant extends SimplePageVariant {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      'content' => [
        'main_content' => ['#weight' => -800] + $this->mainContent,
      ],
    ];
    return $build;
  }

}
