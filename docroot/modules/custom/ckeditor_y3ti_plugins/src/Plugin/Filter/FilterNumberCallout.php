<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\Filter\FilterNumberCallout.
 */
namespace Drupal\ckeditor_y3ti_plugins\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_numbercallout",
 *   title = @Translation("Number Callout Filter"),
 *   description = @Translation("add span around number"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */

class FilterNumberCallout extends FilterBase {

  public function process($text, $langcode) {
    // $result = new FilterProcessResult($text);

    // preg_match('/(<p class="number-highlight" slot="number-highlight">)(.*?)(<\/p>)/', $text, $match);
    preg_match_all('/(<p class="number-highlight" slot="number-highlight">)(.*?)(<\/p>)/', $text, $matches);
    if ($matches[0]) {
      $new_text = $text;
      foreach ($matches as $key => $match) {
        if ($key > 0){
          $toreplace = $matches[0][$key - 1];
          preg_match('/(?:(\D+))?(\d+)(?:(\D+))?/', $matches[2][$key - 1], $splitNum);
          $newNum = '';
          unset($splitNum[0]);
          foreach ($splitNum as $key => $value) {

            if (is_numeric($value)) {
              $newNum .= '<span class="counter">'.$value.'</span>' ;
            } else {
              $newNum .= $value;
            }
          }
          // kint($newNum);

          $replace = '<p class="number-highlight" slot="number-highlight">'.$newNum.'</p>';
          $new_text = str_replace($toreplace, $replace, $new_text);
        }

      }
      return new FilterProcessResult($new_text);
    }
    return new FilterProcessResult($text);
  }

}
