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

  // Checks for an attribute that indicates that lazyloading needs to be eager.
  if (array_key_exists('data-no-lazyload', $variables['attributes'])) {
    // Checks if element has 'loading' attribute
    $key = array_search('loading',  $variables['attributes']);

    if ($key !== FALSE) {
      // prompt quicker loading
      $variables['attributes']['loading'] = 'eager';
    }
  };

}
