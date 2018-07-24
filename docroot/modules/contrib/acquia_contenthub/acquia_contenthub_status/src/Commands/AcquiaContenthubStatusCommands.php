<?php

namespace Drupal\acquia_contenthub_status\Commands;

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
class AcquiaContenthubStatusCommands extends DrushCommands {

  /**
   * Run status check for imported entities with Content Hub
   *
   * @param $limit
   *   Count of items to fetch form history.
   * @param $threshold
   *   How many minutes imported entities can be behind by Content Hub.
   *
   * @command acquia:contenthub-status-check
   * @aliases ach-st-ch,acquia-contenthub-status-check
   */
  public function contenthubStatusCheck($limit, $threshold) {
    if (!empty($limit)) {
      $limit = (int) $limit;
      if ($limit < 1 || $limit > 500) {
        throw new \Exception(dt("The limit has to be an integer from 1 to 500."));
      }
    }

    if (!empty($threshold)) {
      $threshold = (int) $threshold;
      if ($threshold < 1 || $threshold > 120) {
        throw new \Exception(dt("The threshold has to be an integer from 1 to 120."));
      }
    }

    $rows = [];
    $statusService = \Drupal::service('acquia_contenthub_status.status');
    $result = $statusService->checkImported(NULL, $limit, $threshold);

    foreach ($result as $uuid => $data) {
      $rows[] = [
        'uuid' => $uuid,
        'diff' => $data['diff'],
        'local_timestamp' => $data['local_timestamp'],
        'remote_timestamp' => $data['remote_timestamp'],
      ];
    }

    if (count($rows) > 0) {
      $this->output()->writeln(print_r($rows, TRUE));
      return;
    }

    $this->output()->writeln(dt("Imported content is up-to date."));
  }

}
