<?php

/**
 * @file
 * Contains \Drupal\ucsf_wysiwyg\Plugin\CKEditorPlugin\ucsfquote.
 */

namespace Drupal\ucsf_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "ucsfquote" plugin.

 *
 * @CKEditorPlugin(
 *   id = "ucsfquote",
 *   label = @Translation("ucsfquote"),
 *   module = "ucsf_wysiwyg"
 * )
 */
class ucsfquote extends CKEditorPluginBase {

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
    return \Drupal::service('extension.list.module')->getPath('ucsf_wysiwyg')  . '/js/plugins/ucsfquote/plugin.js';
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
      'Ucsfquote' => [
        'label' => t('Pull Quote'),
        'image' => \Drupal::service('extension.list.module')->getPath('ucsf_wysiwyg') . '/js/plugins/ucsfquote/icons/ucsfquote.png'
      ]
    ];
  }

}
