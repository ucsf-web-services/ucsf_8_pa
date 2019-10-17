<?php

/**
 * @file
 * Post-update functions for Image.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Saves the image style dependencies into form and view display entities.
 */
function image_post_update_image_style_dependencies() {
  // Merge view and form displays. Use array_values() to avoid key collisions.
  $displays = array_merge(array_values(EntityViewDisplay::loadMultiple()), array_values(EntityFormDisplay::loadMultiple()));
  /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface[] $displays */
  foreach ($displays as $display) {
    // Re-save each config entity to add missed dependencies.
    $display->save();
  }
}

/**
 * Add 'anchor' setting to 'Scale and crop' effects.
 */
function image_post_update_scale_and_crop_effect_add_anchor(&$sandbox = NULL) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'image_style', function ($image_style) {
    /** @var \Drupal\image\ImageStyleInterface $image_style */
    $effects = $image_style->getEffects();
    foreach ($effects as $effect) {
      if ($effect->getPluginId() === 'image_scale_and_crop') {
        return TRUE;
      }
    }
    return FALSE;
  });
}

/**
 * Fix problem with image dimensions when using multiple upload.
 */
function image_post_update_multiple_upload_fix_with_dimensions() {
  \Drupal::messenger()->addMessage(t('Fixed problem with incorrect processing of image dimensions when using multiple upload. To eliminate this problem for already existing records see <a href="https://www.drupal.org/project/drupal/issues/2967586">https://www.drupal.org/project/drupal/issues/2967586</a>'), 'status');
}

/**
 * Update filter formats to allow the use of the image style filter.
 */
function image_post_update_enable_filter_image_style() {
  /** @var \Drupal\filter\FilterFormatInterface[] $formats */
  $formats = FilterFormat::loadMultiple();
  foreach ($formats as $format) {
    $filter = $format->filters('filter_html');
    if ($filter->status) {
      $config = $filter->getConfiguration();
      $allowed_html = !empty($config['settings']['allowed_html']) ? $config['settings']['allowed_html'] : NULL;
      $matches = [];
      if ($allowed_html && preg_match('/<img([^>]*)>/', $allowed_html, $matches)) {
        // Enable the image style filter, and set the weight to the highest
        // current weight + 1 so that it appears last in the list.
        $highest_weight = array_reduce($format->filters()
          ->getAll(), function ($carry, FilterInterface $filter) {
          return $filter->status !== FALSE && $filter->weight > $carry ? $filter->weight : $carry;
        }, 0);
        $format->setFilterConfig('filter_image_style', [
          'status' => TRUE,
          'weight' => $highest_weight + 1,
        ]);

        // Update the allowed HTML tags of filter_html filter.
        $attributes = array_filter(explode(' ', $matches[1]));
        $attributes[] = 'data-image-style';
        $config['settings']['allowed_html'] = preg_replace('/<img([^>]*)>/', '<img ' . implode(' ', array_unique($attributes)) . '>', $allowed_html);
        $format->setFilterConfig('filter_html', $config);
        $format->save();
      }
    }
  }
}
