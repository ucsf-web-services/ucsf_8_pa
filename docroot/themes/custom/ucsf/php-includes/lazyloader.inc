<?php

/**
 * Implements template_preprocess_image().
 */
function ucsf_preprocess_image(&$variables) {
  // If alt is missing from array,
  // create an alt key with empty string as a value.
  if (!array_key_exists('alt', $variables['attributes'])) {
    $variables['attributes']['alt'] = '';
  }
}
