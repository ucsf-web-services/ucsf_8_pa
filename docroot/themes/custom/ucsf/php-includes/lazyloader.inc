<?php

function ucsf_preprocess_image(&$variables) {
  if (!array_key_exists('alt', $variables['attributes'])) {
    $variables['attributes']['alt'] = '';
  }
}
