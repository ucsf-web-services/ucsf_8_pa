<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin\DomCta.
 */

namespace Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "DomCta" plugin.

 *
 * @CKEditorPlugin(
 *   id = "domcta",
 *   label = @Translation("DomCta")
 * )
 */
class DomCta extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface {

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
    return drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domcta/plugin.js';
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    $config = [];
    $settings = $editor->getSettings();
    if (!isset($settings['plugins']['domcta']['domctacolors'])) {
      return $config;
    }
    $colors = $settings['plugins']['domcta']['domctacolors'];
    $config['colorsSetDomCta'] = $colors;
    return $config;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'Domcta' => [
        'label' => t('Domcta'),
        'image' => drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/plugins/domcta/icons/domcta.png',
      ]
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    // Defaults.
    $config = ['domctacolors' => array('transparent' => 'transparent')];
    $settings = $editor->getSettings();
    if (isset($settings['plugins']['domcta'])) {
      $config = $settings['plugins']['domcta'];
    }

    $form['domctacolors'] = [
      '#title' => $this->t('Color Pick'),
      '#type' => 'checkboxes',
      '#default_value' => $config['domctacolors'],
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
