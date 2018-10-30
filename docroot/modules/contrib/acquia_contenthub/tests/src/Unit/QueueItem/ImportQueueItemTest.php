<?php

namespace Drupal\Tests\acquia_contenthub\Unit\QueueItem;

use Drupal\acquia_contenthub\ImportEntityManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\QueueItem\ImportQueueItem;

/**
 * PHPUnit test for the ImportQueueItem.
 *
 * @coversDefaultClass Drupal\acquia_contenthub\QueueItem\ImportQueueItem
 *
 * @group acquia_contenthub
 */
class ImportQueueItemTest extends UnitTestCase {

  /**
   * Ensure an expected UUID can be retrieved from the ImportQueueItem.
   *
   * @param \Drupal\acquia_contenthub\QueueItem\ImportQueueItem $item
   *   A test ImportQueueItem object.
   * @param string $expected
   *   The expected UUID.
   *
   * @dataProvider provideUuidItems
   */
  public function testGetUuid(ImportQueueItem $item, $expected) {
    $this->assertEquals($item->get('uuid'), $expected);
  }

  /**
   * Ensure an expected dependency can be retrieved from the ImportQueueItem.
   *
   * @param \Drupal\acquia_contenthub\QueueItem\ImportQueueItem $item
   *   A test ImportQueueItem object.
   * @param string $expected
   *   The expected dependency status.
   *
   * @dataProvider provideDependencyItems
   */
  public function testDependencies(ImportQueueItem $item, $expected) {
    $this->assertEquals($item->get('dependencies'), $expected);
  }

  /**
   * Ensure an expected author can be retrieved from the ImportQueueItem.
   *
   * @param \Drupal\acquia_contenthub\QueueItem\ImportQueueItem $item
   *   A test ImportQueueItem object.
   * @param string $expected
   *   The expected dependency status.
   *
   * @dataProvider provideAuthorItems
   */
  public function testGetAuthor(ImportQueueItem $item, $expected) {
    // var_dump(\Drupal::config('acquia_contenthub.entity_config')->get('import_with_queue'));.
    $this->assertEquals($item->get('author'), $expected);
  }

  /**
   * Ensure an expected status can be retrieved from the ImportQueueItem.
   *
   * @param \Drupal\acquia_contenthub\QueueItem\ImportQueueItem $item
   *   A test ImportQueueItem object.
   * @param string $expected
   *   The expected dependency status.
   *
   * @dataProvider provideStatusItems
   */
  public function testGetStatus(ImportQueueItem $item, $expected) {
    $this->assertEquals($item->get('status'), $expected);
  }

  /**
   * Test Import with Queue.
   */
  public function testImportWithQueue() {
    $importEntityManager = $this->getMockBuilder(ImportEntityManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['addEntityToImportQueue'])
      ->getMock();

    $container = new ContainerBuilder();
    $config = $this->getConfigFactoryStub(['acquia_contenthub.entity_config' => ['import_with_queue' => TRUE]]);
    $container->set('config.factory', $config);

    $container->set('config.factory', $config);
    \Drupal::unsetContainer();
    \Drupal::setContainer($container);

    $import_with_queue = \Drupal::config('acquia_contenthub.entity_config')->get('import_with_queue');
    $this->assertTrue($import_with_queue);
  }

  /**
   * Provides a series of queue items.
   *
   * @return array
   *   An array of queue items.
   */
  public function provideUuidItems() {
    $data = [];
    $data[] = [new ImportQueueItem('00000000-0000-0000-0000-000000000000'), '00000000-0000-0000-0000-000000000000'];
    $data[] = [new ImportQueueItem('00000000-0001-0000-0000-000000000000'), '00000000-0001-0000-0000-000000000000'];
    $data[] = [new ImportQueueItem('00000000-0002-0000-0000-000000000000'), '00000000-0002-0000-0000-000000000000'];
    $data[] = [new ImportQueueItem('00000000-0003-0000-0000-000000000000'), '00000000-0003-0000-0000-000000000000'];

    return $data;
  }

  /**
   * Provides a series of queue items with dependencies.
   *
   * @return array
   *   An array with queue items with dependencies.
   */
  public function provideDependencyItems() {
    $data = [];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', TRUE),
      TRUE,
    ];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', FALSE),
      FALSE,
    ];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000'),
      TRUE,
    ];

    return $data;
  }

  /**
   * Provides a series of queue items with dependencies.
   *
   * @return array
   *   A series of queue items with users.
   */
  public function provideAuthorItems() {
    $this->createContainer();
    $data = [];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', TRUE, TRUE),
      TRUE,
    ];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', FALSE, \Drupal::currentUser()),
      \Drupal::currentUser(),
    ];

    return $data;
  }

  /**
   * Provides a series of queue items with dependencies.
   *
   * @return array
   *   An array of queue items with other data.
   */
  public function provideStatusItems() {
    $data = [];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', TRUE, TRUE, 0),
      0,
    ];
    $data[] = [
      new ImportQueueItem('00000000-0000-0000-0000-000000000000', TRUE, TRUE, 1),
      1,
    ];

    return $data;
  }

  /**
   * Build a container before the class.
   */
  public function createContainer() {
    parent::setup();
    \Drupal::unsetContainer();

    $container = new ContainerBuilder();

    $account = $this->getMockBuilder('Drupal\Core\Session\AccountProxyInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('current_user', $account);

    \Drupal::setContainer($container);
  }

}
