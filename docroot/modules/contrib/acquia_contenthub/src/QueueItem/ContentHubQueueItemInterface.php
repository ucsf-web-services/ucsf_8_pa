<?php

namespace Drupal\acquia_contenthub\QueueItem;

/**
 * Interface ContentHubQueueItemInterface.
 *
 * @package Drupal\acquia_contenthub\QueueItem
 */
interface ContentHubQueueItemInterface {

  /**
   * Get a property on the QueueItem.
   *
   * @param string $property
   *   A property key.
   *
   * @return bool|mixed
   *   The value on the queue item.
   */
  public function get($property);

}
