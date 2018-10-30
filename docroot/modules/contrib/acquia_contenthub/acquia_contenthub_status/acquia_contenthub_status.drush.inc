<?php

/**
 * @file
 * Acquia Contenthub Status module drush command.
 */

/**
 * Implements hook_drush_command().
 */
function acquia_contenthub_status_drush_command() {
  $items = [];
  $items['acquia-contenthub-status-check'] = [
    'description' => 'Run status check for imported entities with Content Hub',
    'aliases' => ['ach-st-ch'],
    'arguments' => [
      'limit' => 'Count of items to fetch form history.',
      'threshold' => 'How many minutes imported entities can be behind by Content Hub.',
    ],
    'outputformat' => [
      'default' => 'table',
      'pipe-format' => 'list',
      'field-default' => [
        'uuid',
        'diff',
        'local_timestamp',
        'remote_timestamp',
      ],
      'field-labels' => [
        'uuid' => dt('Entity UUID'),
        'diff' => dt('Behind by Content Hub (minutes)'),
        'local_timestamp' => dt('Local entity Timestamp'),
        'remote_timestamp' => dt('Remote entity Timestamp'),
      ],
      'output-data-type' => 'format-table',
    ],
  ];

  return $items;
}

/**
 * Drush command handler.
 *
 * @param null|int $limit
 *   Limit count history entities.
 * @param null|int $threshold
 *   How many minutes imported entities can be behind by Content Hub.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_status_check($limit = NULL, $threshold = NULL) {

  if (!empty($limit)) {
    $limit = (int) $limit;
    if ($limit < 1 || $limit > 500) {
      return drush_set_error(dt("The limit has to be an integer from 1 to 500."));
    }
  }

  if (!empty($threshold)) {
    $threshold = (int) $threshold;
    if ($threshold < 1 || $threshold > 120) {
      return drush_set_error(dt("The threshold has to be an integer from 1 to 120."));
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
    return $rows;
  }

  drush_print(dt("Imported content is up-to date."));
  return FALSE;
}
