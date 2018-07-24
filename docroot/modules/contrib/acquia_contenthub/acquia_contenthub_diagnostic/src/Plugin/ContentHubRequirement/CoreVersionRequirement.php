<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Defines a core version requirement.
 *
 * @ContentHubRequirement(
 *   id = "core_version",
 *   title = @Translation("Drupal core version"),
 * )
 */
class CoreVersionRequirement extends ContentHubRequirementBase {

  const MINIMUM_SUPPORTED_VERSION = '8.2';

  /**
   * {@inheritdoc}
   */
  public function verify() {
    if (version_compare(\Drupal::VERSION, self::MINIMUM_SUPPORTED_VERSION, '>=')) {
      return REQUIREMENT_OK;
    }

    $this->setValue($this->t('Unsupported version (@version)', [
      '@version' => \Drupal::VERSION,
    ]));
    $this->setDescription($this->t('Drupal @version or greater is required.', [
      '@version' => self::MINIMUM_SUPPORTED_VERSION,
    ]));
    return REQUIREMENT_ERROR;
  }

}
