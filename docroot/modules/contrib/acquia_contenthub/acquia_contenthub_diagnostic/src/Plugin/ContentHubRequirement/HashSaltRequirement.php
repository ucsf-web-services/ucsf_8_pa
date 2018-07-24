<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;
use Drupal\Core\Site\Settings;

/**
 * Defines a salt hash requirement.
 *
 * @ContentHubRequirement(
 *   id = "hash_salt",
 *   title = @Translation("Hash salt"),
 * )
 */
class HashSaltRequirement extends ContentHubRequirementBase {

  /**
   * {@inheritdoc}
   */
  public function verify() {
    if (strlen(Settings::get('hash_salt'))) {
      return REQUIREMENT_OK;
    }

    $this->setValue($this->t('Not set'));
    $this->setDescription($this->t('The <code>hash_salt</code> setting is not configured in settings.php.'));
    return REQUIREMENT_ERROR;
  }

}
