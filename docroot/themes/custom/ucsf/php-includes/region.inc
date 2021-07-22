<?php
use Drupal\node\NodeInterface;

/**
 * Implements template_preprocess_region().
 */
function ucsf_preprocess_region(&$variables) {

  if ($variables['region'] == 'header') {
    // Get the slug for current page.
    $current_path = \Drupal::service('path.current')->getPath();
    $alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
    // Make sure the menu shows up on all slugs starting with /cphp.
    if (strpos($alias, '/cphp') === 0 || strpos($alias, '/topic/parnassus') === 0) {
      // Create a variable to be used in @theme/templates/region/region--header.html.twig.
      $variables['has_subnav'] = TRUE;
    }

    // If there is a checkbox for minimized sticky nav, check it's value.
    /** @var \Drupal\node\Entity\Node $node */
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      if (!$node instanceof NodeInterface) {
        /** @var \Drupal\node\Entity\Node $node */
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($node);
      }
      if ($node->hasField('field_minimize_navigation_header')) {
        $field = $node->field_minimize_navigation_header->value;

        if ($field && $field == 1) {
          // Create a variable to be used in @theme/templates/region/region--header.html.twig.
          $variables['is_minimized_sticky_menu'] = TRUE;
        }
      }
    }
  }
}
