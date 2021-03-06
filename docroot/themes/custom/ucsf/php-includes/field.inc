<?php

/**
 * Implements template_preprocess_field().
 * Sets a trigger attribute for disabling the LazyLoad of banner images.
 */
function ucsf_preprocess_field(&$variables) {
  if ($variables['field_name'] == 'field_banner_image') {
    $variables["items"][0]["content"]["#item_attributes"]['data-no-lazyload'] = TRUE;
  };

  if ($variables['field_name'] == 'field_two_column_banner_image') {
    $variables["items"][0]["content"]["#item_attributes"]['data-no-lazyload'] = TRUE;
  };

  // Remove LazyLoad from Taxonomy Banner Images
  if ($variables['field_name'] == 'field_taxonomy_banner_image') {
    $variables["items"][0]["content"]["#item_attributes"]['data-no-lazyload'] = TRUE;
  }
}
