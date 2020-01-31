<?php

namespace Drupal\metatag\Plugin\metatag\Tag;

/**
 * This base plugin allows "link rel" tags to be further customized.
 */
abstract class LinkRelBase extends MetaNameBase {

  /**
   * {@inheritdoc}
   */
  public function output() {
    $element = parent::output();
    $element = $this->convertToLink($element);
    // if $element is an array of multiple elements.
    if (empty($element['#attributes'])) {
      foreach ($element as $key => $value) {
        $element[$key] = $this->convertToLink($value);
      }
    }

    return $element;
  }

  /**
   * Function to convert Metatag into link.
   *
   * @param [array] $element
   * @return array
   */
  public function convertToLink($element) {
    if (!empty($element['#attributes']['content'])) {
      $element['#tag'] = 'link';
      $element['#attributes'] = [
        'rel' => $this->name(),
        'href' => $element['#attributes']['content'],
      ];
      unset($element['#attributes']['content']);
    }

    return $element;
  }

}
