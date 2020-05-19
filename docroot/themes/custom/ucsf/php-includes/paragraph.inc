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
      $nodeType = $node->getType();
      $variables['article_type'] = $nodeType;
    }
  }
}
