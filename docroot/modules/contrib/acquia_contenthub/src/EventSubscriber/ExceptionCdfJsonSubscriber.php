<?php

namespace Drupal\acquia_contenthub\EventSubscriber;

use Drupal\Core\EventSubscriber\ExceptionJsonSubscriber;

/**
 * Handle Content Hub CDF JSON exceptions the same as JSON exceptions.
 */
class ExceptionCdfJsonSubscriber extends ExceptionJsonSubscriber {

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['acquia_contenthub_cdf'];
  }

}
