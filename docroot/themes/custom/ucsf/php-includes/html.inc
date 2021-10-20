<?php

function ucsf_preprocess_html(&$variables) {

  // Add path alias as body class.
  $current_path = \Drupal::service('path.current')->getPath();
  $path_alias = \Drupal::service('path_alias.manager')
    ->getAliasByPath($current_path);
  $path_alias = ltrim($path_alias, '/');

  $class = 'alias-' . \Drupal\Component\Utility\Html::cleanCssIdentifier($path_alias);

  $test1 = strpos($class, 'alias-user-');
  $test2 = strpos($class, '-edit');

  if ($test1 !== false && $test2 !== false){
    $class .= ' alias-user-edit';
  }

  $variables['attributes']['class'][] = $class;

  $variables['ckeditor_y3ti_plugins'] = '/' . drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/elements/build.html';
}

function ucsf_page_attachments_alter(array &$page) {

  if (!isset($page['#attached']['html_head_link'])) {
    return;
  }

  $preload = [
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/GranjonLTW01-Roman.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-45Light.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-55Roman.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-56Italic.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-65Medium.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW06-75Bold.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-65Medium.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/fontawesome/webfonts/fa-brands-400.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/fontawesome/webfonts/fa-solid-900.woff2',
      'as' => 'font',
      'crossorigin'=>true
    ]
  ];

  foreach ($preload as $index => $file) {
    $page['#attached']['html_head_link'][][] = $file;
  }

}

