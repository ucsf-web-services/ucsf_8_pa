<?php
// adds a target class for animation.
// dynamically loads in a JS library to do animation
function ucsf_preprocess_views_view(&$variables) {
  if ($variables['id'] == 'articles_by_term') {
    $variables['attributes']['class'][] = 'element-swoosh';
    $variables['#attached']['library'][] = 'ucsf/element_swoosh';
  }

  // TODO: set the correct href for the "more-link" in the "press-release" view.
  // TODO: add a class onto the "more-link" so that it looks correct.
  // /news/filter?combine=&tid%5B714%5D=714&field_date_and_time_value_1=All&field_date_and_time_value_2=All
}
