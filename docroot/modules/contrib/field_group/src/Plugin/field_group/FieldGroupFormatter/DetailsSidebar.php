<?php

/**
 * /**
 * @file
 * Contains \Drupal\field_group\Plugin\field_group\FieldGroupFormatter\Details.
 */

namespace Drupal\field_group\Plugin\field_group\FieldGroupFormatter;

/**
 * Details Sidebar element.
 *
 * @FieldGroupFormatter(
 *   id = "details_sidebar",
 *   label = @Translation("Details Sidebar"),
 *   description = @Translation("Add a details sidebar element"),
 *   supported_contexts = {
 *     "form",
 *     "view"
 *   }
 * )
 */
class DetailsSidebar extends Details {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    $element += [
      '#group' => 'advanced',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    $defaults = parent::defaultContextSettings($context);
    return $defaults;
  }

}