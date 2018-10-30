<?php

namespace Drupal\acquia_contenthub\Queue;

use Drupal\Core\Queue\QueueDatabaseFactory;

/**
 * A factory for a database queue that checks memory limitations.
 */
class MemoryLimitDatabaseQueueFactory extends QueueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return new MemoryLimitDatabaseQueue($name, $this->connection);
  }

}
