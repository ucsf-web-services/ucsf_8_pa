<?php

/**
 * Implements template_preprocess_region().
 */
function ucsf_preprocess_region(&$variables) {

  if ($variables['region'] == 'header') {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $type = $node->getType();

      if ($type == 'article') {
        $variables['progress_bar'] = TRUE;
      }
    }
  }

}
