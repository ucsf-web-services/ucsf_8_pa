<?php

namespace Drupal\acquia_contenthub\QueueItem;

/**
 * Class ContentHubQueueItemBase.
 *
 * @package Drupal\acquia_contenthub\QueueItem
 */
abstract class ContentHubQueueItemBase implements ContentHubQueueItemInterface {

  /**
   * Get a property on the QueueItem.
   *
   * @param string $property
   *   The queue item property.
   *
   * @return bool|mixed
   *   The property value if exists, FALSE otherwise.
   */
  public function get($property) {
    return isset($this->{$property}) ? $this->{$property} : FALSE;
  }

}
