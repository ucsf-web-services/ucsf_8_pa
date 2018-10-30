<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Drupal\acquia_contenthub\ContentHubEntityEmbedHandler;
use Drupal\Tests\UnitTestCase;

/**
 * PHPUnit for the ContentHubEntityEmbedHandler class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\ContentHubEntityEmbedHandler
 *
 * @group acquia_contenthub
 */
class ContentHubEntityEmbedHandlerTest extends UnitTestCase {

  /**
   * Drupal field object.
   *
   * @var \Drupal\Core\Field\FieldItemListInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $field;

  /**
   * Entity Type Manager Interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $container;

  /**
   * The Entity Embed Handler.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntityEmbedHandler
   */
  protected $entityEmbedHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setting up the Module Handler.
    $this->field = $this->getMock('\Drupal\Core\Field\FieldItemListInterface');
    $this->entityTypeManager = $this->getMock('\Drupal\Core\Entity\EntityTypeManagerInterface');

    // Setting up the Container.
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    $this->container
      ->method('get')
      ->willReturnCallback(function ($name) {
        switch ($name) {
          case 'entity_type.manager':
            return $this->entityTypeManager;
        }
        return NULL;
      });
    \Drupal::setContainer($this->container);

    // Initializing Entity Embed Handler.
    $this->entityEmbedHandler = new ContentHubEntityEmbedHandler($this->field);

  }

  /**
   * Test for getReferencedUuids method.
   *
   * @covers ::getReferencedUuids
   */
  public function testGetReferencedUuids() {
    $text = '<drupal-entity data-caption="some_image" data-embed-button="media_browser" data-entity-embed-display="view_mode:media.embedded" data-entity-type="media" data-entity-uuid="00000000-1111-0000-0000-000000000000"></drupal-entity>';
    $uuids = $this->entityEmbedHandler->getReferencedUuids($text);
    $expected = [
      "00000000-1111-0000-0000-000000000000",
    ];
    $this->assertEquals($expected, $uuids);
  }

  /**
   * Test for getReferencedEntities method.
   *
   * @covers ::getReferencedEntities
   */
  public function testGetReferencedEntities() {
    $uuid = "00000000-1111-0000-0000-000000000000";
    $type = "media";
    $entity = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity->method('uuid')->willReturn($uuid);
    $entity_storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $expected = [
      $uuid => $entity,
    ];
    $entity_storage->expects($this->once())->method('loadByProperties')->with(['uuid' => $uuid])->willReturn($expected);
    $this->entityTypeManager->expects($this->once())->method('getStorage')->with($type)->willReturn($entity_storage);
    $text = '<drupal-entity data-caption="some_image" data-embed-button="media_browser" data-entity-embed-display="view_mode:media.embedded" data-entity-type="' . $type . '" data-entity-uuid="' . $uuid . '"></drupal-entity>';
    $this->field->expects($this->once())->method('getString')->willReturn($text);
    $result = $this->entityEmbedHandler->getReferencedEntities();
    $this->assertEquals($expected, $result);
  }

  /**
   * Test for isProcessable method.
   *
   * @covers ::isProcessable
   */
  public function testIsProcessable() {
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->at(0))->method('getType')->willReturn('text_with_summary');
    $field_definition->expects($this->at(1))->method('getType')->willReturn('string_long');
    $field_definition->expects($this->at(2))->method('getType')->willReturn('text_long');
    $field_definition->expects($this->at(3))->method('getType')->willReturn('text');
    $this->field->expects($this->any())->method('getFieldDefinition')->willReturn($field_definition);
    $this->assertTrue($this->entityEmbedHandler->isProcessable());
    $this->assertTrue($this->entityEmbedHandler->isProcessable());
    $this->assertTrue($this->entityEmbedHandler->isProcessable());
    $this->assertFalse($this->entityEmbedHandler->isProcessable());
  }

}
