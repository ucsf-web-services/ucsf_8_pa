<?php

/**
 * @file
 * Contains \Drupal\ckeditor_test\Plugin\CKEditorPlugin\timestamp.
 */

namespace Drupal\ckeditor_test\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "timestamp" plugin.

 *
 * @CKEditorPlugin(
 *   id = "timestamp",
 *   label = @Translation("timestamp"),
 *   module = "ckeditor_test"
 * )
 */
class timestamp extends CKEditorPluginBase {

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getDependencies().
   */
  public function getDependencies(Editor $editor) {
    return array();
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getLibraries().
   */
  public function getLibraries(Editor $editor) {
    return array();
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::isInternal().
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
   */
  public function getFile() {
    return drupal_get_path('module', 'ckeditor_test') . '/js/plugins/timestamp/plugin.js';
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    return array();
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'timestamp' => [
        'label' => t('timestamp'),
        'image' => drupal_get_path('module', 'ckeditor_test') . '/js/plugins/timestamp/icons/timestamp.png',
      ]
    ];
  }
   /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    return $form;
  }


}
