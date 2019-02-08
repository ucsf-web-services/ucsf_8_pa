<?php
/**
 * User: Eric Guerin
 * Date: 2/6/19
 * Time: 1:22 PM
 */
namespace Drupal\ucsf_search\Controller;

use Drupal\Core\Controller\ControllerBase;

class UcsfsearchController extends ControllerBase {
    public function content() {

      $markup = <<<MARKUP
        <div class="form-search-block-div">
          <div class="search-box-container">
            <gcse:searchbox enableAutoComplete="true"></gcse:searchbox>
          </div>
          <div id="cse">
            <gcse:searchresults queryParameterName="gcsearch"></gcse:searchresults>
          </div>
        </div>
MARKUP;


      return [
        '#type' => 'inline_template',
        '#template' => $markup,
        'attached' => [
          'library' => [
            'ucsf_search/ucsf_search'
          ]
        ]
      ];

    }
}