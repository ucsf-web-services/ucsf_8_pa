<?php

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function ucsf_theme_suggestions_form_alter(array &$suggestions, array $variables) {
  $suggestions[] = 'form__' . $variables['element']['#form_id'];
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
