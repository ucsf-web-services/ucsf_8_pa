<?php

namespace Drupal\acquia_contenthub\Plugin\QueueWorker;

/**
 * Import content from the Acquia Content Hub service.
 *
 * @QueueWorker(
 *   id = "acquia_contenthub_import_queue",
 *   title = @Translation("Import Content from Acquia Content Hub")
 * )
 */
class ContentHubImportQueue extends ContentHubImportQueueBase {}
