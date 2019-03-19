<?php

use Drupal\node\NodeInterface;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function ucsf_theme_suggestions_page_alter(array &$suggestions, array $variables) {

  $node = \Drupal::routeMatch()->getParameter('node');

  // Template suggestions based on node type.
  /** @var Drupal\node\Entity\Node $node */
  if ($node instanceof NodeInterface) {
    $node_type = $node->getType();
    $custom_layout_types = [
      'article',
      'author',
      'full-width-page',
      'homepage',
      'narrow-page',
      'news-center',
      'news-package',
      'trending',
    ];

    if(in_array($node_type, $custom_layout_types)) {
      $suggestions[] = 'page__fancy';
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function ucsf_theme_suggestions_form_alter(array &$suggestions, array $variables) {
  $suggestions[] = 'form__' . $variables['element']['#form_id'];
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function ucsf_theme_suggestions_taxonomy_term_alter(array &$suggestions, array $variables) {

  $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');

  $suggestions[] = 'taxonomy_term__' . $sanitized_view_mode;
}

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function ucsf_theme_suggestions_views_view_alter(array &$suggestions, array $variables) {
  $view = $variables['view'];

  $suggestions[] = 'views_view__' . $view->id();
  $suggestions[] = 'views_view__' . $view->id() . '__' . $view->current_display;
}

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function ucsf_theme_suggestions_views_view_unformatted_alter(array &$suggestions, array $variables) {
  $view = $variables['view'];

  $suggestions[] = 'views_view_unformatted__' . $view->id();
  $suggestions[] = 'views_view_unformatted__' . $view->id() . '__' . $view->current_display;
}
