<?php

namespace Drupal\acquia_contenthub_subscriber;

use Drupal\Core\Language\LanguageInterface;

/**
 * Elasticsearch query generator.
 *
 * @package Drupal\acquia_contenthub_audit
 */
class ESQueryGenerator implements ESQueryGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function getElasticSearchQuery(ContentHubFilterInterface $filter, $asset_uuid, $asset_type, array $options = []) {
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
    // Supported Entity Types and Bundles.
    $supported_entity_types_bundles = $this->getSubscriberSupportedEntityBundles();
    // Iterating over each condition.
    foreach ($filter->getConditions() as $condition) {
      list($filter, $value) = explode(':', $condition);
      // Tweak ES query for each filter condition.
      switch ($filter) {
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
          foreach ($supported_entity_types_bundles as $entity_type => $bundles) {
            foreach (explode(',', $value) as $bundle_value) {
              if (in_array($bundle_value, $bundles['bundles'], TRUE)) {
                $bundle_key = $bundles['bundle_key'];
                break;
              }
            }
          }
          if (empty($bundle_key)) {
            break;
          }
          // Test all supported languages.
          $supported_languages = array_keys(\Drupal::languageManager()->getLanguages(LanguageInterface::STATE_ALL));
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

        // For Search Term (Keyword).
        case 'search_term':
          if (!empty($value)) {
            $keywordQuery = $this->getQueryFromString($value);
            if ($keywordQuery !== FALSE) {
              $query['query']['bool']['must'][] = [
                $keywordQuery,
              ];
            }
          }
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
            $tags_bool_queries['bool']['should'][] = $keywordQuery;
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
    return $query;
  }

  /**
   * Obtains a list of supported entity bundles.
   */
  protected function getSubscriberSupportedEntityBundles() {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_manager = \Drupal::getContainer()->get('acquia_contenthub.entity_manager');
    /** @var \Drupal\acquia_contenthub\EntityManager $entity_manager */
    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_types = $entity_manager->getAllowedEntityTypes();
    $entity_types_and_bundles = [];
    foreach ($entity_types as $entity_type => $bundles) {
      if ($entity_type === 'taxonomy_term') {
        $bundle_key = 'vocabulary';
      }
      else {
        $bundle_key = $entity_type_manager->getDefinition($entity_type)->getKey('bundle');
      }
      $entity_types_and_bundles[$entity_type] = [
        'bundle_key' => $bundle_key,
        'bundles' => array_keys($bundles),
      ];
    }
    return $entity_types_and_bundles;
  }

  /**
   * Converts a query string to a query array.
   */
  protected function getQueryFromString($queryString) {
    $query = [
      'bool' => [
        'must' => [],
      ],
    ];

    // Explode the search term into parts, ignore any that are null/empty.
    $queryStringTokens = preg_split("/[^a-zA-Z\d:]+/", $queryString);
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

}
