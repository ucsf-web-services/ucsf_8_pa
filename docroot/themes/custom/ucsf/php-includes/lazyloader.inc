<?php

/**
 * Implements template_preprocess_image().
 */
function ucsf_preprocess_image(&$variables) {
  // If alt is missing from array or it is null,
  // create an alt key with empty string as a value.
  if (empty($variables['attributes']['alt'])) {
    $variables['attributes']['alt'] = '';
  };

  $variables['attributes']['loading'] = 'lazy';
  // Adds a class responsible for the same animation as LazyLoaded elements.
  $variables['attributes']['class'][] = 'element-fade';
  // Adds a library responsible for running animation when element is in view.
  $variables['#attached']['library'][] = 'ucsf/element_fade';
}
