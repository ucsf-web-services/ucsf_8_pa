<?php
// adds a target class for animation.
// dynamically loads in a JS library to do animation
function ucsf_preprocess_views_view(&$variables) {
  if ($variables['id'] == 'articles_by_term') {
    $variables['attributes']['class'][] = 'element-swoosh';
    $variables['#attached']['library'][] = 'ucsf/element_swoosh';
  }

  // Set the correct href for the "more-link" in the "press-release" view.
  if ($variables['id'] == 'press_releases') {
    $variables['more']['#options']['attributes']['class'] = [
      'button',
      'redirect-to-advanced'
    ];

    if (isset($variables['more']['#url'])) {
      // Build search query for the 'news/filter'
      $query = [
        // Only search within 'Press Releases
        'tid[714]' => '714',
        'field_date_and_time_value_1' => 'All',
        'field_date_and_time_value_2' => 'All',
      ];

      $variables['more']['#url']->setOption('query', $query);
    }
  }

  if ($variables['id'] == 'news_filter') {
    $variables['#attached']['library'][] = 'ucsf/view_news_filter';
  }
}
