<?php

namespace Drupal\Tests\acquia_contenthub_status\Unit;

use Drupal\acquia_contenthub_status\StatusService;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\acquia_contenthub_status\StatusService
 *
 * @group acquia_contenthub_status
 */
class StatusServiceTest extends UnitTestCase {

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
  protected $contentHubEntitiesTracking;

  /**
   * The Status Service.
   *
   * @var \Drupal\acquia_contenthub_status\StatusService
   */
  protected $statusService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->loggerFactory = $this->getMockBuilder('Drupal\Core\Logger\LoggerChannelFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->config = $this->getMockBuilder('Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory = $this->getMockBuilder('Drupal\Core\Config\ConfigFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory->method('get')
      ->with('acquia_contenthub_status.settings')
      ->willReturn($this->config);
    $this->clientManager = $this->getMock('\Drupal\acquia_contenthub\Client\ClientManagerInterface');
    $this->contentHubEntitiesTracking = $this->getMockBuilder('Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();

    $this->statusService = new StatusService($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking);
  }

  /**
   * Creates an array of tracking entities for testing.
   */
  private function getTrackingLogs() {
    return [
      '0000-0000-0000-0000' => '2017-10-10T13:00:00Z',
      '1111-1111-1111-1111' => '2017-10-11T14:00:00Z',
      '2222-2222-2222-2222' => '2017-10-12T15:00:00Z',
    ];
  }

  /**
   * Creates an array of logs for testing.
   */
  private function getContentHubLogs() {
    $logs = [
      'hits' => [
        'hits' => [
          [
            '_id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
            '_index' => 'nickampdemo_history_v1',
            '_score' => '',
            '_source' =>
              [
                'client' => '2d924da7-ad4d-4a1c-6909-783cf42cb360',
                'entity' => '0000-0000-0000-0000',
                'id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
                'message' => '',
                'request_id' => '8ae8f12d-4a0c-42a5-7a05-29787e533102',
                'status' => 'succeeded',
                'subscription' => 'NICKAMPDEMO',
                'timestamp' => '2017-10-10T13:10:00Z',
                'type' => 'update',
              ],

            '_type' => 'history',
            'sort' => [
              '0' => 1508421219000,
            ],
          ],
          [
            '_id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
            '_index' => 'nickampdemo_history_v1',
            '_score' => '',
            '_source' =>
              [
                'client' => '2d924da7-ad4d-4a1c-6909-783cf42cb360',
                'entity' => '1111-1111-1111-1111',
                'id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
                'message' => '',
                'request_id' => '8ae8f12d-4a0c-42a5-7a05-29787e533102',
                'status' => 'succeeded',
                'subscription' => 'NICKAMPDEMO',
                'timestamp' => '2017-10-11T14:40:00Z',
                'type' => 'update',
              ],

            '_type' => 'history',
            'sort' => [
              '0' => 1508421219000,
            ],
          ],
          [
            '_id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
            '_index' => 'nickampdemo_history_v1',
            '_score' => '',
            '_source' =>
              [
                'client' => '2d924da7-ad4d-4a1c-6909-783cf42cb360',
                'entity' => '2222-2222-2222-2222',
                'id' => '76ebe7e4-e67a-4c90-6a2f-81eaee8817a2',
                'message' => '',
                'request_id' => '8ae8f12d-4a0c-42a5-7a05-29787e533102',
                'status' => 'succeeded',
                'subscription' => 'NICKAMPDEMO',
                'timestamp' => '2017-10-12T16:10:00Z',
                'type' => 'update',
              ],

            '_type' => 'history',
            'sort' => [
              '0' => 1508421219000,
            ],
          ],
        ],
      ],
    ];
    return $logs;
  }

  /**
   * Creates array for internal usage from logs.
   *
   * @return array
   *   Array[uuid] = timestamp
   */
  public function getUuidsAndTimestamp($logs) {
    $expected = [];
    $items = $logs['hits']['hits'];
    foreach ($items as $item) {
      $expected[$item['_source']['entity']] = $item['_source']['timestamp'];
    }

    return $expected;
  }

  /**
   * Test for getHistory() method.
   *
   * @covers ::getHistory
   */
  public function testGetHistory() {
    $logs = $this->getContentHubLogs();
    $this->clientManager->method('createRequest')->willReturn($logs);
    $output = $this->statusService->getHistory();
    $expected = $this->getUuidsAndTimestamp($logs);
    $this->assertEquals($expected, $output);
  }

  /**
   * Test for checkImported() method.
   *
   * @covers ::checkImported
   */
  public function testCheckImported() {
    $logs = $this->getContentHubLogs();
    $this->clientManager->method('createRequest')->willReturn($logs);

    $uuids = $this->getTrackingLogs();
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadMultipleByUuid')
      ->willReturn($uuids);

    $output = $this->statusService->checkImported();

    $expected = [
      '0000-0000-0000-0000' => [
        'diff' => 10,
        'local_timestamp' => '2017-10-10T13:00:00Z',
        'remote_timestamp' => '2017-10-10T13:10:00Z',
      ],
      '1111-1111-1111-1111' => [
        'diff' => 40,
        'local_timestamp' => '2017-10-11T14:00:00Z',
        'remote_timestamp' => '2017-10-11T14:40:00Z',
      ],
      '2222-2222-2222-2222' => [
        'diff' => 70,
        'local_timestamp' => '2017-10-12T15:00:00Z',
        'remote_timestamp' => '2017-10-12T16:10:00Z',
      ],
    ];

    $this->assertEquals($expected, $output);
  }

}
