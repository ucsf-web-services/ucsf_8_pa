<?php

namespace Drupal\acquia_contenthub\Plugin\QueueWorker;

/**
 * Export content to the Acquia Content Hub service.
 *
 * @QueueWorker(
 *   id = "acquia_contenthub_export_queue",
 *   title = @Translation("Export Content to Acquia Content Hub")
 * )
 */
class ContentHubExportQueue extends ContentHubExportQueueBase {}
