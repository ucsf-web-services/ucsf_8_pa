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
use Symfony\Component\HttpFoundation\Request as Post;

class UcsfsearchController extends ControllerBase {

  public function content(Post $request) {

    $searchterm = '';
    //@todo try using symfony request object instead of direct get request
    if(isset($_GET['search'])) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }

    $directory = $this->directoryLookup($searchterm);

    return [
      '#theme' => 'ucsf_universal_search',
      '#results' => $directory,
      '#directory' => $directory,
      '#searchterm' => $searchterm,
      '#attached' => [
        'library' => [
          'ucsf_search/ucsf_search'
        ]
      ]
    ];

  }



  /**
   * DIRECTORY API is retiring soon, might need to change this eventually.
   * https://directory.ucsf.edu/people/search/name/john%20Kealy/json
   */
  protected function directoryLookup($search, $limit=null) {

    //don't search anything under 3 characters, reduce lookup load
    if (strlen($search)<3) {
      return [];
    }
    //url encode the string for searching
    //@todo make #search the cache key, $directory the cache items
    $search = urlencode($search);

    //@todo - check the cache for the results
    //call GuzzleHTTP for the lookup and JSON decode the body request
    $client       = new Client(array('base_uri' => 'https://directory.ucsf.edu'));
    $res          = $client->request('GET', "/people/search/name/{$search}/json");
    $jsonresponse = json_decode($res->getBody(), TRUE);


    //return the array of data values
    if (isset($jsonresponse['data'])) {
      $data = $jsonresponse['data'];
      // fix the odd array structure from the API
      foreach ($data as $person) {
        foreach ($person as $key=>$value) {
            $person[$key] = $value[0];
        }
        $directory[] = $person;
      }

      //@todo limit the results to three if univeral page

      //@todo store the results in the cache using the key for further lookup
      return $directory;
    } else {
      return [];
    }

  }
}