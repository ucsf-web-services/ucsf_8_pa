<?php

/**
 * Implements template_preprocess_paragraph().
 */
function ucsf_preprocess_paragraph(&$variables) {
  $paragraph = $variables['paragraph'];
  $type = $paragraph->getType();

  if ($type == 'featured') {
    $node = $paragraph->field_news_article->entity;
    if ($node) {
      $node_type = $node->getType();
      $variables['article_type'] = $node_type;

      // get variable for primary area to use in twig
      $term = $node->field_areas->entity;
      if ($term) {
        $primary_area = $term->getName();
        $variables['primary_area'] = $primary_area;
      }
    }
  }
}
