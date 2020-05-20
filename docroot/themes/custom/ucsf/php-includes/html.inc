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

  // In order to use Sass BEM naming convention adding block class .magazine to the <body>
  // Get current path
  $current_path = \Drupal::service('path.current')->getPath();
  $alias = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);

  // check if path has /magazine in it
  if (strpos($alias, '/magazine') === 0) {
    // Add class to the <body>
    $variables['attributes']['class'][] = 'magazine';
  }

}

function ucsf_page_attachments_alter(array &$page) {

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
}

