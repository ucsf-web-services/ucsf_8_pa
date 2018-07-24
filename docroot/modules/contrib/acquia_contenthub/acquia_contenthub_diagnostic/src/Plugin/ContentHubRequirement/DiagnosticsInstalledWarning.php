<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Requirement to warn that this should be off in production.
 *
 * @ContentHubRequirement(
 *   id = "diagnostics_installed",
 *   title = @Translation("Diagnostic module installed"),
 * )
 */
class DiagnosticsInstalledWarning extends ContentHubRequirementBase {

  /**
   * Verify no untested module versions are installed.
   */
  public function verify() {
    $this->setValue($this->t('Diagnostics module is installed.'));
    $this->setDescription($this->t('Diagnostics are run on every Drupal bootstrap. It is recommended that this module be turned off on production sites for better site performance.'));
    return REQUIREMENT_WARNING;
  }

}
