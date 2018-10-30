<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\ImportEntityManager;
use Drupal\acquia_contenthub\QueueItem\ImportQueueItem;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\NodeInterface;

require_once __DIR__ . '/Polyfill/Drupal.php';

/**
 * PHPUnit test for the ImportEntityManager class.
 *
 * @coversDefaultClass Drupal\acquia_contenthub\ImportEntityManager
 *
 * @group acquia_contenthub
 */
class ImportEntityManagerTest extends UnitTestCase {

  use ContentHubEntityTrait;

  /**
   * The Content Hub Entities Tracking Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contentHubEntitiesTracking;

  /**
   * Diff module's entity comparison service.
   *
   * @var \Drupal\diff\DiffEntityComparison|\PHPUnit_Framework_MockObject_MockObject
   */
  private $diffEntityComparison;

  /**
   * Import entity manager.
   *
   * @var \Drupal\acquia_contenthub\ImportEntityManager
   */
  private $importEntityManager;

  /**
   * The Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  private $entityManager;

  /**
   * The Queue Factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queueFactory;

  /**
   * The Import Queue.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $importQueue;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->database = $this->getMockBuilder('Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();
    $this->loggerFactory = $this->getMockBuilder('Drupal\Core\Logger\LoggerChannelFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->serializer = $this->getMock('\Symfony\Component\Serializer\SerializerInterface');
    $this->entityRepository = $this->getMock('\Drupal\Core\Entity\EntityRepositoryInterface');
    $this->clientManager = $this->getMock('\Drupal\acquia_contenthub\Client\ClientManagerInterface');
    $this->contentHubEntitiesTracking = $this->getMockBuilder('Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();
    $this->diffEntityComparison = $this->getMockBuilder('Drupal\diff\DiffEntityComparison')
      ->disableOriginalConstructor()
      ->getMock();
    $this->entityManager = $this->getMockBuilder('Drupal\acquia_contenthub\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();
    $this->translation_manager = $this->getMockBuilder('Drupal\Core\StringTranslation\TranslationInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->importQueue = $this->getMock('\Drupal\Core\Queue\QueueInterface');
    $this->queueFactory = $this->getMockBuilder('Drupal\Core\Queue\QueueFactory')
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();
    $this->queueFactory->method('get')->with('acquia_contenthub_import_queue')->willReturn($this->importQueue);

    $this->importEntityManager = new ImportEntityManager(
      $this->database,
      $this->loggerFactory,
      $this->serializer,
      $this->entityRepository,
      $this->clientManager,
      $this->contentHubEntitiesTracking,
      $this->diffEntityComparison,
      $this->entityManager,
      $this->translation_manager,
      $this->queueFactory
    );
  }

  /**
   * Tests the entityUpdate() method, node is during sync.
   *
   * @covers ::entityUpdate
   */
  public function testEntityUpdateNodeIsDuringSync() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->__contenthub_entity_syncing = TRUE;
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('loadImportedByDrupalEntity');

    $this->importEntityManager->entityUpdate($node);
  }

  /**
   * Tests the entityUpdate() method, node is not imported.
   *
   * @covers ::entityUpdate
   */
  public function testEntityUpdateNodeNotImported() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn(NULL);

    $this->importEntityManager->entityUpdate($node);
  }

  /**
   * Tests the entityUpdate() method, node is pending sync.
   *
   * @covers ::entityUpdate
   */
  public function testEntityUpdateNodeIsPendingSync() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);

    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('getUuid');

    $this->importEntityManager->entityUpdate($node);
  }

  /**
   * Tests the entityUpdate() method, node is to be resync'ed.
   *
   * @covers ::entityUpdate
   */
  public function testEntityUpdateNodeToResync() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(TRUE);
    $uuid = '75156e0c-9b3c-48f0-b385-a373d98f8ba7';
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('getUuid')
      ->willReturn($uuid);
    $this->clientManager->expects($this->once())
      ->method('createRequest')
      ->with('readEntity', [$uuid]);

    $this->importEntityManager->entityUpdate($node);
  }

  /**
   * Tests the entityPresave() method, node has no original.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveNodeHasNoOriginal() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('loadImportedByDrupalEntity');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, node is during sync.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveNodeIsDuringSync() {
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;
    $node->__contenthub_entity_syncing = TRUE;
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('loadImportedByDrupalEntity');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, node is not imported.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveNodeNotImported() {
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn(NULL);

    $this->diffEntityComparison->expects($this->never())
      ->method('compareRevisions');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, the entity is dependent.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveEntityIsDependent() {
    $original_paragraph = $this->getMock('\Drupal\paragraphs\ParagraphInterface');
    $parent_paragraph = $this->getMock('\Drupal\paragraphs\ParagraphInterface');
    $paragraph = $this->getMock('\Drupal\paragraphs\ParagraphInterface');
    $paragraph->original = $original_paragraph;
    $paragraph->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('paragraph');
    $paragraph->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $paragraph->expects($this->once())
      ->method('getParentEntity')
      ->willReturn($parent_paragraph);
    $parent_paragraph->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('paragraph');
    $parent_paragraph->expects($this->once())
      ->method('id')
      ->willReturn(13);
    $this->contentHubEntitiesTracking->expects($this->at(0))
      ->method('loadImportedByDrupalEntity')
      ->with('paragraph', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->at(1))
      ->method('isDependent')
      ->willReturn(TRUE);
    $this->contentHubEntitiesTracking->expects($this->at(2))
      ->method('loadImportedByDrupalEntity')
      ->with('paragraph', 13)
      ->willReturn(NULL);

    $this->diffEntityComparison->expects($this->never())
      ->method('compareRevisions');

    $this->importEntityManager->entityPresave($paragraph);
  }

  /**
   * Tests the entityPresave() method, node is pending sync.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveNodeIsPendingSync() {
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(TRUE);

    $this->diffEntityComparison->expects($this->never())
      ->method('compareRevisions');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, node is has local change.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveNodeHasLocalChange() {
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(TRUE);

    $this->diffEntityComparison->expects($this->never())
      ->method('compareRevisions');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, compare, and no setLocalChange.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareNoLocalChange() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;

    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    // Nodes do not have referenced entities.
    $node->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([]);
    $node->original->expects($this->once())
      ->method('referencedEntities')
      ->willReturn([]);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->any())
      ->method('getType')
      ->willReturn('text');

    $node->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $field_comparisons = [
      '12:node.same_field_1' => [
        '#data' => [
          '#left' => 'same_value_1',
          '#right' => 'same_value_1',
        ],
      ],
      '12:node.same_field_2' => [
        '#data' => [
          '#left' => 'same_value_2',
          '#right' => 'same_value_2',
        ],
      ],
    ];

    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('save');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method, compare, and yes setLocalChange.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareYesLocalChange() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;

    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->any())
      ->method('getType')
      ->willReturn('text');

    $node->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $field_comparisons = [
      '12:node.same_field_1' => [
        '#data' => [
          '#left' => 'same_value_1',
          '#right' => 'same_value_1',
        ],
      ],
      '12:node.difference_field_2' => [
        '#data' => [
          '#left' => 'a_value',
          '#right' => 'a_different_value',
        ],
      ],
      '12:node.same_field_2' => [
        '#data' => [
          '#left' => 'same_value_2',
          '#right' => 'same_value_2',
        ],
      ],
    ];

    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('save');

    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the importRemoteEntity() method.
   *
   * @covers ::entityPresave
   */
  public function testImportRemoteEntityMissingEntityWithRequiredBundle() {
    $uuid = '11111111-1111-1111-1111-111111111111';
    $site_origin = '11111111-2222-1111-1111-111111111111';
    $entity_ch = $this->createContentHubEntity([
      'uuid' => $uuid,
    ]);

    $this->contentHubEntitiesTracking->expects($this->any())
      ->method('getSiteOrigin')
      ->willReturn($site_origin);

    $this->clientManager->expects($this->any())
      ->method('createRequest')
      ->with('readEntity', [$uuid])
      ->willReturn($entity_ch);

    $this->entityManager->expects($this->any())
      ->method('getAllowedEntityTypes')
      ->willReturn(['node' => ['test' => 'Test content type']]);

    $loggerChannelInterface = $this->getMock('\Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with('acquia_contenthub')
      ->willReturn($loggerChannelInterface);

    $loggerChannelInterface->expects($this->any())
      ->method('warning');

    $result = $this->importEntityManager->importRemoteEntity($uuid, FALSE);
    $status_code = json_decode($result->getStatusCode());
    $this->assertEquals($status_code, 403);
  }

  /**
   * Tests the entityPresave() method.
   *
   * Include content entities from comparison.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareIncludeFieldReference() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;

    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $node->expects($this->any())
      ->method('referencedEntities')
      ->willReturn([]);
    $node->original->expects($this->any())
      ->method('referencedEntities')
      ->willReturn([]);

    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    $fieldReferenceInterface = $this->getMock('Drupal\Core\Field\EntityReferenceFieldItemListInterface');

    // Get reference entity.
    $referenced_entities = [];
    $entity1 = $this->getMock('\Drupal\Core\Entity\EntityInterface');

    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $entity1->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    $entity_type->expects($this->once())
      ->method('entityClassImplements')
      ->willReturn(FALSE);
    $referenced_entities[] = $entity1;

    $fieldReferenceInterface->expects($this->any())
      ->method('referencedEntities')
      ->willReturn($referenced_entities);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->once())
      ->method('getType')
      ->willReturn('entity_reference');

    $node->expects($this->once())
      ->method('getFieldDefinition')
      ->with('same_field_1')
      ->willReturn($fieldDefinition);

    $node->expects($this->once())
      ->method('get')
      ->with('same_field_1')
      ->willReturn($fieldReferenceInterface);

    $field_comparisons = [
      '12:node.same_field_1' => [
        '#data' => [
          '#left' => 'same_value_1',
          '#right' => 'same_value_2',
        ],
      ],
    ];

    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('save');
    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method.
   *
   * Exclude configuration entities from comparison.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareExcludeFieldReference() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;
    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $node->expects($this->any())
      ->method('referencedEntities')
      ->willReturn([]);
    $node->original->expects($this->any())
      ->method('referencedEntities')
      ->willReturn([]);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    // Get reference entity.
    $referenced_entities = [];
    $entity1 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity_type = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityTypeInterface');
    $entity1->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    $entity_type->expects($this->once())
      ->method('entityClassImplements')
      ->willReturn(TRUE);
    $referenced_entities[] = $entity1;

    // Set reference field.
    $fieldReferenceInterface = $this->getMock('Drupal\Core\Field\EntityReferenceFieldItemListInterface');
    $fieldReferenceInterface->expects($this->any())
      ->method('referencedEntities')
      ->willReturn($referenced_entities);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->at(0))
      ->method('getType')
      ->willReturn('entity_reference');

    $fieldDefinition->expects($this->at(1))
      ->method('getType')
      ->willReturn('text');

    $node->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $node->expects($this->any())
      ->method('get')
      ->with('same_field_1')
      ->willReturn($fieldReferenceInterface);

    $field_comparisons = [
      '12:node.same_field_1' => [
        '#data' => [
          '#left' => 'same_value_1',
          '#right' => 'same_value_2',
        ],
      ],
      '12:node.same_field_2' => [
        '#data' => [
          '#left' => 'same_value_2',
          '#right' => 'same_value_2',
        ],
      ],
    ];
    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('save');
    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method.
   *
   * Exclude configuration entity from comparison.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareExcludeEntityFromComparison() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;

    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->any())
      ->method('getType')
      ->willReturn('text');

    $node->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $field_comparisons = [
      '12:node.same_field_2' => [
        '#data' => [
          '#left' => 'same_value_2',
          '#right' => 'same_value_2',
        ],
      ],
    ];

    $reference = $this->getMock('\Drupal\node\NodeInterface');
    $reference->original = $this->getMock('\Drupal\node\NodeInterface');
    $entity1 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity_type = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityTypeInterface');

    $entity1->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    // Set uuid for entity reference 1.
    $entity1->method('uuid')
      ->willReturn('test-uuid-reference-1');
    $entity_type->expects($this->any())
      ->method('entityClassImplements')
      ->willReturn(TRUE);
    $referenced_entities1[] = $entity1;

    $entity2 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity2->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    // Set uuid for entity reference 2.
    $entity2->method('uuid')
      ->willReturn('test-uuid-reference-2');
    $entity_type->expects($this->any())
      ->method('entityClassImplements')
      ->willReturn(TRUE);
    $referenced_entities2[] = $entity2;

    // Set reference to node.
    $node->expects($this->once())
      ->method('referencedEntities')
      ->willReturn($referenced_entities1);

    // Set reference to origin node.
    $node->original->expects($this->once())
      ->method('referencedEntities')
      ->willReturn($referenced_entities2);

    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('save');
    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Tests the entityPresave() method.
   *
   * Include content entities to comparison.
   *
   * @covers ::entityPresave
   */
  public function testEntityPresaveCompareIncludeEntityFromComparison() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $original_node = $this->getMock('\Drupal\node\NodeInterface');
    $node->original = $original_node;

    $node->expects($this->any())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isPendingSync')
      ->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn(FALSE);

    $fieldDefinition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $fieldDefinition->expects($this->once())
      ->method('getType')
      ->willReturn('text');

    $node->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $field_comparisons = [
      '12:node.same_field_2' => [
        '#data' => [
          '#left' => 'same_value_2',
          '#right' => 'same_value_2',
        ],
      ],
    ];

    $reference = $this->getMock('\Drupal\node\NodeInterface');
    $reference->original = $this->getMock('\Drupal\node\NodeInterface');
    $entity1 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity_type = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityTypeInterface');

    $entity1->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    // Set uuid for entity reference 1.
    $entity1->method('uuid')
      ->willReturn('test-uuid-reference-1');
    $entity_type->expects($this->any())
      ->method('entityClassImplements')
      ->willReturn(FALSE);
    $referenced_entities1[] = $entity1;

    $entity2 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity2->expects($this->any())
      ->method('getEntityType')
      ->will($this->returnValue($entity_type));
    // Set uuid for entity reference 2.
    $entity2->method('uuid')
      ->willReturn('test-uuid-reference-2');
    $entity_type->expects($this->any())
      ->method('entityClassImplements')
      ->willReturn(FALSE);
    $referenced_entities2[] = $entity2;

    // Set reference to node.
    $node->expects($this->once())
      ->method('referencedEntities')
      ->willReturn($referenced_entities1);

    // Set reference to origin node.
    $node->original->expects($this->once())
      ->method('referencedEntities')
      ->willReturn($referenced_entities2);

    $this->diffEntityComparison->expects($this->once())
      ->method('compareRevisions')
      ->with($original_node, $node)
      ->willReturn($field_comparisons);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('setLocalChange');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('save');
    $this->importEntityManager->entityPresave($node);
  }

  /**
   * Provide some UUID values.
   *
   * @return array
   *   A list of UUIDs.
   */
  public function provideEntityUuid() {
    return [
      ['00000000-0000-0000-0000-000000000000'],
      ['00000000-0001-0000-0000-000000000000'],
      ['00000000-0002-0000-0000-000000000000'],
      ['00000000-0003-0000-0000-000000000000'],
    ];
  }

  /**
   * Build a container that has some configuration available.
   *
   * @param array $config
   *   THe container config array.
   */
  public function buildConfigContainer(array $config = []) {
    $container = new ContainerBuilder();
    $config = $this->getConfigFactoryStub($config);
    $container->set('config.factory', $config);
    \Drupal::unsetContainer();
    \Drupal::setContainer($container);
  }

  /**
   * Tests the ability to add an entity to the import queue.
   *
   * @covers ::addEntityToImportQueue
   * @dataProvider provideEntityUuid
   */
  public function testAddEntityToQueue($uuid) {
    $item = (object) ['data' => []];
    $item->data[] = new ImportQueueItem($uuid, TRUE, NULL, 0);
    $this->importQueue->method('createItem')->with($item)->willReturn(TRUE);

    $return = $this->importEntityManager->addEntityToImportQueue($uuid);
    $this->assertEquals(200, $return->getStatusCode());
  }

  /**
   * Ensure that if configuration is set the entity will be added to the queue.
   *
   * @covers ::import
   * @dataProvider provideEntityUuid
   */
  public function testImportWithQueue($uuid = '') {
    $importEntityManager = $this->getMockBuilder(ImportEntityManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['addEntityToImportQueue', 'importRemoteEntity'])
      ->getMock();

    $this->buildConfigContainer(['acquia_contenthub.entity_config' => ['import_with_queue' => TRUE]]);

    $importEntityManager->expects($this->once())
      ->method('addEntityToImportQueue')
      ->with($uuid);

    $importEntityManager->expects($this->never())->method('importRemoteEntity');

    $importEntityManager->import($uuid);
  }

  /**
   * Ensure if configuration is correct entity will be imported immediately.
   *
   * @covers ::import
   * @dataProvider provideEntityUuid
   */
  public function testImportWithoutQueue($uuid = '') {
    $importEntityManager = $this->getMockBuilder(ImportEntityManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['addEntityToImportQueue', 'importRemoteEntity'])
      ->getMock();

    $this->buildConfigContainer(['acquia_contenthub.entity_config' => ['import_with_queue' => FALSE]]);

    $importEntityManager->expects($this->once())
      ->method('importRemoteEntity')
      ->with($uuid);

    $importEntityManager->expects($this->never())->method('addEntityToImportQueue');

    $importEntityManager->import($uuid);

  }

}
