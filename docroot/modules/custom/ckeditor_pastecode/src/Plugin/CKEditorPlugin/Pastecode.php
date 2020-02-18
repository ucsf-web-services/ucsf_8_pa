<?php

namespace Drupal\ckeditor_pastecode\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Pastecode" plugin.
 *
 * @CKEditorPlugin(
 *   id = "pastecode",
 *   label = @Translation("Pastecode"),
 *   module = "ckeditor_pastecode"
 * )
 */
class Pastecode extends CKEditorPluginBase {
  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return base_path() . 'libraries/pastecode/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'pastecode' => [
        'label' => t('Pastecode'),
        'image' => base_path() . 'libraries/pastecode/icons/pastecode.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }
}