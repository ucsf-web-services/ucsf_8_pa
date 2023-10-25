<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin\DomTwocolumn.
 */

namespace Drupal\ckeditor_y3ti_plugins\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "DomTwocolumn" plugin.

 *
 * @CKEditorPlugin(
 *   id = "domtwocolumn",
 *   label = @Translation("DomTwocolumn")
 * )
 */
class DomTwocolumn extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface {

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
    return \Drupal::service('extension.list.module')->getPath('ckeditor_y3ti_plugins')  . '/js/plugins/domtwocolumn/plugin.js';
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    $config = [];
    $settings = $editor->getSettings();
    if (!isset($settings['plugins']['domtwocolumn']['domtwocolumncolors'])) {
      return $config;
    }
    $colors = $settings['plugins']['domtwocolumn']['domtwocolumncolors'];
    $config['colorsSetDomTwoColumn'] = $colors;
    return $config;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return [
      'Domtwocolumn' => [
        'label' => t('Domtwocolumn'),
        'image' => \Drupal::service('extension.list.module')->getPath('ckeditor_y3ti_plugins')  . '/js/plugins/domtwocolumn/icons/domtwocolumn.png',
      ]
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    // Defaults.
    $config = ['domtwocolumncolors' => array('transparent' => 'transparent')];
    $settings = $editor->getSettings();
    if (isset($settings['plugins']['domtwocolumn'])) {
      $config = $settings['plugins']['domtwocolumn'];
    }

    $form['domtwocolumncolors'] = [
      '#title' => $this->t('Color Pick'),
      '#type' => 'checkboxes',
      '#default_value' => $config['domtwocolumncolors'],
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
      )
    ];

    return $form;
  }

}
