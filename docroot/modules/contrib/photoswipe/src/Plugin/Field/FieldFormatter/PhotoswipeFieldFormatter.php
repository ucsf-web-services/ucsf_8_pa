<?php

namespace Drupal\photoswipe\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'photoswipe_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "photoswipe_field_formatter",
 *   label = @Translation("Photoswipe"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class PhotoswipeFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'photoswipe_node_style_first' => '',
      'photoswipe_node_style' => '',
      'photoswipe_image_style' => '',
      'photoswipe_caption' => '',
      'photoswipe_view_mode' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $image_styles_hide = $image_styles;
    $image_styles_hide['hide'] = $this->t('Hide (do not display image)');
    $element['photoswipe_node_style_first'] = [
      '#title' => $this->t('Node image style for first image'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('photoswipe_node_style_first'),
      '#empty_option' => $this->t('No special style.'),
      '#options' => $image_styles_hide,
      '#description' => $this->t('Image style to use in the content for the first image.'),
    ];
    $element['photoswipe_node_style'] = [
      '#title' => $this->t('Node image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('photoswipe_node_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles_hide,
      '#description' => $this->t('Image style to use in the node.'),
    ];
    $element['photoswipe_image_style'] = [
      '#title' => $this->t('Photoswipe image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('photoswipe_image_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles,
      '#description' => $this->t('Image style to use in the Photoswipe.'),
    ];

    // Set our caption options.
    $caption_options = [
      'title' => $this->t('Image Title Tag'),
      'alt' => $this->t('Image Alt Tag'),
      'node_title' => $this->t('Node Title'),
    ];
    // Add the other node fields as options.
    if (!empty($form['#fields'])) {
      foreach ($form['#fields'] as $node_field) {
        if ($node_field != $this->fieldDefinition->getName()) {
          $caption_options[$node_field] = $node_field;
        }
      }
    }

    $element['photoswipe_caption'] = [
      '#title' => $this->t('Photoswipe image caption'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('photoswipe_caption'),
      '#options' => $caption_options,
      '#description' => $this->t('Field that should be used for the caption.'),
    ];

    // Add the current view mode so we can control view mode for node fields.
    $element['photoswipe_view_mode'] = [
      '#type' => 'hidden',
      '#value' => $this->viewMode,
    ];

    return $element + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    if (isset($image_styles[$this->getSetting('photoswipe_node_style')])) {
      $summary[] = $this->t('Node image style: @style', ['@style' => $image_styles[$this->getSetting('photoswipe_node_style')]]);
    }
    elseif ($this->getSetting('photoswipe_node_style') == 'hide') {
      $summary[] = $this->t('Node image style: Hide');
    }
    else {
      $summary[] = $this->t('Node image style: Original image');
    }

    if (isset($image_styles[$this->getSetting('photoswipe_node_style_first')])) {
      $summary[] = $this->t('Node image style of first image: @style', ['@style' => $image_styles[$this->getSetting('photoswipe_node_style_first')]]);
    }
    elseif ($this->getSetting('photoswipe_node_style_first') == 'hide') {
      $summary[] = $this->t('Node image style of first image: Hide');
    }
    else {
      $summary[] = $this->t('Node image style of first image: Original image');
    }

    if (isset($image_styles[$this->getSetting('photoswipe_image_style')])) {
      $summary[] = $this->t('Photoswipe image style: @style', ['@style' => $image_styles[$this->getSetting('photoswipe_image_style')]]);
    }
    else {
      $summary[] = $this->t('photoswipe image style: Original image');
    }

    if ($this->getSetting('photoswipe_caption')) {
      $caption_options = [
        'alt' => $this->t('Image Alt Tag'),
        'title' => $this->t('Image Title Tag'),
        'node_title' => $this->t('Node Title'),
      ];
      if (array_key_exists($this->getSetting('photoswipe_caption'), $caption_options)) {
        $caption_setting = $caption_options[$this->getSetting('photoswipe_caption')];
      }
      else {
        $caption_setting = $this->getSetting('photoswipe_caption');
      }
      $summary[] = $this->t('Photoswipe Caption: @field', ['@field' => $caption_setting]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    if (!empty($items)) {
      \Drupal::service('photoswipe.assets_manager')->attach($elements);
    }

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'photoswipe_image_formatter',
        '#item' => $item,
        '#display_settings' => $settings,
        '#delta' => $delta,
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $style_ids = [];
    $style_ids[] = $this->getSetting('photoswipe_node_style');
    if (!empty($this->getSetting('photoswipe_node_style_first'))) {
      $style_ids[] = $this->getSetting('photoswipe_node_style_first');
    }
    $style_ids[] = $this->getSetting('photoswipe_image_style');
    /** @var \Drupal\image\ImageStyleInterface $style */
    foreach ($style_ids as $style_id) {
      if ($style_id && $style = ImageStyle::load($style_id)) {
        // If this formatter uses a valid image style to display the image, add
        // the image style configuration entity as dependency of this formatter.
        $dependencies[$style->getConfigDependencyKey()][] = $style->getConfigDependencyName();
      }
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);
    $style_ids = [];
    $style_ids['photoswipe_node_style'] = $this->getSetting('photoswipe_node_style');
    if (!empty($this->getSetting('photoswipe_node_style_first'))) {
      $style_ids['photoswipe_node_style_first'] = $this->getSetting('photoswipe_node_style_first');
    }
    $style_ids['photoswipe_image_style'] = $this->getSetting('photoswipe_image_style');
    /** @var \Drupal\image\ImageStyleInterface $style */
    foreach ($style_ids as $name => $style_id) {
      if ($style_id && $style = ImageStyle::load($style_id)) {
        if (!empty($dependencies[$style->getConfigDependencyKey()][$style->getConfigDependencyName()])) {
          $replacement_id = $this->imageStyleStorage->getReplacementId($style_id);
          // If a valid replacement has been provided in the storage, replace
          // the image style with the replacement and signal that the formatter
          // plugin settings were updated.
          if ($replacement_id && ImageStyle::load($replacement_id)) {
            $this->setSetting($name, $replacement_id);
            $changed = TRUE;
          }
        }
      }
    }
    return $changed;
  }

}
