<?php

/**
 * @file
 * Theme and preprocess functions for node template.
 */

/**
 * Implements hook_preprocess_node() for node.html.twig.
 */
function ucsf_preprocess_node(&$variables) {
  $node = $variables['node'];
  // If node is an article and it has been created before redesign.
  $date_formatter = \Drupal::service('date.formatter');
  $node_type = $node->getType();
  $date = $date_formatter->format($node->get('created')->value, 'custom', 'Y');

  // Load 'legacy_node_components.scss' if node is created before 2019.
  if ($node_type === 'article' && intval($date) < intval('2019')) {
    $variables['#attached']['library'][] = 'ucsf/legacy_node_components';
  }
}
