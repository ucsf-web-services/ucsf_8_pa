<?php

namespace Drupal\acquia_contenthub;

use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Perform queries to the Content Hub "_search" endpoint [Elasticsearch].
 */
class ContentHubSearch {

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager
   */
  protected $clientManager;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    return new static(
      $container->get('acquia_contenthub.client_manager'),
      $language_manager,
      $entity_type_manager
    );
  }

  /**
   * Constructs an ContentEntityNormalizer object.
   *
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager.
   */
  public function __construct(ClientManagerInterface $client_manager, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->clientManager = $client_manager;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Executes an elasticsearch query.
   *
   * @param array $query
   *   Search query.
   *
   * @return mixed
   *   Returns elasticSearch query response hits.
   */
  public function executeSearchQuery(array $query) {
    if ($query_response = $this->clientManager->createRequest('searchEntity', [$query])) {
      return $query_response['hits'];
    }
    return FALSE;
  }

  /**
   * Helper function to build elasticsearch query for terms using AND operator.
   *
   * @param string $search_term
   *   Search term.
   *
   * @return mixed
   *   Returns query result.
   */
  public function getFilters($search_term) {
    if ($search_term) {
      $items = array_map('trim', explode(',', $search_term));
      $last_item = array_pop($items);

      $query['query'] = [
        'query_string' => [
          'query' => $last_item,
          'default_operator' => 'and',
        ],
      ];
      $query['_source'] = TRUE;
      $query['highlight'] = [
        'fields' => [
          '*' => new \stdClass(),
        ],
      ];
      $result = $this->executeSearchQuery($query);
      return $result ? $result['hits'] : FALSE;
    }
  }

  /**
   * Builds elasticsearch query to get filters name for auto suggestions.
   *
   * @param string $search_term
   *   Given search term.
   *
   * @return mixed
   *   Returns query result.
   */
  public function getReferenceFilters($search_term) {
    if ($search_term) {

      $match[] = ['match' => ['_all' => $search_term]];

      $query['query']['filtered']['query']['bool']['must'] = $match;
      $query['query']['filtered']['query']['bool']['must_not']['term']['data.type'] = 'taxonomy_term';
      $query['_source'] = TRUE;
      $query['highlight'] = [
        'fields' => [
          '*' => new \stdClass(),
        ],
      ];
      $result = $this->executeSearchQuery($query);

      return $result ? $result['hits'] : FALSE;
    }
  }

  /**
   * Builds Search query for given search terms..
   *
   * @param array $conditions
   *   The conditions string that needs to be parsed for querying elasticsearch.
   * @param string $asset_uuid
   *   The Asset Uuid that came through a webhook.
   * @param string $asset_type
   *   The Asset Type (entity_type_id).
   * @param array $options
   *   An associative array of options for this query, including:
   *   - count: number of items per page.
   *   - start: defines the offset to start from.
   *
   * @return int|mixed
   *   Returns query response.
   */
  public function getElasticSearchQueryResponse(array $conditions, $asset_uuid, $asset_type, array $options = []) {
    $query = [
      'query' => [
        'bool' => [
          'must' => [],
        ],
      ],
      'size' => !empty($options['count']) ? $options['count'] : 10,
      'from' => !empty($options['start']) ? $options['start'] : 0,
      'highlight' => [
        'fields' => [
          '*' => new \stdClass(),
        ],
      ],
    ];

    // Iterating over each condition.
    foreach ($conditions as $condition) {
      list($filter, $value) = explode(':', $condition);

      // Tweak ES query for each filter condition.
      switch ($filter) {

        // For Search Term (Keyword).
        case 'search_term':
          if (!empty($value)) {
            $keywordQuery = $this->getQueryFromString($value);
            if ($keywordQuery != false) {
              $query['query']['bool']['must'][] = [
                $keywordQuery,
              ];
            }
           }
           break;

        // For entity types.
        case 'entity_types':
          $query['query']['bool']['must'][] = [
            'terms' => [
              'data.type' => explode(',', $value),
            ],
          ];
          break;

        // For bundles.
        case 'bundle':
          // Obtaining bundle_key for this bundle.
          $bundle_key = $this->entityTypeManager->getDefinition($asset_type)->getKey('bundle');
          if (empty($bundle_key)) {
            break;
          }

          // Test all supported languages.
          $supported_languages = array_keys($this->languageManager->getLanguages(LanguageInterface::STATE_ALL));
          if (empty($supported_languages)) {
            break;
          }

          // Create and add bundle's bool queries.
          $bundle_bool_queries = [
            'bool' => [
              'should' => [],
            ],
          ];
          $bundles = explode(',', $value);
          foreach ($bundles as $bundle) {
            foreach ($supported_languages as $supported_language) {
              $bundle_bool_queries['bool']['should'][] = [
                'term' => [
                  "data.attributes.{$bundle_key}.value.{$supported_language}" => $bundle,
                ],
              ];
            }
          }
          $query['query']['bool']['must'][] = $bundle_bool_queries;
          break;

        // For Tags.
        case 'tags':
          // Create and add bundle's bool queries.
          $tags_bool_queries = [
            'bool' => [
              'should' => [],
            ],
          ];
          $tags = explode(',', $value);
          foreach ($tags as $tag) {
            $keywordQuery = $this->getQueryFromString($tag);
            $tags_bool_queries['bool']['should'] = [
              $keywordQuery
            ];
          }
          $query['query']['bool']['must'][] = $tags_bool_queries;
          break;

        // For Origin / Source.
        case 'origins':
          $query['query']['bool']['must'][] = [
            'match' => [
              'data.origin' => $value,
            ],
          ];
          break;

        case 'modified':
          $date_modified['time_zone'] = '+1:00';
          $dates = explode('to', $value);
          $from = isset($dates[0]) ? trim($dates[0]) : '';
          $to = isset($dates[1]) ? trim($dates[1]) : '';
          if (!empty($from)) {
            $date_modified['gte'] = $from;
          }
          if (!empty($to)) {
            $date_modified['lte'] = $to;
          }
          $query['query']['bool']['must'][] = [
            'range' => [
              'data.modified' => $date_modified,
            ],
          ];
          break;
      }
    }
    if (!empty($options['sort']) && strtolower($options['sort']) !== 'relevance') {
      $query['sort']['data.modified'] = strtolower($options['sort']);
    }
    if (isset($asset_uuid)) {
      $query['query']['bool']['must'][] = [
        'term' => [
          '_id' => $asset_uuid,
        ],
      ];
    }

    return $this->executeSearchQuery($query);
  }

   /**
    * Breaks the query string into individual alphanumeric components.
    * This deals with uuids, so that each component of a uuid is AND'd
    * it also deals with any other characters in the search query that are not
    * alphanumeric and treats them as delimeters - ignoring them
    *
    * @param string $queryString
    *   The search value to be passed to the query
    *
    * @return array
    *   An Elastic Search query segment that can be inserted into the main query
    */
  private function getQueryFromString($queryString) {
     $query = [
      'bool' => [
        'must' => [],
      ],
    ];

    // explode the search term into parts, ignore any that are null/empty
    $queryStringTokens = preg_split("/[^a-zA-Z\d:]+/",$queryString);
    foreach ($queryStringTokens as $token) {
      if (strlen($token) != 0) {
        $query['bool']['must'][] = [
          'match' => [
            '_all' => "*{$token}*",
          ],
        ];
      }
    }
    return $query;
  }

  /**
   * Executes and ES query for a given asset uuid.
   *
   * @param array $conditions
   *   List of filter conditions.
   * @param string $asset_uuid
   *   The Asset Uuid that arrived through a webhook.
   * @param string $asset_type
   *   The Asset Type (entity_type_id).
   *
   * @return bool
   *   Returns query result.
   */
  public function buildElasticSearchQuery(array $conditions, $asset_uuid, $asset_type) {
    $result = $this->getElasticSearchQueryResponse($conditions, $asset_uuid, $asset_type);
    if ($result & !empty($result['total'])) {
      return $result['total'];
    }
    return 0;
  }

  /**
   * Builds elasticsearch query to retrieve data in reverse chronological order.
   *
   * @param array $options
   *   An associative array of options for this query, including:
   *   - count: number of items per page.
   *   - start: defines the offset to start from.
   *
   * @return mixed
   *   Returns query result.
   */
  public function buildChronologicalQuery(array $options = []) {

    $query['query']['match_all'] = new \stdClass();
    $query['sort']['data.modified'] = 'desc';
    if (!empty($options['sort']) && strtolower($options['sort']) !== 'relevance') {
      $query['sort']['data.modified'] = strtolower($options['sort']);
    }
    $query['filter']['term']['data.type'] = 'node';
    $query['size'] = !empty($options['count']) ? $options['count'] : 10;
    $query['from'] = !empty($options['start']) ? $options['start'] : 0;
    $result = $this->executeSearchQuery($query);

    return $result;
  }

}
