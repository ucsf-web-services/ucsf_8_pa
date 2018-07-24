<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Defines a requirement to check for invalid certificates.
 *
 * @ContentHubRequirement(
 *   id = "invalid_certificates",
 *   title = @Translation("Invalid certificates"),
 * )
 */
class InvalidCertificatesRequirement extends ContentHubRequirementBase {

  /**
   * Verify SSL certificates.
   *
   * Request to the HTTPS version of the site. If it fails at all or the
   * response status is not 200 then the requirement fails.
   */
  public function verify() {
    $domain = $this->getDomain();

    try {
      $client = \Drupal::httpClient();
      $request = $client->get($domain);
      if ($request->getStatusCode() != 200) {
        throw new \Exception($this->t('Status 200 not returned.'));
      }
    }
    catch (\Exception $e) {
      $this->setValue($this->t('Certificate cannot be validated'));
      $this->setDescription($this->t("Your site's homepage could not be reached over HTTPS. Verify your server configuration to ensure the site is reachable via HTTPS and uses valid SSL certificates. Error: @error", [
        '@error' => $e->getMessage(),
      ]));
      return REQUIREMENT_ERROR;
    }

    return REQUIREMENT_OK;

  }

}
