
<?php

/**
 * Implements template_preprocess_region().
 */
function ucsf_preprocess_region(&$variables) {

  if ($variables['region'] == 'header') {
    // Get the slug for current page
    $current_path = \Drupal::service('path.current')->getPath();
    $alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);

    // Create a variable to be used in @theme/templates/region/region--header.html.twig
    if ($alias == '/cphp') {
      $variables['has_subnav'] = TRUE;
    }
  }
}
