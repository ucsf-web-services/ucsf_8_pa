<?php

/**
 * @file
 * Post-update functions for Image.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\filter\FilterFormatInterface;

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
 * Update filter formats to allow the use of the image style filter.
 */
function image_post_update_enable_filter_image_style(array &$sandbox) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'filter_format', function (FilterFormatInterface $format) {
    /** @var \Drupal\filter\Plugin\FilterInterface $filter */
    if (!($filter = $format->filters('filter_html')) || !$filter->status) {
      return FALSE;
    }

    $config = $filter->getConfiguration();
    $allowed_html = !empty($config['settings']['allowed_html']) ? $config['settings']['allowed_html'] : NULL;
    $matches = [];
    if ($allowed_html && preg_match('/<img([^>]*)>/', $allowed_html, $matches)) {
      $attributes = array_filter(preg_split('/\s/', $matches[1]));
      $attributes[] = 'data-image-style';
      $config['settings']['allowed_html'] = preg_replace('/<img([^>]*)>/', '<img ' . implode(' ', array_unique($attributes)) . '>', $allowed_html);
      $format->setFilterConfig('filter_html', $config);
      return TRUE;
    }
    return FALSE;
  });
}
