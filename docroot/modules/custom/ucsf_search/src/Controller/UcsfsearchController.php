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
use Symfony\Component\Cache;

class UcsfsearchController extends ControllerBase {

  public function search(Post $request) {

    $searchterm = '';
    //@todo try using symfony request object instead of direct get request
    if(isset($_GET['search'])) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }
    $websites  = $this->websiteLookup($searchterm);
    $people = $this->directoryLookup($searchterm);

    return [
      '#theme' => 'ucsf_universal_search',
      '#directory' => $people['results'],
      '#websites' => $websites['results'],
      '#searchterm' => $searchterm,
      '#more' => [ 'web'=>$websites['more'], 'people'=>$people['more'] ],
      '#attached' => [
        'library' => [
          'ucsf_search/ucsf_search'
        ]
      ]
    ];

  }

  public function news(Post $request) {

    $searchterm = '';
    //@todo try using symfony request object instead of direct get request
    if(isset($_GET['search'])) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }

    return [
      '#theme' => 'ucsf_news_search',
      '#searchterm' => $searchterm,
      '#more' => [],
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'ucsf_search/ucsf_search'
        ]
      ]
    ];
  }

  public function people(Post $request) {

    $searchterm = '';
    //@todo try using symfony request object instead of direct get request
    if(isset($_GET['search'])) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }

    $directory = $this->directoryLookup($searchterm, 25);

    return [
      '#theme' => 'ucsf_people_search',
      '#directory' => $directory['results'],
      '#searchterm' => $searchterm,
      '#more' => $directory['more'],
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'ucsf_search/ucsf_search'
        ]
      ]
    ];
  }

  public function websites(Post $request) {

    $searchterm = '';
    //@todo try using symfony request object instead of direct get request
    if(isset($_GET['search'])) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }

    $websites = $this->websiteLookup($searchterm, 25);

    return [
      '#theme' => 'ucsf_websites_search',
      '#websites' => $websites['results'],
      '#searchterm' => $searchterm,
      '#more' => $websites['more'],
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'ucsf_search/ucsf_search'
        ]
      ]
    ];
  }
  /**
   * First check if the search string looks like a domain, if it does
   * and it doesn't match our domain(s) then ignore the search
   * /ucsf.edu|ucsfmedicalcenter.org|ucsfhealth.org/
   *
   * If doesn't appear to be a domain, then do a string lookup
   * check the meta title, title, and description fields in the
   * combine filer.
   *
   * Cache all results for further usage using a filtered, trimmed and
   * normalized search term.
   *
   * Return the top-three results as array.
   * If more then three results show the "more results" button"
   *
   * @param $search
   * @param int $limit
   * @return array
   */
  protected function websiteLookup($search, $limit=3) {

    $base_url = 'http://local.websites.ucsf.edu';
    //don't search anything under 3 characters, reduce lookup load
    if (strlen($search)<3) {
      return [];
    }
    //url encode the string for searching
    //@todo make #search the cache key, $directory the cache items
    $search = urlencode($search);

    if (preg_match('#^([\w-]+\.)+(ucsfopenresearch\.org|ucsfmedicalcenter\.org|ucsfnursing\.org|ucsfhealth\.org|immunetolerance\.org|ucsf\.edu|ucsfdentalcenter\.org|ucsfcme\.com)+(\:|\/)+([\w-./?%&=\#\$~,_\[\]:()@\^+.]*)?$#', $search, $matches)) {
      // might be a domain lookup
    }


    //@todo - check the cache for the results
    //call GuzzleHTTP for the lookup and JSON decode the body request
    $client       = new Client(array('base_uri' => $base_url));
    $res          = $client->request('GET', "/azlist/json?combine={$search}");
    $jsonresponse = json_decode($res->getBody(), TRUE);

    $results = [];
    $cnt = 0;
    $items = count($jsonresponse['nodes']);
    $more = ($items > $limit) ? true : false;

    if ($items > 0) {
      foreach ($jsonresponse['nodes'] as $key=>$node) {
          $results[] = $node['node'];
          $cnt++;
          if ($cnt==($limit)) break;
      }
      //dpm($results);
      return ['results'=>$results, 'more'=>$more];
    } else {
      return ['results'=>[], 'more'=>false];
    }

  }

  /**
   * DIRECTORY API is retiring soon, might need to change this eventually.
   * https://directory.ucsf.edu/people/search/name/john%20Kealy/json
   */
  protected function directoryLookup($search, $limit=3) {

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

    $cnt=0;
    $directory = [];
    $items = count($jsonresponse['data']);
    $more = ($items > $limit) ? true : false;

    //return the array of data values
    if ($items > 0) {
      // fix the odd array structure from the API
      foreach ($jsonresponse['data'] as $person) {
        foreach ($person as $key=>$value) {
            $person[$key] = $value[0];
        }
        $directory[] = $person;
        $cnt++;
        if ($cnt==($limit)) break;
      }

      //@todo add a see more here to take you to the directory.ucsf.edu website results

      //@todo store the results in the cache using the key for further lookup
      return ['results'=>$directory, 'more'=>$more];
    } else {
      return ['results'=>[], 'more'=>false];
    }

  }
}