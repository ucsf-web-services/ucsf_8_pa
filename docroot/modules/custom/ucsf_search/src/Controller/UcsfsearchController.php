<?php

namespace Drupal\ucsf_search\Controller;

use GuzzleHttp\Client;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\Request as GetReq;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class UcsfsearchController extends ControllerBase {

  public function search(GetReq $request) {

    $searchterm = '';
    $websites = [ 'results'=>[], 'more'=>false ];
    $people   = [ 'results'=>[], 'more'=>false ];

    if($request->query->get('search')) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $request->query->get('search'));
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));

      //$websites = $this->websiteLookup($searchterm);
      $people   = $this->directoryLookup($searchterm);
    }

    $searchterm = $this->toCurlyQuotes($searchterm);

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

  public function news(GetReq $request) {

    $searchterm = '';

    if($request->query->get('search')) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $request->query->get('search'));
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
    }

    $searchterm = $this->toCurlyQuotes($searchterm);

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

  public function people(GetReq $request) {

    $searchterm = '';
    $directory = [ 'results'=>[], 'more'=>false ];


    if($request->query->get('search')) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $request->query->get('search'));
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
      $directory = $this->directoryLookup($searchterm, 25);
    }

    $searchterm = $this->toCurlyQuotes($searchterm);

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

  public function websites(GetReq $request) {

    $searchterm = '';
    $websites = [ 'results'=>[], 'more'=>false ];

    if($request->query->get('search')) {
      $searchterm = preg_replace("/\r\n|\r|\n/", ' ', $request->query->get('search'));
      $searchterm = Xss::filter(htmlspecialchars($searchterm, ENT_QUOTES));
      $websites = $this->websiteLookup($searchterm, 25);
    }

    $searchterm = $this->toCurlyQuotes($searchterm);

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

    //don't search anything under 3 characters, reduce lookup load
    if (strlen($search)<3) {
      return [];
    }

    //make sure file system is available for the Cache Adapter
    $path = \Drupal::service('file_system')->realpath(\Drupal::config('system.file')->get('default_scheme') . "://");
    $path .= '/cache';
    $file_system_interface = \Drupal::service('file_system');
    if ($file_system_interface->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY)) {
      $cache = new FilesystemAdapter('', 86400, $path);
    } else {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('Cache directory is not writable.');
      return false;
    }

    //url encode the string for searching
    $search   = urlencode($search);
    $cachekey = "websites.{$search}";

    $cachedItem = $cache->getItem($cachekey);
    if ($cache->hasItem($cachekey)) {
      $results = $cachedItem->get();
      $resultSlice = array_slice($results['results'], 0, $limit);

      \Drupal::logger('ucsf_search')->notice('Used cached result for key: '. $cachekey);
      return ['results'=>$resultSlice, 'more'=>$results['more']];
    }

    //call GuzzleHTTP for the lookup and JSON decode the body request
    $base_url = 'https://websites.ucsf.edu';
    $client       = new Client(['base_uri' => $base_url, 'http_errors' => false]);
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
          if ($cnt==24) break;
      }

      $resultSet = ['results'=>$results, 'more'=>$more];
      $cachedItem->set($resultSet);
      $cache->save($cachedItem);
      \Drupal::logger('ucsf_search')->notice('Saved search cache: '. $cachekey);

      //only return the limit, but store 25 results
      $resultSlice = array_slice($results, 0, $limit);

      return ['results'=>$resultSlice, 'more'=>$more];
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

    //make sure file system is available for the Cache Adapter
    $path = \Drupal::service('file_system')->realpath(\Drupal::config('system.file')->get('default_scheme') . "://");
    $path .= '/cache';
    $file_system_interface = \Drupal::service('file_system');
    if ($file_system_interface->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY)) {
      $cache = new FilesystemAdapter('', 86400, $path);
    } else {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('Cache directory is not writable.');
      return false;
    }

    //url encode the string for searching
    //@todo make #search the cache key, $directory the cache items
    // disregard quotes and match results in people directory
    $search = $this->removeAllQuotes($search);
    $search = urlencode($search);
    $cachekey = "people.{$search}";

    $cachedItem = $cache->getItem($cachekey);
    if ($cache->hasItem($cachekey)) {
      //we always get the 25 results back so depending on limit, shorten it
      $results = $cachedItem->get();
      $resultSlice = array_slice($results['results'], 0, $limit);
      \Drupal::logger('ucsf_search')->notice('Used cached result for key: '. $cachekey);

      return ['results'=>$resultSlice, 'more'=>$results['more']];
    }


    //call GuzzleHTTP for the lookup and JSON decode the body request
    $client       = new Client(['base_uri' => 'https://directory.ucsf.edu', 'http_errors' => false]);
    $res          = $client->request('GET', "/people/search/name/{$search}/json");
    $jsonresponse = json_decode($res->getBody(), TRUE);
    //dpm("https://directory.ucsf.edu/people/search/name/{$search}/json");
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
        if ($cnt==24) break;
      }

      $resultSet = ['results'=>$directory, 'more'=>$more];
      $cachedItem->set($resultSet);
      $cache->save($cachedItem);
      \Drupal::logger('ucsf_search')->notice('Saved cache for key: '. $cachekey);

      //fix return length after storing the 25 results for the big page
      $resultSlice = array_slice($directory, 0, $limit);

      return ['results'=>$resultSlice, 'more'=>$more];
    } else {
      return ['results'=>[], 'more'=>false];
    }

  }

  // Convert regular quotes into curly quotes.
  protected function toCurlyQuotes($searchterm) {
    return preg_replace(['/\b&quot;/','/&quot;/','/\b&#039;/','/&#039;/'], ['”','“',"’","‘"], $searchterm );
  }

  // Remove all quotes from the search string.
  protected function removeAllQuotes($searchterm) {
    return str_replace(["‘", "’", "“", "”", "`","\"","b&quot;", "&quot;", "b&#039;", "&#039;"], "", $searchterm);
  }
}
