<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Controller;

use Drupal\acquia_contenthub\Controller\ContentHubExportQueueController;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Session\AccountInterface;

/**
 * PHPUnit test for the ContentHubExportQueueController class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
 *
 * @group acquia_contenthub
 */
class ContentHubExportQueueControllerTest extends UnitTestCase {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The mock config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Queue Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $queueFactory;

  /**
   * The Queue Manager.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManager
   */
  protected $queueManager;

  /**
   * The Content Hub Export Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  protected $contentHubExportQueueController;

  /**
   * The acquia_contenthub.entity_config array.
   *
   * @var array
   */
  protected $configEntity = [
    'dependency_depth'               => 3,
    'user_role'                      => AccountInterface::ANONYMOUS_ROLE,
    'export_queue_entities_per_item' => 1,
    'export_queue_batch_size'        => 1,
    'export_queue_waiting_time'      => 5,
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($this->container);
    $this->configFactory = $this->getMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->configFactory
      ->method('get')
      ->with('acquia_contenthub.entity_config')
      ->willReturn($this->createMockForContentHubEntityConfig());
    $this->queueFactory = $this->getMockBuilder('Drupal\Core\Queue\QueueFactory')
      ->disableOriginalConstructor()
      ->getMock();
    $this->queueManager = $this->getMockBuilder('Drupal\Core\Queue\QueueWorkerManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentHubExportQueueController = new ContentHubExportQueueController($this->queueFactory, $this->queueManager, $this->configFactory);
  }

  /**
   * Test the getQueueCount method.
   *
   * @covers ::getQueueCount
   */
  public function testGetQueueCount() {
    $queue = $this->getMock('Drupal\Core\Queue\QueueInterface');
    $queue->method('numberOfItems')->willReturn(100);
    $this->queueFactory->method('get')->with('acquia_contenthub_export_queue')->willReturn($queue);
    $count = $this->contentHubExportQueueController->getQueueCount();
    $this->assertEquals($count, 100);
  }

  /**
   * Test the getWaitingTime method.
   *
   * @covers ::getWaitingTime
   */
  public function testGetWaitingTime() {
    $waiting_time = $this->contentHubExportQueueController->getWaitingTime();
    $this->assertEquals($waiting_time, 5);
  }

  /**
   * Test the enqueueExportEntities method.
   *
   * @covers ::enqueueExportEntities
   */
  public function testEnqueueExportEntities() {
    $queued_items = [
      $this->createQueueItem('node', 1, '00000000-0000-0000-0000-000000000000'),
      $this->createQueueItem('node', 2, '00000000-0000-1111-0000-000000000000'),
      $this->createQueueItem('node', 3, '00000000-0000-2222-0000-000000000000'),
      $this->createQueueItem('node', 4, '00000000-0000-3333-0000-000000000000'),
      $this->createQueueItem('node', 5, '00000000-0000-4444-0000-000000000000'),
      $this->createQueueItem('node', 6, '00000000-0000-5555-0000-000000000000'),
      $this->createQueueItem('node', 7, '00000000-0000-6666-0000-000000000000'),
    ];
    // Defining the queue.
    $queue = $this->getMock('Drupal\Core\Queue\QueueInterface');
    $queue->method('numberOfItems')->willReturn(100);
    $queue->expects($this->at(0))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[0];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(1))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[1];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(2))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[2];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(3))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[3];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(4))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[4];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(5))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[5];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $queue->expects($this->at(6))->method('createItem')->willReturnCallback(function ($item) use ($queued_items) {
      $item = (array) $item;
      $queued_item = (array) $queued_items[6];
      $this->assertEquals($item, $queued_item);
      return ($item == $queued_item) ? 1 : FALSE;
    });
    $this->queueFactory->method('get')->with('acquia_contenthub_export_queue')->willReturn($queue);

    // Defining candidate entities.
    $candidate_entities = [
      '00000000-0000-0000-0000-000000000000' => $this->createMockForContentEntity('node', 1, '00000000-0000-0000-0000-000000000000'),
      '00000000-0000-1111-0000-000000000000' => $this->createMockForContentEntity('node', 2, '00000000-0000-1111-0000-000000000000'),
      '00000000-0000-2222-0000-000000000000' => $this->createMockForContentEntity('node', 3, '00000000-0000-2222-0000-000000000000'),
      '00000000-0000-3333-0000-000000000000' => $this->createMockForContentEntity('node', 4, '00000000-0000-3333-0000-000000000000'),
      '00000000-0000-4444-0000-000000000000' => $this->createMockForContentEntity('node', 5, '00000000-0000-4444-0000-000000000000'),
      '00000000-0000-5555-0000-000000000000' => $this->createMockForContentEntity('node', 6, '00000000-0000-5555-0000-000000000000'),
      '00000000-0000-6666-0000-000000000000' => $this->createMockForContentEntity('node', 7, '00000000-0000-6666-0000-000000000000'),
    ];

    // Enqueue entities.
    $this->contentHubExportQueueController->enqueueExportEntities($candidate_entities);
  }

  /**
   * Returns a fake ContentHubEntityConfig object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The fake config.
   */
  protected function createMockForContentHubEntityConfig() {
    $contenthub_entity_config = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();
    $contenthub_entity_config
      ->method('get')
      ->willReturnCallback(function ($argument) {
        if (isset($this->configEntity[$argument])) {
          return $this->configEntity[$argument];
        }
        return NULL;
      });

    return $contenthub_entity_config;
  }

  /**
   * Creates a mock content entity.
   *
   * @param string $entity_type_id
   *   The Entity type ID.
   * @param string $id
   *   The Entity ID.
   * @param string $uuid
   *   The Entity UUID.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The fake ContentEntity.
   */
  protected function createMockForContentEntity($entity_type_id, $id, $uuid) {
    $enabled_methods = [
      'getEntityTypeId',
      'id',
      'uuid',
      'getFields',
    ];
    $content_entity_mock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityBase')
      ->disableOriginalConstructor()
      ->setMethods($enabled_methods)
      ->getMockForAbstractClass();
    $content_entity_mock->method('getEntityTypeId')->willReturn($entity_type_id);
    $content_entity_mock->method('id')->willReturn($id);
    $content_entity_mock->method('uuid')->willReturn($uuid);

    // No fields. We are just testing we can add item to the queue.
    $content_entity_mock->method('getFields')->willReturn([]);
    return $content_entity_mock;
  }

  /**
   * Creates a queue item object.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $entity_id
   *   The entity ID.
   * @param string $entity_uuid
   *   The entity UUID.
   *
   * @return object
   *   The queue item.
   */
  protected function createQueueItem($entity_type, $entity_id, $entity_uuid) {
    return (object) [
      'data' => [
        0 => [
          'entity_type' => $entity_type,
          'entity_id' => $entity_id,
          'entity_uuid' => $entity_uuid,
        ],
      ],
    ];
  }

}
