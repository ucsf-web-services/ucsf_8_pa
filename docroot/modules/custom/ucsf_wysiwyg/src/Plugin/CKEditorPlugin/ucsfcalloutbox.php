<?php

/**
 * @file
 * Contains \Drupal\ckeditor_test\Plugin\CKEditorPlugin\ucsfquote.
 */

namespace Drupal\ucsf_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "ucsfcalloutbox" plugin.

 *
 * @CKEditorPlugin(
 *   id = "ucsfcalloutbox",
 *   label = @Translation("ucsfcalloutbox"),
 *   module = "ucsf_wysiwyg"
 * )
 */
class ucsfcalloutbox extends CKEditorPluginBase {

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
    return \Drupal::service('extension.list.module')->getPath('ucsf_wysiwyg') . '/js/plugins/ucsfcalloutbox/plugin.js';
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
      'Ucsfcalloutbox' => [
        'label' => t('Callout Box'),
        'image' => \Drupal::service('extension.list.module')->getPath('ucsf_wysiwyg') . '/js/plugins/ucsfcalloutbox/icons/ucsfcalloutbox.png',
      ]
    ];
  }

}
