<?php

namespace Drupal\acquia_contenthub\Queue;

use Drupal\Component\Utility\Bytes;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\DatabaseQueue;

/**
 * Overridden queue implementation that evaluates memory usage.
 *
 * @ingroup queue
 */
class MemoryLimitDatabaseQueue extends DatabaseQueue {

  /**
   * The memory limit of the php runtime.
   *
   * @var int
   */
  protected $memoryLimit;

  /**
   * Constructs \Drupal\acquia_contenthub\Queue\MemoryLimitDatabaseQueue.
   *
   * @param string $name
   *   The name of the queue.
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection object containing the key-value tables.
   */
  public function __construct($name, Connection $connection) {
    // Record the memory limit in bytes.
    $limit = trim(ini_get('memory_limit'));
    if ($limit == '-1') {
      $this->memoryLimit = PHP_INT_MAX;
    }
    else {
      $this->memoryLimit = Bytes::toInt($limit);
    }
    parent::__construct($name, $connection);
  }

  /**
   * Find the php memory limit.
   *
   * @return int
   *   The memory limit.
   */
  protected function memoryLimit() {
    return $this->memoryLimit;
  }

  /**
   * Tries to reclaim memory.
   *
   * @return int
   *   The memory usage after reclaim.
   */
  protected function attemptMemoryReclaim() {
    // Entity storage can blow up with caches so clear them out.
    $manager = \Drupal::entityTypeManager();
    foreach ($manager->getDefinitions() as $id => $definition) {
      $manager->getStorage($id)->resetCache();
    }

    // @TODO: explore resetting the container.

    // Run garbage collector to further reduce memory.
    gc_collect_cycles();

    return memory_get_usage();
  }

  /**
   * {@inheritdoc}
   */
  public function claimItem($lease_time = 30) {
    // Allow for the queue to be paused via Drupal state. Useful when the queue
    // is processed by multiple workers and distributed across machines, this
    // central switch allow for a graceful pause of the entire processing.
    $paused = \Drupal::state()
      ->get('acquia_contenthub.export_queue.paused');
    if ($paused) {
      drush_log(dt("Acquia Content Hub export queue is currently paused."));
      return FALSE;
    }

    // Claim an item by updating its expire fields. If claim is not successful
    // another thread may have claimed the item in the meantime. Therefore loop
    // until an item is successfully claimed or we are reasonably sure there
    // are no unclaimed items left.
    while (($this->attemptMemoryReclaim() * 2) <= $this->memoryLimit()) {
      try {
        $item = $this->connection->queryRange('SELECT data, created, item_id FROM {' . static::TABLE_NAME . '} q WHERE expire = 0 AND name = :name ORDER BY created, item_id ASC', 0, 1, [':name' => $this->name])->fetchObject();
      }
      catch (\Exception $e) {
        $this->catchException($e);
        // If the table does not exist there are no items currently available to
        // claim.
        return FALSE;
      }
      if ($item) {
        // Try to update the item. Only one thread can succeed in UPDATEing the
        // same row. We cannot rely on REQUEST_TIME because items might be
        // claimed by a single consumer which runs longer than 1 second. If we
        // continue to use REQUEST_TIME instead of the current time(), we steal
        // time from the lease, and will tend to reset items before the lease
        // should really expire.
        $update = $this->connection->update(static::TABLE_NAME)
          ->fields([
            'expire' => time() + $lease_time,
          ])
          ->condition('item_id', $item->item_id)
          ->condition('expire', 0);
        // If there are affected rows, this update succeeded.
        if ($update->execute()) {
          $item->data = unserialize($item->data);
          return $item;
        }
      }
      else {
        // No items currently available to claim.
        return FALSE;
      }
    }
    if ($this->numberOfItems()) {
      drush_log(dt("The queue operation has run out of memory. There are @number items left in the queue. Restart the queue to continue processing.", ['@number' => $this->numberOfItems()]));
    }
  }

}
