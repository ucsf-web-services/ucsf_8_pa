<?php

namespace Drupal\acquia_contenthub_subscriber;

use Acquia\ContentHubClient\Entity as ContentHubEntity;
use Drupal\acquia_contenthub\ContentHubEntityDependency;
use Drupal\acquia_contenthub\ContentHubSearch;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Common methods for subscriber audit.
 *
 * @package Drupal\acquia_contenthub_audit
 */
class SubscriberCommon {

  /**
   * The list of entities returned by the executed filters.
   *
   * @var array
   */
  protected $entities = [];

  /**
   * A map of entity uuids to the filter they matched.
   *
   * @var array
   */
  protected $filterMap = [];

  /**
   * The Query Generator.
   *
   * @var \Drupal\acquia_contenthub_subscriber\ESQueryGeneratorInterface
   */
  protected $generator;

  /**
   * Entity Storage for Content Hub Filters.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $contentHubFilterStorage;

  /**
   * The Content Hub Search Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSearch
   */
  protected $search;

  /**
   * Public Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager Service.
   * @param \Drupal\acquia_contenthub\ContentHubSearch $search
   *   The Acquia ContentHub Search Service.
   * @param \Drupal\acquia_contenthub_subscriber\ESQueryGeneratorInterface $generator
   *   The Query Generator Service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ContentHubSearch $search, ESQueryGeneratorInterface $generator) {
    $this->contentHubFilterStorage = $entity_type_manager->getStorage('contenthub_filter');
    $this->search = $search;
    $this->generator = $generator;
  }

  /**
   * Executes all filters.
   */
  public function executeAllFilters($include_none = FALSE) {
    $contenthub_filters = $this->contentHubFilterStorage->loadMultiple();
    foreach ($contenthub_filters as $filter) {
      if ($filter->getPublishStatus() === FALSE && !$include_none) {
        continue;
      }
      $entities = $this->executeFilter($filter, 0, 1);
      // Do something with the entities.
      $total = $entities['total'];
      // Dividing into batches of 1000 entities.
      $iterations = ceil($total / 1000);
      for ($i = 0; $i < $iterations; $i++) {
        $start = $i * 1000;
        $this->entities += $this->executeFilter($filter, $start);
      }
    }
    unset($this->entities['total']);
    return $this->entities;
  }

  /**
   * Execute a given filter and return the retrieved entities.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $contenthub_filter
   *   The filter to execute.
   * @param int $start
   *   The start for query paging.
   * @param int $size
   *   The limit for query paging.
   *
   * @return \Acquia\ContentHubClient\Entity[]
   *   An array of entities.
   */
  public function executeFilter(ContentHubFilterInterface $contenthub_filter, $start = 0, $size = 1000) {
    $options = [
      'start' => $start,
      'count' => $size,
    ];
    // Obtain the Filter conditions.
    $conditions = $contenthub_filter->getConditions();
    if (!empty($conditions)) {
      $items = $this->getElasticSearchQueryResponse($contenthub_filter, NULL, NULL, $options);
      $entities = [
        'total' => $items['total'] ?? 0,
      ];
      if (!isset($items['hits'])) {
        return $entities;
      }
      foreach ($items['hits'] as $item) {
        if (!in_array($item['_source']['data']['type'], ContentHubEntityDependency::getPostDependencyEntityTypes())) {
          $entities[$item['_source']['data']['uuid']] = new ContentHubEntity($item['_source']['data']);
        }
      }
      $entities_uuids = $entities;
      foreach (array_keys($entities_uuids) as $uuid) {
        $this->filterMap[$uuid] = $contenthub_filter->id();
      }
      return $entities;
    }
    // If we reach here, return empty array.
    return [];
  }

  /**
   * Obtains the filter entities.
   */
  public function getFilterEntities() {
    return $this->entities;
  }

  /**
   * Resets the storage for filter entities.
   */
  public function resetFilterEntities() {
    $this->entities = [];
  }

  /**
   * Gets the filter map.
   */
  public function getFilterMap() {
    return $this->filterMap;
  }

  /**
   * Obtains an Elasticsearch query response.
   */
  protected function getElasticSearchQueryResponse(ContentHubFilterInterface $filter, $asset_uuid, $asset_type, array $options = []) {
    return $this->search->executeSearchQuery($this->generator->getElasticSearchQuery($filter, $asset_uuid, $asset_type, $options));
  }

}
