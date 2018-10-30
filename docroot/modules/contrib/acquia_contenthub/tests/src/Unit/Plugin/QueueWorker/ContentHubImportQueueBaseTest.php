<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Plugin\QueueWorker;

use Drupal\acquia_contenthub\ImportEntityManager;
use Drupal\acquia_contenthub\Plugin\QueueWorker\ContentHubImportQueue;
use Drupal\acquia_contenthub\QueueItem\ImportQueueItem;
use Drupal\Tests\UnitTestCase;

/**
 * Class ContentHubImportQueueBaseTest.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Plugin\QueueWorker\ContentHubImportQueueBase
 *
 * @group acquia_contenthub
 */
class ContentHubImportQueueBaseTest extends UnitTestCase {

  /**
   * Provide QueueItems for the queue process.
   */
  public function provideQueueItems() {
    $data = [];

    $data[] = [
      [new ImportQueueItem('00000000-0000-0000-0000-000000000000')],
      [['00000000-0000-0000-0000-000000000000', TRUE, TRUE, 0]],
    ];

    $data[] = [
      [
        new ImportQueueItem('00000000-0000-0000-0000-000000000000'),
        new ImportQueueItem('00000000-0001-0000-0000-000000000000', FALSE),
        new ImportQueueItem('00000000-0002-0000-0000-000000000000', TRUE, TRUE, 1),
      ],
      [
        ['00000000-0000-0000-0000-000000000000', TRUE, TRUE, 0],
        ['00000000-0001-0000-0000-000000000000', FALSE, TRUE, 0],
        ['00000000-0002-0000-0000-000000000000', TRUE, TRUE, 1],
      ],
    ];

    $data[] = [
      (object) [
        'data' => [
          new ImportQueueItem('00000000-0000-0000-0000-000000000000'),
        ],
      ],
      [['00000000-0000-0000-0000-000000000000', TRUE, TRUE, 0]],
    ];

    return $data;
  }

  /**
   * Ensure that the queue can process given entities.
   *
   * @param mixed $item
   *   Some items to test.
   * @param array $expected
   *   An array of expected items.
   *
   * @dataProvider provideQueueItems
   */
  public function testProcessItem($item = [], array $expected = []) {
    $import_entity_manager = $this->getMockBuilder(ImportEntityManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['importRemoteEntity'])
      ->getMock();

    foreach ($expected as $index => $values) {
      // @codingStandardsIgnoreStart
      /**
       * @var string $uuid
       * @var bool $deps
       * @var bool $author
       * @var int $status
       */
      // @codingStandardsIgnoreEnd
      $values = array_combine(['uuid', 'deps', 'author', 'status'], $values);
      extract($values);
      $import_entity_manager->expects($this->at($index))
        ->method('importRemoteEntity')
        ->with($uuid, $deps, $author, $status);
    }

    $worker = $this->getMockBuilder(ContentHubImportQueue::class)
      ->disableOriginalConstructor()
      ->setMethods(['getEntityManager'])
      ->getMock();

    $worker->expects($this->any())->method('getEntityManager')->willReturn($import_entity_manager);
    $worker->processItem($item);
  }

}
