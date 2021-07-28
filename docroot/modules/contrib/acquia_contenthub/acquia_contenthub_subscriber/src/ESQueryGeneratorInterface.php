<?php

namespace Drupal\acquia_contenthub_subscriber;

/**
 * Interface for elastic search query generation.
 *
 * @package Drupal\acquia_contenthub_audit
 */
interface ESQueryGeneratorInterface {

  /**
   * Obtains an Elasticsearch Query.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $filter
   *   The Content Hub Filter.
   * @param string $asset_uuid
   *   The asset UUID.
   * @param string $asset_type
   *   The asset type.
   * @param array $options
   *   The options array.
   *
   * @return mixed
   *   The Elastic Search Query.
   */
  public function getElasticSearchQuery(ContentHubFilterInterface $filter, $asset_uuid, $asset_type, array $options = []);

}
