<?php
/**
 * User: Eric Guerin
 * Date: 2/6/19
 * Time: 1:22 PM
 */
namespace Drupal\ucsf_search\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;

class UcsfsearchController extends ControllerBase {

  public function content() {

    $search = '';

    if(isset($_GET['search'])) {
      $search = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $search = Xss::filter(htmlspecialchars($search, ENT_QUOTES));
    }

    $directory = $this->directoryLookup($search);

    $markup = <<<XHTML

        <div class="form-search-block-div">
          <div class="search-box-container">
            <gcse:searchbox enableAutoComplete="true"></gcse:searchbox>
          </div>
          <div id="directorySearch">

          </div>
          <div id="websiteSearch">

          </div>
          <div id="cse">
            <gcse:searchresults queryParameterName="search" {$refinement}></gcse:searchresults>
          </div>
        </div>
XHTML;


      return [
        'ucsfsearch' => [
          '#type' => 'inline_template',
          '#template' => $markup,
          '#attached' => [
            'library' => [
              'ucsf_search/ucsf_search'
            ]
          ]
        ]
      ];

    }

  /**
   * DIRECTORY API is retiring soon, might need to change this eventually.
   * https://directory.ucsf.edu/people/search/name/john%20Kealy/json
   */
  protected function directoryLookup($search) {

      $searchterm = explode(' ', urldecode($search));

      if (count($searchterm) < 2) {
        return array();
      }

      $searchterm = urlencode(implode(' ', $searchterm));

      $client = new Client(array('base_uri' => 'https://directory.ucsf.edu'));
      $res = $client->request('GET', "/people/search/name/{$searchterm}/json");
      $response = json_decode($res->getBody(), TRUE);

      //dpm($response);
      if (isset($response['data'][0])) {
        // return the json results
        return $response['data'][0];
      }

    }


}