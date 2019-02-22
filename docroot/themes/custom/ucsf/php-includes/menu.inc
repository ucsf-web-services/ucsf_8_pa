<?php

function ucsf_preprocess_block(&$variables) {
  $variables['content']['#attributes']['block'] = $variables['attributes']['id'];
}

function ucsf_theme_suggestions_menu_alter(array &$suggestions, array $variables) {
  if (isset($variables['attributes']['block'])) {
    $hook = str_replace(array('block-', '-'), array('', '_'), $variables['attributes']['block']);
    $suggestions[] = $variables['theme_hook_original'] . '__' . $hook;
  }
}