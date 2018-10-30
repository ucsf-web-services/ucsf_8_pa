<?php

namespace Drupal\acquia_contenthub\QueueItem;

/**
 * Class ImportQueueItem.
 *
 * @package Drupal\acquia_contenthub\QueueItem
 */
class ImportQueueItem extends ContentHubQueueItemBase implements ContentHubQueueItemInterface {

  /**
   * Import dependencies.
   *
   * @var bool
   */
  protected $dependencies;

  /**
   * Import the author.
   *
   * @var bool
   */
  protected $author;

  /**
   * Import the status.
   *
   * @var int
   */
  protected $status;

  /**
   * The entity UUID to import.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Construct the ImportQueueItem.
   *
   * @param string $uuid
   *   The UUID for an entity.
   * @param bool $dependencies
   *   Whether or not to import the dependencies.
   * @param bool $author
   *   Whether or not to import the author.
   * @param int $status
   *   The status to import the entity with.
   */
  public function __construct($uuid, $dependencies = TRUE, $author = TRUE, $status = 0) {
    $this->uuid = $uuid;
    $this->dependencies = $dependencies;
    $this->author = $author;
    $this->status = $status;
  }

}
