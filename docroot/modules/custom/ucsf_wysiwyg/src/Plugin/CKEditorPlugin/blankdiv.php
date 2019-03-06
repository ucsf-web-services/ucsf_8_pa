<?php

/**
 * @file
 * Contains \Drupal\ucsf_wysiwyg\Plugin\CKEditorPlugin\ucsfquote.
 */

namespace Drupal\ucsf_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "blankdiv" plugin.

 *
 * @CKEditorPlugin(
 *   id = "blankdiv",
 *   label = @Translation("blankdiv"),
 *   module = "ucsf_wysiwyg"
 * )
 */
class blankdiv extends CKEditorPluginBase {

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
    return drupal_get_path('module', 'ucsf_wysiwyg') . '/js/plugins/blankdiv/plugin.js';
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
      'blankdiv' => [
        'label' => t('Pull Quote'),
        'image' => drupal_get_path('module', 'ucsf_wysiwyg') . '/js/plugins/blankdiv/icons/blankdiv.png'
      ]
    ];
  }

}
