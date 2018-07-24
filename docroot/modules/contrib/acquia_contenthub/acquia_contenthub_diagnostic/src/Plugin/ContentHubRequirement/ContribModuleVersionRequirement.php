<?php

namespace Drupal\acquia_contenthub_diagnostic\Plugin\ContentHubRequirement;

use Drupal\acquia_contenthub_diagnostic\ContentHubRequirementBase;

/**
 * Requirement to verify no untested module versions are installed.
 *
 * @ContentHubRequirement(
 *   id = "contrib_module_versions",
 *   title = @Translation("Untested contributed module versions"),
 * )
 */
class ContribModuleVersionRequirement extends ContentHubRequirementBase {

  const MODULE_VERSIONS = [
    'workbench_moderation' => '8.x-1.2',
    'paragraphs' => '8.x-1.0',
    'entity_reference_revisions' => '8.x-1.0',
  ];

  /**
   * Verify no untested module versions are installed.
   */
  public function verify() {
    $issues_found = [];
    foreach (static::MODULE_VERSIONS as $module => $recommended_version) {
      if ($this->moduleHandler->moduleExists($module)) {
        $current_version = system_get_info('module', $module)['version'];
        if ($current_version !== $recommended_version) {
          $name = $this->moduleHandler->getName($module);
          $issues_found[] = $this->t('@name @current_version (Recommended @recommended_version)', [
            '@name' => $name,
            '@current_version' => $current_version,
            '@recommended_version' => $recommended_version,
          ]);
        }
      }
    }

    if (empty($issues_found)) {
      return REQUIREMENT_OK;
    }

    $this->setValue($this->t('Untested module versions active'));
    $this->setDescription($this->t('The following untested module versions were found: @module_list', [
      '@module_list' => implode(', ', $issues_found),
    ]));
    return REQUIREMENT_WARNING;
  }

}
