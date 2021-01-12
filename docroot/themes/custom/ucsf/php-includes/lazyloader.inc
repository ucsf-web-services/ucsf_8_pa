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

  // Checks for an attribute that indicates that lazyloading needs to be disabled.
  if (array_key_exists('data-no-lazyload', $variables['attributes'])) {
    // Checks if element has 'lazyload' class
    $key = FALSE;
    if (!empty($variables['attributes']['class'])) {
      $key = array_search('lazyload',  $variables['attributes']['class']);
    }

    if ($key !== FALSE) {
      // Removes 'lazyload' class
      unset($variables['attributes']['class'][$key]);

      if ($variables['attributes']['data-src']) {
        // Saves data-src value to src attribute, removes data-src.
        $variables['attributes']['src'] = $variables['attributes']['data-src'];
        unset($variables['attributes']['data-src']);
      }
    }
  };

  // Adds a class responsible for the same animation as LazyLoaded elements.
  $variables['attributes']['class'][] = 'element-fade';
  // Adds a library responsible for running animation when element is in view.
  $variables['#attached']['library'][] = 'ucsf/element_fade';
}
