<?php

namespace Drupal\acquia_contenthub_status;

use DateTime;
use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class StatusService.
 */
class StatusService {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */

  protected $configFactory;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManagerInterface
   */
  protected $clientManager;


  /**
   * The Drupal Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The ContentHubEntitiesTracking class.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  protected $entityTracking;

  /**
   * StatusService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entity_tracking
   *   The ContentHubEntitiesTracking.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    ClientManagerInterface $client_manager,
    ContentHubEntitiesTracking $entity_tracking) {

    $this->loggerFactory = $logger_factory;
    $this->configFactory = $config_factory;
    $this->clientManager = $client_manager;
    $this->entityTracking = $entity_tracking;

    // Get the content hub config settings.
    $this->config = $this->configFactory->get('acquia_contenthub_status.settings');
  }

  /**
   * Method to fetch information about history events.
   *
   * @param int $limit
   *   Count of items to fetch form history.
   * @param null|string $start_time
   *   Start date. Should be in ISO8601 format or ElasticSearch format and UTC.
   * @param string $end_time
   *   End date. Should be in ISO8601 format or ElasticSearch format and UTC.
   *
   * @return array
   *   Array[uuid] = timestamp
   */
  public function getHistory($limit = 20, $start_time = NULL, $end_time = 'now/m') {

    // Initial query.
    $q = [
      'query' => [
        'filtered' => [
          'query' => [
            'bool' => [
              'must' => [
                [
                  'terms' => [
                    'status' => ['succeeded', 'failed'],
                  ],
                ],
                [
                  'terms' => [
                    'type' => ['create', 'update'],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],

      // It's important to fetch last events.
      'sort' => [
        'timestamp' => [
          'order' => 'desc',
        ],
      ],
    ];

    // Adds time range filter.
    if ($start_time !== NULL) {
      $q['query']['filtered']['filter'] = [
        'range' => [
          'timestamp' => [
            'gte' => $start_time,
            'lte' => $end_time,
          ],
        ],
      ];
    }

    $query = json_encode($q);
    $options = ['size' => $limit];
    $ids = [];
    if ($logs = $this->clientManager->createRequest('logs', [$query, $options])) {
      // Process result.
      if (isset($logs['hits']['hits'])) {
        foreach ($logs['hits']['hits'] as $log) {
          // Some failed events does not contain uid.
          if ($log['_source']['entity']) {
            $key = $log['_source']['entity'];
            $timestamp = $log['_source']['timestamp'];

            // We need only last changes.
            if (!isset($ids[$key])) {
              $ids[$key] = $timestamp;
            }
          }
        }
      }
    }

    return $ids;
  }

  /**
   * Method to run check for imported entities.
   *
   * @param null|string $last_run
   *   Last check run. Should be in ISO8601 format or ElasticSearch format
   *    and UTC timezone.
   * @param null|int $limit
   *   Limit count history entities.
   * @param null|int $threshold
   *   How many minutes imported entities can be behind by Content Hub.
   *
   * @return array
   *   Array[uuid]
   *        ['diff'] - Difference in minutes
   *        ['local_timestamp'] - Date for local entity
   *        ['remote_timestamp'] - Last event date for this entity.
   */
  public function checkImported($last_run = NULL, $limit = NULL, $threshold = NULL) {
    $ids = [];
    $outdated = [];

    // Prepare options for history query.
    $threshold = !empty($threshold) ? $threshold : $this->config->get('threshold');
    $limit = !empty($limit) ? $limit : $this->config->get('limit');

    // Get history of entities events.
    $logs = $this->getHistory(
      $limit,
      $last_run
    );

    // Don't perform check if plexus return empty history.
    if (count($logs) > 0) {
      $ids = $this->entityTracking->loadMultipleByUuid(
        array_keys($logs)
      );
    }

    foreach ($ids as $uuid => $event_date) {
      // Convert string to DateTime object.
      $remote_event_date = new DateTime($logs[$uuid]);
      $local_event_date = new DateTime($event_date);

      // Check difference between two dates.
      $diff = $remote_event_date->diff($local_event_date);

      // Convert difference to minutes.
      $minutes = $diff->days * 24 * 60;
      $minutes += $diff->h * 60;
      $minutes += $diff->i;

      // Check difference with threshold value.
      if ($minutes > $threshold) {
        $outdated[$uuid] = [
          'diff' => $minutes,
          'local_timestamp' => $event_date,
          'remote_timestamp' => $logs[$uuid],
        ];
      }
    }

    return $outdated;
  }

}
