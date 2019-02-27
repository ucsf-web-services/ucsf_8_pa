<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin\DomThreecolumn.
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
 *   id = "domthreecolumn",
 *   label = @Translation("DomThreecolumn")
 * )
 */
class DomThreeColumn extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface {

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
    return drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domthreecolumn/plugin.js';
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    $config = [];
    $settings = $editor->getSettings();
    if (!isset($settings['plugins']['domthreecolumn']['domthreecolumncolors'])) {
      return $config;
    }
    $colors = $settings['plugins']['domthreecolumn']['domthreecolumncolors'];
    $config['colorsSetDomThreeColumn'] = $colors;
    return $config;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'Domthreecolumn' => [
        'label' => t('Domthreecolumn'),
        'image' => drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domthreecolumn/icons/domthreecolumn.png',
      ]
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    // Defaults.
    $config = ['domthreecolumncolors' => array('transparent' => 'transparent')];
    $settings = $editor->getSettings();
    if (isset($settings['plugins']['domthreecolumn'])) {
      $config = $settings['plugins']['domthreecolumn'];
    }

    $form['domthreecolumncolors'] = [
      '#title' => $this->t('Color Pick'),
      '#type' => 'checkboxes',
      '#default_value' => $config['domthreecolumncolors'],
      '#options' => array(
        'transparent' => 'Transparent',
        'interactive-blue' => 'Interactive Blue',
        'interactive-dark-blue' => 'Interactive Navy',
        'interactive-light-navy' => 'Interactive Light Navy',
        'interactive-dark-grey' => 'Interactive Dark Grey',
        'interactive-teal' => 'Interactive Teal',
        'interactive-purple' => 'Interactive Purple',
        'interactive-red' => 'Interactive Red',
        'interactive-yellow' => 'Interactive Yellow',
        'interactive-orange' => 'Interactive Orange',
        'interactive-green' => 'Interactive Green',
      ),
      //'#required' => TRUE
    ];

    return $form;
  }

}
