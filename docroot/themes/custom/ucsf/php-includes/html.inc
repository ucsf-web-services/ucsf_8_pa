<?php

function ucsf_preprocess_html(&$variables) {

  // Add path alias as body class.
  $current_path = \Drupal::service('path.current')->getPath();
  $path_alias = \Drupal::service('path.alias_manager')
    ->getAliasByPath($current_path);
  $path_alias = ltrim($path_alias, '/');

  $class = 'alias-' . \Drupal\Component\Utility\Html::cleanCssIdentifier($path_alias);

  $test1 = strpos($class, 'alias-user-');
  $test2 = strpos($class, '-edit');

  if ($test1 !== false && $test2 !== false){
    $class .= ' alias-user-edit';
  }

  $variables['attributes']['class'][] = $class;
}
