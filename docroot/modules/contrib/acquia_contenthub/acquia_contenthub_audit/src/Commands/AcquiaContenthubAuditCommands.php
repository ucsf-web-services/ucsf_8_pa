<?php

namespace Drupal\acquia_contenthub_audit\Commands;

use Drupal\acquia_contenthub_audit\fileExistsOrDirectoryisWritableTrait;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class AcquiaContenthubAuditCommands extends DrushCommands {

  use fileExistsOrDirectoryisWritableTrait;

  /**
   * Checks imported entities and compares them to Content Hub.
   *
   * @param string $manifest_file
   *   The File name including path to write the manifest to.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option types
   *   Comma separated list of entity types to search and delete.
   *
   * @throws \Exception
   *
   * @command acquia:contenthub-audit-subscriber
   * @aliases ach-as,ach-audit-subscriber
   */
  public function contenthubAuditSubscriber(string $manifest_file, array $options = ['types' => '']) {
    $this->fileExistsOrDirectoryIsWritable($manifest_file);
    $audit = $this->getSubscriberAudit();

    $entity_type_ids = array_filter(explode(',', $options['types']));
    $audit->generateManifest($manifest_file, $entity_type_ids);
  }

  /**
   * Executes a Manifest file.
   *
   * @param string $manifest_file
   *   Filename including path to write the manifest to.
   * @param string $output_manifest_file
   *   Filename including path to write the resulting manifest after execution.
   *
   * @throws \Exception
   *
   * @command acquia:contenthub-audit-subscriber-execute-manifest
   * @aliases ach-asem,ach-audit-subscriber-exe-manifest
   */
  public function contenthubAuditSubscriberExecuteManifest(string $manifest_file, string $output_manifest_file) {
    if (!$this->io()->confirm(sprintf('Are you sure you want to execute Manifest file "%s" and write its resulting output to "%s"? Please make sure you make a database backup first. There is no way back from this action!', $manifest_file, $output_manifest_file))) {
      return;
    }
    $this->fileExistsOrDirectoryIsWritable($manifest_file);
    $this->fileExistsOrDirectoryIsWritable($output_manifest_file);
    $audit = $this->getSubscriberAudit();
    $audit->executeManifest($manifest_file, $output_manifest_file);
  }

  /**
   * Obtains the subscriber audit.
   *
   * @return \Drupal\acquia_contenthub_audit\SubscriberAudit
   *   The Subscriber audit.
   */
  protected function getSubscriberAudit() {
    return \Drupal::service('acquia_contenthub_audit.subscriber_audit');
  }

}
