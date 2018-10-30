<?php

namespace Drupal\block_style_plugins\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Block style plugins.
 */
interface BlockStyleInterface extends PluginInspectionInterface {

  /**
   * Returns the configuration form elements specific to a block configuration.
   *
   * This code will be run as part of a form alter so that the current blocks
   * configuration will be available to this method.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   */
  public function prepareForm(array $form, FormStateInterface $form_state);

  /**
   * Returns an array of field elements.
   *
   * These form fields will be injected into the block configuration form.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   A list of all form field elements that will allow setting styles.
   */
  public function formElements($form, FormStateInterface $form_state);

  /**
   * Returns a customized form array with new form settings for styles.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   */
  public function formAlter(array $form, FormStateInterface $form_state);

  /**
   * Adds block style specific submission handling for the block form.
   *
   * @param array $form
   *   The form definition array for the full block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm($form, FormStateInterface $form_state);

  /**
   * Builds and returns the renderable array for this block style plugin.
   *
   * @param array $variables
   *   List of all variables sent to the theme system.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  public function build(array $variables);

  /**
   * Exclude styles from appearing on a block.
   *
   * Determine if configuration should be excluded from certain blocks when a
   * block plugin id or block content type is passed from a plugin.
   *
   * @return bool
   *   Return True if the current block should not get the styles.
   */
  public function exclude();

  /**
   * Only show styles on specific blocks.
   *
   * Determine if configuration should be only included on certain blocks when a
   * block plugin id or block content type is passed from a plugin.
   *
   * @return bool
   *   Return True if the current block should only get the styles.
   */
  public function includeOnly();

  /**
   * Add theme suggestions for the block.
   *
   * @param array $suggestions
   *   List of theme suggestions.
   * @param array $variables
   *   List of variables from a preprocess hook.
   *
   * @return array
   *   List of all theme suggestions.
   */
  public function themeSuggestion(array $suggestions, array $variables);

  /**
   * Create a list of style configuration defaults.
   *
   * @return array
   *   Return a list of all the default styles.
   */
  public function defaultStyles();

  /**
   * Sets the style configuration for this plugin instance.
   *
   * @param array $styles
   *   A list of styles that need to be applied.
   */
  public function setStyles(array $styles);

  /**
   * Retrieve a list of style configuration.
   *
   * @return array
   *   Return a list of all styles.
   */
  public function getStyles();

}
