<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin\DomNumbercallout.
 */

namespace Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "DomThreeColumn" plugin.

 *
 * @CKEditorPlugin(
 *   id = "domnumbercallout",
 *   label = @Translation("DomNumbercallout")
 * )
 */
class DomNumbercallout extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface {

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
    return drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domnumbercallout/plugin.js';
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    $config = [];
    $settings = $editor->getSettings();
    if (!isset($settings['plugins']['domnumbercallout']['domnumbercalloutcolors'])) {
      return $config;
    }
    $colors = $settings['plugins']['domnumbercallout']['domnumbercalloutcolors'];
    $config['colorsSetDomNumberCallout'] = $colors;
    return $config;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'Domnumbercallout' => [
        'label' => t('Domnumbercallout'),
        'image' => drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domnumbercallout/icons/domnumbercallout.png',
      ]
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    // Defaults.
    $config = ['domnumbercalloutcolors' => array('transparent' => 'transparent')];
    $settings = $editor->getSettings();
    if (isset($settings['plugins']['domnumbercallout'])) {
      $config = $settings['plugins']['domnumbercallout'];
    }

    $form['domnumbercalloutcolors'] = [
      '#title' => $this->t('Color Pick'),
      '#type' => 'checkboxes',
      '#default_value' => $config['domnumbercalloutcolors'],
      '#options' => array(
        'default' => 'Default',
        'navy' => 'Navy',
        'turquise' => 'Turquise',
        'green' => 'Green',
        'blue' => 'Blue',
        'orange' => 'Orange',
        'purple' => 'Purple',
        'red' => 'Red',
        'yellow' => 'Yellow',
        'light-gray' => 'Light Gray',
        'gray' => 'Gray',
        'dark-gray' => 'Dark Gray'
      ),
      //'#required' => TRUE
    ];

    return $form;
  }

}
