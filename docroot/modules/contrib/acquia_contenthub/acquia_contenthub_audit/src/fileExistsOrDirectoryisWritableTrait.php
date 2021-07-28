<?php

namespace Drupal\acquia_contenthub_audit;

/**
 * Trait for consistency in file checking.
 *
 * @package Drupal\acquia_contenthub_audit
 */
trait fileExistsOrDirectoryisWritableTrait {

  /**
   * Checks whether directory/file is writable.
   */
  protected function fileExistsOrDirectoryIsWritable(string $file_path) {
    if (!file_exists($file_path) && !is_writable(dirname($file_path))) {
      throw new \Exception(sprintf("The %s directory is not writable.", dirname($file_path)));
    }
    if (file_exists($file_path) && !is_writable($file_path)) {
      throw new \Exception(sprintf("The %s file is not writable.", $file_path));
    }
    return TRUE;
  }

}
