<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Defines a module compatibility requirement.
 *
 * @ContentHubRequirement(
 *   id = "module_compatibility",
 *   title = @Translation("Module compatibility"),
 * )
 */
class ModuleCompatibilityRequirement extends ContentHubRequirementBase {

  const INCOMPATIBLE_MODULES = [
    'content_moderation',
  ];

  /**
   * {@inheritdoc}
   */
  public function verify() {
    $active_modules = array_keys($this->moduleHandler->getModuleList());
    $incompatibilities_found = array_intersect($active_modules, self::INCOMPATIBLE_MODULES);
    if (empty($incompatibilities_found)) {
      return REQUIREMENT_OK;
    }

    $this->setValue($this->t('Incompatibile modules active'));
    $this->setDescription($this->t('The following incompatible modules must be uninstalled: @module_list', [
      '@module_list' => implode(', ', $incompatibilities_found),
    ]));
    return REQUIREMENT_ERROR;
  }

}
