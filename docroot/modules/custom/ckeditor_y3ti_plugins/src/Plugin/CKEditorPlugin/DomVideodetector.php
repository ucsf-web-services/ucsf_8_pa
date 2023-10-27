<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin\DomVideodetector.
 */

namespace Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "DomVideodetector" plugin.

 *
 * @CKEditorPlugin(
 *   id = "domvideodetector",
 *   label = @Translation("DomVideodetector")
 * )
 */
class DomVideodetector extends CKEditorPluginBase implements CKEditorPluginInterface {

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
    return \Drupal::service('extension.list.module')->getPath('ckeditor_y3ti_plugins')  . '/js/plugins/domvideodetector/plugin.js';
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
      'Domvideodetector' => [
        'label' => t('Domvideodetector'),
        'image' => \Drupal::service('extension.list.module')->getPath('ckeditor_y3ti_plugins')  . '/js/plugins/domvideodetector/icons/domvideodetector.png',
      ]
    ];
  }
}
