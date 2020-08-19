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

  $variables['ckeditor_y3ti_plugins'] = '/' . drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/elements/build.html';
}

function ucsf_page_attachments_alter(array &$page) {
  //dpm('page attachments alter -- ucsf/php-includes/html.inc');

  /**
   * @note - while this works, there doesn't appear to be a way to
   * weight the link below the js includes for polymer, which is what is
   * needed here.  Put the <link> into the template html.html.twig
   *
  $link = [
    '#type'=> 'html_tag',
    '#tag' => 'link',
    '#attributes' => [
      'name' => 'polymer_platform',
      'href' => '/'. drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/elements/elements.html',
      'rel' => 'import'
    ],
    '#weight' => '200'
  ];

  $page['#attached']['html_head'][] = [$link, 'polymer_platform'];
  */



  if (!isset($page['#attached']['html_head_link'])) {
    return;
  }

  $preload = [
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/GranjonLTW01-Roman.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-45Light.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-55Roman.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-56Italic.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-65Medium.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW06-75Bold.woff2',
      'as' => 'font'
    ],
    [
      'rel' => 'preload',
      'href' => '/themes/custom/ucsf/fonts/HelveticaNeueLTW04-65Medium.woff2',
      'as' => 'font'
    ],
  ];

  foreach ($preload as $index => $file) {
    $page['#attached']['html_head_link'][][] = $file;
  }

}

