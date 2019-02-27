<?php

/**
 * @file
 * Contains \Drupal\ckeditor_y3ti_plugins\Plugin\Filter\FilterCta.
 */
namespace Drupal\ckeditor_y3ti_plugins\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_cta",
 *   title = @Translation("Cta Filter"),
 *   description = @Translation("Format Image cta"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */

class FilterCta extends FilterBase {

  public function process($text, $langcode) {
    // $result = new FilterProcessResult($text);

    preg_match_all('/(<dom-cta .*?>)(.*?)(<\/dom-cta>)/s', $text, $matches);
    if ($matches[0]) {
      $new_text = $text;
      foreach ($matches as $key => $match) {
//        if ($key > 0){
          $href = $imgSrc = $ctaContent = array();
          preg_match('/(href=".*?")/s', $matches[2][$key], $href);
          preg_match('/(src=")(.*?)(")/s',$matches[2][$key], $imgSrc);
          $dom = new \DOMDocument();
          if($matches[2][$key]){$dom->loadHTML($matches[2][$key]);}

          $ctaContent  = $dom->saveXML($dom->getElementsByTagName('div')->item(0));

          $replace = $matches[1][$key].'<a '.$href[0].' style="background-image: url('.$imgSrc[2].')" >'.$ctaContent.'</a>'.$matches[3][$key];
          $new_text = str_replace($matches[0][$key], $replace, $new_text);
//        }

      }

      return new FilterProcessResult($new_text);
    }
    return new FilterProcessResult($text);
  }

}
