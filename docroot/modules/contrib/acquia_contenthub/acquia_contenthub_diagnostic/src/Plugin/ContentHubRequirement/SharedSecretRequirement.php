<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Verify shared secret is stored locally and matches remote shared secret.
 *
 * @ContentHubRequirement(
 *   id = "shared_secret_verification",
 *   title = @Translation("Shared secret"),
 * )
 */
class SharedSecretRequirement extends ContentHubRequirementBase {

  /**
   * Verify shared secret is stored locally and matches remote shared secret.
   */
  public function verify() {
    // If Content Hub is not installed there is no reason to run this test.
    if (!$this->moduleHandler->moduleExists('acquia_contenthub')) {
      return REQUIREMENT_OK;
    }

    $shared_secret = \Drupal::state()->get('acquia_contenthub.shared_secret');
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');

    // If client manager is not connected we cannot verify the remote key.
    if (!$client_manager->isConnected()) {
      $this->setValue($this->t('Acquia Content Hub service is not connected.'));
      $this->setDescription($this->t('Unable to reach Acquia Content Hub service. Cannot verify shared secret.'));
      return REQUIREMENT_WARNING;
    }

    // Get settings via direct request as the getSettings function sets the
    // shared secret.
    $settings = $client_manager->createRequest('getSettings');
    // If we cannot get the settings warn that we cannot verify shared secret.
    if (!$settings) {
      $this->setValue($this->t('Unable to get Acquia Content Hub settings.'));
      $this->setDescription($this->t('Unable to get Acquia Content Hub settings. Cannot verify shared secret.'));
      return REQUIREMENT_WARNING;
    }

    // Warn if no key is stored locally.
    if (!$shared_secret) {
      $this->setValue($this->t('Shared secret not stored locally.'));
      $this->setDescription($this->t('The shared secret is not stored locally. An attempt has been made to correct this issue. Refresh the page and if this error persists contact Acquia Support.'));
      \Drupal::service('acquia_contenthub.acquia_contenthub_subscription')->getSettings();
      return REQUIREMENT_WARNING;
    }

    // If the local shared secret and remote shared secret do not match throw
    // an error and store the new secret.
    if ($shared_secret != $settings->getSharedSecret()) {
      $this->setValue($this->t('Local shared secret incorrect.'));
      $this->setDescription($this->t('The local shared secret did not match the remote shared secret. An attempt has been made to correct this issue. Refresh the page and if this error persists contact Acquia Support.'));
      \Drupal::service('acquia_contenthub.acquia_contenthub_subscription')->getSettings();
      return REQUIREMENT_ERROR;
    }

    return REQUIREMENT_OK;
  }

}
