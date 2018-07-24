<?php

namespace Drupal\acquia_contenthub_diagnostic\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Content Hub Requirement annotation object.
 *
 * @Annotation
 */
class ContentHubRequirement extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable title of the requirement.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}
