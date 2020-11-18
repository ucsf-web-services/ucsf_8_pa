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

  if (empty($variables['attributes']['loading'])) {
    $variables['attributes']['loading'] = 'lazy';
  };

  if ($variables['attributes']['src']) {
    // Saves data-src value to src attribute, removes data-src.
    $variables['attributes']['data-src'] = $variables['attributes']['src'];
    unset($variables['attributes']['src']);
  }

  // Checks for an attribute that indicates that lazyloading needs to be eager.
  if (array_key_exists('data-no-lazyload', $variables['attributes'])) {
    // Checks if element has 'loading' attribute
    $key = array_search('loading',  $variables['attributes']);
    unset($variables['attributes']['class']['loading']);

    if ($key !== FALSE) {
      // prompt quicker loading
      $variables['attributes']['loading'] = 'auto';

      if ($variables['attributes']['data-src']) {
        // Saves data-src value to src attribute, removes data-src.
        $variables['attributes']['src'] = $variables['attributes']['data-src'];
        unset($variables['attributes']['data-src']);
      }
    }
  };

}
