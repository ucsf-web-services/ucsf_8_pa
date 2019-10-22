<?php
// adds a target class for animation.
// dynamically loads in a JS library to do animation
function ucsf_preprocess_views_view(&$variables) {
  if ($variables['id'] == 'articles_by_term') {
    $variables['attributes']['class'][] = 'element-swoosh';
    $variables['#attached']['library'][] = 'ucsf/element_swoosh';
  }
}
