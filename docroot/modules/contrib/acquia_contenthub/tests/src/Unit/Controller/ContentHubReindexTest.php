<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Controller;

use Drupal\acquia_contenthub\Controller\ContentHubReindex;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\acquia_contenthub\Controller\ContentHubReindex
 *
 * @group acquia_contenthub
 */
class ContentHubReindexTest extends UnitTestCase {

  /**
   * The Reindex State.
   *
   * @var string
   */
  protected $reindexState;

  /**
   * Number of Entities to Reindex.
   *
   * @var int
   */
  protected $numEntities;

  /**
   * Drupal State.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The Translation Interface.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationInterface;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Entity Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager
   */
  protected $clientManager;

  /**
   * Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * The Content Hub Entities Tracking Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  protected $contentHubEntitiesTracking;

  /**
   * The Content Hub Reindex Service.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubReindex
   */
  protected $contentHubReindex;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->state = $this->getMock('Drupal\Core\State\StateInterface');
    $this->state
      ->method('get')
      ->willReturnCallback(function ($name, $value) {
        if ($name == ContentHubReindex::REINDEXING_STATE) {
          if (!empty($this->reindexState)) {
            return $this->reindexState;
          }
          return $value;
        }
        return NULL;
      });
    $this->state
      ->method('set')
      ->willReturnCallback(function ($name, $value) {
        if ($name === ContentHubReindex::REINDEXING_STATE) {
          $this->reindexState = $value;
        }
        else {
          $this->reindexState = ContentHubReindex::REINDEX_NONE;
        }
        return $this->reindexState;
      });

    $this->translationInterface = $this->getMock('Drupal\Core\StringTranslation\TranslationInterface');
    $this->entityManager = $this->getMockBuilder('Drupal\acquia_contenthub\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();

    // Setting up Entity Type Manager.
    $this->entityTypeManager = $this->getMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->method('getStorage')->willReturn($this->entityStorage);

    // Setting up the Container.
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    $this->container
      ->method('get')
      ->willReturnCallback(function ($name) {
        switch ($name) {
          case 'state':
            return $this->state;

          case 'acquia_contenthub.acquia_contenthub_reindex':
            return $this->contentHubReindex;

          case 'acquia_contenthub.entity_manager':
            return $this->entityManager;

          case 'entity_type.manager':
            return $this->entityTypeManager;

          case 'string_translation':
            return $this->translationInterface;

        }
        return NULL;
      });
    \Drupal::setContainer($this->container);

    $this->clientManager = $this->getMock('\Drupal\acquia_contenthub\Client\ClientManagerInterface');
    $this->contentHubEntitiesTracking = $this->getMockBuilder('Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();

    // Defining a certain number of entities to be reindexed.
    $this->numEntities = 5;
    $this->contentHubEntitiesTracking->method('getCountEntitiesToReindex')->willReturn($this->numEntities);

    // Creating the Test Object.
    $this->contentHubReindex = new ContentHubReindex($this->contentHubEntitiesTracking, $this->clientManager);
  }

  /**
   * Obtains entities sample data.
   *
   * @return array
   *   An array of entities.
   */
  private function getEntitiesData() {
    return [
      (object) [
        'entity_uuid' => '00000000-0000-0000-0000-000000000000',
        'entity_type' => 'node',
        'entity_id' => 1,
      ],
      (object) [
        'entity_uuid' => '00000000-0000-0000-1111-000000000000',
        'entity_type' => 'node',
        'entity_id' => 2,
      ],
      (object) [
        'entity_uuid' => '00000000-0000-0000-2222-000000000000',
        'entity_type' => 'node',
        'entity_id' => 3,
      ],
      (object) [
        'entity_uuid' => '00000000-0000-0000-3333-000000000000',
        'entity_type' => 'node',
        'entity_id' => 4,
      ],
      (object) [
        'entity_uuid' => '00000000-0000-0000-4444-000000000000',
        'entity_type' => 'node',
        'entity_id' => 5,
      ],
    ];
  }

  /**
   * Test the isReindexNone function.
   *
   * @covers ::isReindexNone
   */
  public function testIsReindexNone() {
    $this->reindexState = ContentHubReindex::REINDEX_NONE;
    $this->assertTrue($this->contentHubReindex->isReindexNone());
  }

  /**
   * Test the setReindexStateNone function.
   *
   * @covers ::setReindexStateNone
   */
  public function testSetReindexStateNone() {
    $this->reindexState = ContentHubReindex::REINDEX_FINISHED;
    $this->contentHubReindex->setReindexStateNone();
    $this->assertTrue($this->contentHubReindex->isReindexNone());
  }

  /**
   * Test the isReindexSent function.
   *
   * @covers ::isReindexSent
   */
  public function testIsReindexSent() {
    $this->reindexState = ContentHubReindex::REINDEX_SENT;
    $this->assertTrue($this->contentHubReindex->isReindexSent());
  }

  /**
   * Test the setReindexStateSent function.
   *
   * @covers ::setReindexStateSent
   */
  public function testSetReindexStateSent() {
    $this->reindexState = ContentHubReindex::REINDEX_NONE;
    $this->contentHubReindex->setReindexStateSent();
    $this->assertTrue($this->contentHubReindex->isReindexSent());
  }

  /**
   * Test the isReindexFailed function.
   *
   * @covers ::isReindexFailed
   */
  public function testIsReindexFailed() {
    $this->reindexState = ContentHubReindex::REINDEX_FAILED;
    $this->assertTrue($this->contentHubReindex->isReindexFailed());
  }

  /**
   * Test the setReindexStateFailed function.
   *
   * @covers ::setReindexStateFailed
   */
  public function testSetReindexStateFailed() {
    $this->reindexState = ContentHubReindex::REINDEX_NONE;
    $this->contentHubReindex->setReindexStateFailed();
    $this->assertTrue($this->contentHubReindex->isReindexFailed());
  }

  /**
   * Test the isReindexFinished function.
   *
   * @covers ::isReindexFinished
   */
  public function testIsReindexFinished() {
    $this->reindexState = ContentHubReindex::REINDEX_FINISHED;
    $this->assertTrue($this->contentHubReindex->isReindexFinished());
  }

  /**
   * Test the setReindexStateFinished function.
   *
   * @covers ::setReindexStateFinished
   */
  public function testSetReindexStateFinished() {
    $this->reindexState = ContentHubReindex::REINDEX_NONE;
    $this->contentHubReindex->setReindexStateFinished();
    $this->assertTrue($this->contentHubReindex->isReindexFinished());
  }

  /**
   * Test the getCountReExportEntities function.
   *
   * @covers ::getCountReExportEntities
   */
  public function testGetCountReExportEntities() {
    $entities = $this->contentHubReindex->getCountReExportEntities();
    $this->assertEquals($this->numEntities, $entities);
  }

  /**
   * Test the getReExportEntities function.
   *
   * @covers ::getReExportEntities
   */
  public function testGetReExportEntities() {
    $entities = $this->getEntitiesData();
    $offset = 1;
    $limit = 3;
    $this->contentHubEntitiesTracking->expects($this->once())->method('getEntitiesToReindex')->willReturn($entities);
    $result = $this->contentHubReindex->getReExportEntities($offset, $limit);
    $this->assertEquals(array_slice($entities, $offset, $limit), $result);
  }

  /**
   * Test the setExportedEntitiesToReindex function.
   *
   * @covers ::setExportedEntitiesToReindex
   */
  public function testSetExportedEntitiesToReindex() {
    $entities = $this->getEntitiesData();
    $this->contentHubEntitiesTracking->expects($this->once())->method('setExportedEntitiesForReindex')->with('node')->willReturn(TRUE);
    $this->contentHubEntitiesTracking->expects($this->once())->method('getEntitiesToReindex')->willReturn($entities);
    foreach ($entities as $key => $entity) {
      $this->clientManager->expects($this->at($key))
        ->method('createRequest')
        ->with('deleteEntity', [$entity->entity_uuid])
        ->willReturn(TRUE);
    }
    $this->clientManager->expects($this->at(count($entities)))
      ->method('createRequest')
      ->with('reindex')
      ->willReturn(['success' => TRUE]);

    // Initially the system is not set to reindex.
    $this->assertTrue($this->contentHubReindex->isReindexNone());
    $this->contentHubReindex->setExportedEntitiesToReindex('node');
    $this->assertTrue($this->contentHubReindex->isReindexSent());
  }

  /**
   * Test the reExportEntities function.
   *
   * @covers ::reExportEntities
   */
  public function testReExportEntities() {
    $limit = 3;
    $entities_list = $this->getEntitiesData();
    $this->contentHubEntitiesTracking->expects($this->once())->method('getEntitiesToReindex')->willReturn($entities_list);
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    foreach ($entities_list as $key => $entity_data) {
      if ($key < $limit) {
        $this->entityStorage->expects($this->at($key))
          ->method('load')
          ->with($entity_data->entity_id)
          ->willReturn($entity);
        $this->entityManager->expects($this->at($key))
          ->method('enqueueCandidateEntity')
          ->with($entity)
          ->willReturn(NULL);
      }
    }

    $context = [];
    ContentHubReindex::reExportEntities($limit, $context);
    $expected_context = [
      'sandbox' => [
        'progress' => 3,
        'max' => 5,
      ],
      'results' => [
        0 => 1,
        1 => 2,
        2 => 3,
      ],
      'message' => 'Exporting entities: @entities',
    ];
    $this->assertEquals($expected_context['sandbox'], $context['sandbox']);
    $this->assertEquals($expected_context['results'], $context['results']);
    $this->assertEquals($expected_context['message'], $context['message']->getUntranslatedString());
  }

  /**
   * Test the getExportedEntitiesNotOwnedByThisSite function.
   *
   * @covers ::getExportedEntitiesNotOwnedByThisSite
   */
  public function testGetExportedEntitiesNotOwnedByThisSite() {
    $origin = '00000000-0000-0000-0000-000000000000';
    $entity_type_id = 'media';
    $this->contentHubEntitiesTracking->expects($this->once())->method('getSiteOrigin')->willReturn($origin);
    // Some entities do not belong to this origin.
    $entities = [
      'success' => TRUE,
      'data' => [
        // Entities owned by this site origin.
        [
          'uuid' => '00000000-0000-0000-1111-000000000000',
          'origin' => $origin,
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
        [
          'uuid' => '00000000-0000-0000-2222-000000000000',
          'origin' => $origin,
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
        [
          'uuid' => '00000000-0000-0000-3333-000000000000',
          'origin' => $origin,
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
        // Entities that do not belong to this site origin.
        [
          'uuid' => '00000000-0000-0000-4444-000000000000',
          'origin' => '11111111-0000-0000-0000-000000000000',
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
        [
          'uuid' => '00000000-0000-0000-5555-000000000000',
          'origin' => '22222222-0000-0000-0000-000000000000',
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
        [
          'uuid' => '00000000-0000-0000-6666-000000000000',
          'origin' => '33333333-0000-0000-0000-000000000000',
          'modified' => '2017-09-07T22:45:41+00:00',
          'type' => $entity_type_id,
        ],
      ],
    ];
    $options = ['type' => $entity_type_id];
    $this->clientManager->expects($this->once())->method('createRequest')->with('listEntities', [$options])->willReturn($entities);
    $external_ownership = $this->contentHubReindex->getExportedEntitiesNotOwnedByThisSite($entity_type_id);
    $expected_output = array_slice($entities['data'], 3, 3);
    $this->assertEquals($expected_output, $external_ownership);
  }

}
