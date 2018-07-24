<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Drupal\acquia_contenthub\ContentHubEntityDependency;
use Drupal\Tests\UnitTestCase;
use Acquia\ContentHubClient\Attribute;

/**
 * PHPUnit for the ContentHubEntityDependency class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\ContentHubEntityDependency
 *
 * @group acquia_contenthub
 */
class ContentHubEntityDependencyTest extends UnitTestCase {

  use ContentHubEntityTrait;

  /**
   * Returns the module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $container;

  /**
   * A Content Hub Entity.
   *
   * @var \Acquia\ContentHubClient\Entity|\PHPUnit_Framework_MockObject_MockObject
   */
  private $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setting up the Module Handler.
    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    // Setting up the Container.
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    $this->container
      ->method('get')
      ->willReturnCallback(function ($name) {
        switch ($name) {
          case 'module_handler':
            return $this->moduleHandler;
        }
        return NULL;
      });
    \Drupal::setContainer($this->container);
  }

  /**
   * Test for getUuid() and getEntityType() method.
   *
   * @covers ::getUuid
   * @covers ::getEntityType
   */
  public function testGetUuidAndType() {
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $this->assertEquals($this->entity->getUuid(), $ch_entity_dependency->getUuid());
    $this->assertEquals('node', $ch_entity_dependency->getEntityType());
  }

  /**
   * Test for isEntityDependent() method.
   *
   * @covers ::isEntityDependent
   */
  public function testIsEntityDependent() {
    // A 'node' entity is an 'independent' entity.
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $this->assertFalse($ch_entity_dependency->isEntityDependent());

    // A 'paragraph' entity is a 'dependent' entity.
    $values = [
      'type' => 'paragraph',
    ];
    $this->entity = $this->createContentHubEntity($values);
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $this->assertTrue($ch_entity_dependency->isEntityDependent());
  }

  /**
   * Test for getRelationship() method.
   *
   * @covers ::getRelationship
   */
  public function testGetRelationship() {
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $this->assertEquals(ContentHubEntityDependency::RELATIONSHIP_INDEPENDENT, $ch_entity_dependency->getRelationship());
  }

  /**
   * Test Dependency Chain.
   *
   * @covers ::appendDependencyChain
   * @covers ::isInDependencyChain
   * @covers ::getDependencyChain
   */
  public function testDependencyChain() {
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);

    // Adding dependency.
    $entity1 = $this->createContentHubEntity([
      'uuid' => '00000000-2222-0000-0000-000000000000',
    ]);
    $ch_entity_dependency1 = new ContentHubEntityDependency($entity1);
    $ch_entity_dependency->appendDependencyChain($ch_entity_dependency1);
    $expected_dependency_chain = [];
    $expected_dependency_chain[] = $entity1->getUuid();
    $this->assertEquals($expected_dependency_chain, $ch_entity_dependency->getDependencyChain());

    // Adding another dependency.
    $entity2 = $this->createContentHubEntity([
      'uuid' => '00000000-3333-0000-0000-000000000000',
    ]);
    $ch_entity_dependency2 = new ContentHubEntityDependency($entity2);
    $ch_entity_dependency->appendDependencyChain($ch_entity_dependency2);
    $expected_dependency_chain[] = $entity2->getUuid();
    $this->assertEquals($expected_dependency_chain, $ch_entity_dependency->getDependencyChain());

    // $entity1 and $entity2 should be in the dependency chain.
    $this->assertTrue($ch_entity_dependency->isInDependencyChain($ch_entity_dependency1));
    $this->assertTrue($ch_entity_dependency->isInDependencyChain($ch_entity_dependency2));

    // This entity should not be in the dependency chain.
    $entity3 = $this->createContentHubEntity([
      'uuid' => '00000000-4444-0000-0000-000000000000',
    ]);
    $ch_entity_dependency3 = new ContentHubEntityDependency($entity3);
    $this->assertFalse($ch_entity_dependency->isInDependencyChain($ch_entity_dependency3));
  }

  /**
   * Test Dependency Chain.
   *
   * @covers ::setParent
   * @covers ::getParent
   */
  public function testParenthood() {
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);

    // Adding a parent.
    $entity1 = $this->createContentHubEntity([
      'uuid' => '00000000-2222-0000-0000-000000000000',
    ]);
    $ch_entity_dependency1 = new ContentHubEntityDependency($entity1);
    $ch_entity_dependency->setParent($ch_entity_dependency1);
    $this->assertEquals($ch_entity_dependency1, $ch_entity_dependency->getParent());
  }

  /**
   * Test getRawEntity method.
   *
   * @covers ::getRawEntity
   */
  public function testGetRawEntity() {
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $this->assertEquals($this->entity, $ch_entity_dependency->getRawEntity());
  }

  /**
   * Test setAuthor method.
   *
   * @covers ::setAuthor
   */
  public function testSetAuthor() {
    $author = '00000000-9999-0000-0000-000000000000';
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $ch_entity_dependency->setAuthor($author);
    $cdf = $ch_entity_dependency->getRawEntity();
    $expected = [
      'type' => Attribute::TYPE_REFERENCE,
      'value' => [
        'en' => $author,
      ],
    ];
    $this->assertEquals($expected, $cdf['attributes']['author']);
  }

  /**
   * Test setStatus method.
   *
   * @covers ::setStatus
   */
  public function testSetStatus() {
    $status = 1;
    $this->entity = $this->createContentHubEntity();
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $ch_entity_dependency->setStatus($status);
    $cdf = $ch_entity_dependency->getRawEntity();
    $expected = [
      'type' => Attribute::TYPE_INTEGER,
      'value' => [
        'en' => $status,
      ],
    ];
    $this->assertEquals($expected, $cdf['attributes']['status']);
  }

  /**
   * Test getRemoteDependencies method.
   *
   * @covers ::getRemoteDependencies
   */
  public function testGetRemoteDependencies() {
    $this->moduleHandler->expects($this->at(0))->method('moduleExists')->with('entity_embed')->willReturn(TRUE);
    $values['attributes']['field_reference1'] = [
      'type' => Attribute::TYPE_REFERENCE,
      'value' => [
        'en' => '00000000-2222-0000-0000-000000000000',
      ],
    ];
    $values['attributes']['field_reference2'] = [
      'type' => Attribute::TYPE_ARRAY_REFERENCE,
      'value' => [
        'en' => [
          '00000000-3333-0000-0000-000000000000',
          '00000000-4444-0000-0000-000000000000',
          '00000000-5555-0000-0000-000000000000',
        ],
      ],
    ];
    // Author should be excluded from dependency calculation.
    $values['attributes']['author'] = [
      'type' => Attribute::TYPE_REFERENCE,
      'value' => [
        'en' => '00000000-6666-0000-0000-000000000000',
      ],
    ];

    // Embedded entities should be included in the dependencies.
    $body_value = [
      'value' => '<drupal-entity data-caption="some_image" data-embed-button="media_browser" data-entity-embed-display="view_mode:media.embedded" data-entity-type="media" data-entity-uuid="00000000-7777-0000-0000-000000000000"></drupal-entity>',
      'summary' => '',
      'format' => 'rich_text',
    ];
    $values['attributes']['body'] = [
      'type' => Attribute::TYPE_ARRAY_STRING,
      'value' => [
        'en' => [
          json_encode($body_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
        ],
      ],
    ];

    $this->entity = $this->createContentHubEntity($values);
    $ch_entity_dependency = new ContentHubEntityDependency($this->entity);
    $dependencies = $ch_entity_dependency->getRemoteDependencies();
    $expected = [
      '00000000-2222-0000-0000-000000000000',
      '00000000-3333-0000-0000-000000000000',
      '00000000-4444-0000-0000-000000000000',
      '00000000-5555-0000-0000-000000000000',
      '00000000-7777-0000-0000-000000000000',
    ];
    $this->assertEquals($expected, $dependencies);
  }

}
