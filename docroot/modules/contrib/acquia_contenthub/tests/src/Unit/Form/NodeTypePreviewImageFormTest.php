<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Form;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\Form\NodeTypePreviewImageForm;

require_once __DIR__ . '/../Polyfill/Drupal.php';

/**
 * PHPUnit test for the NodeTypePreviewImageForm class.
 *
 * @coversDefaultClass Drupal\acquia_contenthub\Form\NodeTypePreviewImageForm
 *
 * @group acquia_contenthub
 */
class NodeTypePreviewImageFormTest extends UnitTestCase {

  /**
   * Entity manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contenthubEntityManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $entityTypeManager;

  /**
   * Entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
   */
  private $entityTypeRepository;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $entityFieldManager;

  /**
   * Content Hub Entity Config.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contenthubEntityConfig;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->contenthubEntityManager = $this->getMockBuilder('Drupal\acquia_contenthub\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();
    $this->entityTypeManager = $this->getMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->entityFieldManager = $this->getMock('Drupal\Core\Entity\EntityFieldManagerInterface');
    $this->contenthubEntityConfig = $this->getMock('Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface');

    $this->contenthubEntityManager->expects($this->once())
      ->method('getContentHubEntityTypeConfigurationEntity')
      ->willReturn($this->contenthubEntityConfig);
  }

  /**
   * Tests the getForm() method, no field for this entity.
   *
   * @covers ::getForm
   */
  public function testGetFormNoField() {
    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn([]);
    $preview_image_form->setStringTranslation($this->getStringTranslationStub());

    $form = $preview_image_form->getForm('article');
    $this->assertRegexp('/no image field/', $form['no_image_field']['#markup']);
  }

  /**
   * Tests the getForm() method, no style.
   *
   * @covers ::getForm
   */
  public function testGetFormWithNoStyle() {
    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $preview_image_form->setStringTranslation($this->getStringTranslationStub());

    $field_definitions = [
      'field_description' => $this->getFieldDefinition('description'),
      'field_image' => $this->getFieldDefinition('image'),
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn($field_definitions);

    acquia_polyfill_controller_set_return('image_style_options', []);

    $form = $preview_image_form->getForm('article');

    $this->assertEquals(1, count($form['style']['#options']));
    $this->assertEquals('Acquia Content Hub Preview Image (150×150)', $form['style']['#options']['acquia_contenthub_preview_image_add']);
  }

  /**
   * Tests the getForm() method, already with default style.
   *
   * @covers ::getForm
   */
  public function testGetFormWithDefaultStyle() {
    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $preview_image_form->setStringTranslation($this->getStringTranslationStub());

    $field_definitions = [
      'field_description' => $this->getFieldDefinition('description'),
      'field_image' => $this->getFieldDefinition('image'),
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn($field_definitions);

    $image_style_options_return_value = [
      'medium' => 'Medium',
      NodeTypePreviewImageForm::PREVIEW_IMAGE_DEFAULT_KEY => 'Preview Image',
    ];
    acquia_polyfill_controller_set_return('image_style_options', $image_style_options_return_value);

    $form = $preview_image_form->getForm('article');

    $this->assertEquals(2, count($form['style']['#options']));
    $this->assertEquals($image_style_options_return_value, $form['style']['#options']);
  }

  /**
   * Tests the getForm() method, with an image field and with style.
   *
   * @covers ::getForm
   */
  public function testGetFormWithFieldAndStyle() {
    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $preview_image_form->setStringTranslation($this->getStringTranslationStub());

    $field_image_definition = $this->getFieldDefinition('image');
    $field_definitions = [
      'field_description' => $this->getFieldDefinition('description'),
      'field_image' => $field_image_definition,
      'field_entity_reference' => $this->getFieldDefinition('entity_reference'),
    ];
    $entity_reference_field_definitions = [
      'field_child_image' => $this->getFieldDefinition('image'),
      // This line is testing circular reference handling.
      // See $processedFieldHashes.
      'field_image' => $field_image_definition,
    ];
    $entity_type_definition = $this->getMock('Drupal\Core\Entity\EntityTypeInterface');

    $this->entityFieldManager->expects($this->at(0))
      ->method('getFieldDefinitions')
      ->with('node', 'article')
      ->willReturn($field_definitions);

    $this->contenthubEntityConfig->expects($this->once())
      ->method('getPreviewImageField')
      ->with('article')
      ->willReturn('field_media->field_image');
    $this->contenthubEntityConfig->expects($this->once())
      ->method('getPreviewImageStyle')
      ->with('article')
      ->willReturn('medium');

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinition')
      ->with('entity_reference_setting')
      ->willReturn($entity_type_definition);
    $entity_type_definition->expects($this->once())
      ->method('entityClassImplements')
      ->with('\Drupal\Core\Entity\FieldableEntityInterface')
      ->willReturn(TRUE);
    $this->entityFieldManager->expects($this->at(1))
      ->method('getFieldDefinitions')
      ->with('entity_reference_setting', 'entity_reference')
      ->willReturn($entity_reference_field_definitions);

    acquia_polyfill_controller_set_return('image_style_options', ['medium' => 'Medium']);

    $form = $preview_image_form->getForm('article');

    $expected_field_options = [
      'field_image' => 'Image Label (field_image)',
      'field_entity_reference->field_child_image' => 'Entity_reference Label->Image Label (field_entity_reference->field_child_image)',
    ];
    $this->assertEquals('Acquia Content Hub', $form['#title']);
    $this->assertEquals($expected_field_options, $form['field']['#options']);
    $this->assertEquals('field_media->field_image', $form['field']['#default_value']);
    $this->assertEquals(['acquia_contenthub_preview_image_add' => 'Acquia Content Hub Preview Image (150×150)', 'medium' => 'Medium'], $form['style']['#options']);
    $this->assertEquals('medium', $form['style']['#default_value']);
  }

  /**
   * Tests the saveSettings() method.
   *
   * @covers ::saveSettings
   */
  public function testSaveSettings() {
    $this->contenthubEntityConfig->expects($this->at(0))
      ->method('setPreviewImageField')
      ->with('article', 'some_field');
    $this->contenthubEntityConfig->expects($this->at(1))
      ->method('setPreviewImageStyle')
      ->with('article', 'some_style');
    $this->contenthubEntityConfig->expects($this->at(2))
      ->method('save');

    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $preview_image_form->saveSettings('article', ['field' => 'some_field', 'style' => 'some_style']);
  }

  /**
   * Tests the saveSettings() method, add default image style option.
   *
   * @covers ::saveSettings
   */
  public function testSaveSettingsAddDefaultImageStyleOption() {
    $image_style = $this->getMockBuilder('Drupal\image\Entity\ImageStyle')
      ->disableOriginalConstructor()
      ->getMock();
    $image_style->expects($this->once())
      ->method('addImageEffect')
      ->with([
        'id' => 'image_scale_and_crop',
        'weight' => 1,
        'data' => [
          'width' => 150,
          'height' => 150,
        ],
      ]);
    $image_style->expects($this->once())
      ->method('save');

    $this->entityTypeRepository = $this->getMock('Drupal\Core\Entity\EntityTypeRepositoryInterface');
    $entity_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager = $this->getMock('\Drupal\Core\Entity\EntityTypeManagerInterface');

    // Setting up the Container.
    $container = new Container();
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('entity_type.repository', $this->entityTypeRepository);
    \Drupal::setContainer($container);

    $this->entityTypeRepository->expects($this->once())
      ->method('getEntityTypeFromClass')
      ->with('Drupal\image\Entity\ImageStyle')
      ->willReturn($image_style);
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with($image_style)
      ->willReturn($entity_storage);
    $image_style_create_argument = [
      'label' => 'Acquia Content Hub Preview Image (150×150)',
      'name' => 'acquia_contenthub_preview_image',
    ];
    $entity_storage->expects($this->once())
      ->method('create')
      ->with($image_style_create_argument)
      ->willReturn($image_style);

    $this->contenthubEntityConfig->expects($this->at(0))
      ->method('setPreviewImageField')
      ->with('article', 'some_field');
    $this->contenthubEntityConfig->expects($this->at(1))
      ->method('setPreviewImageStyle')
      ->with('article', NodeTypePreviewImageForm::PREVIEW_IMAGE_DEFAULT_KEY);
    $this->contenthubEntityConfig->expects($this->at(2))
      ->method('save');

    $preview_image_form = new NodeTypePreviewImageForm($this->contenthubEntityManager, $this->entityTypeManager, $this->entityFieldManager);
    $preview_image_form->setStringTranslation($this->getStringTranslationStub());
    $preview_image_form->saveSettings('article', ['field' => 'some_field', 'style' => NodeTypePreviewImageForm::PREVIEW_IMAGE_ADD_DEFAULT_KEY]);
  }

  /**
   * Get FieldDefinition mock.
   *
   * @param string $type
   *   FieldDefinition type.
   */
  private function getFieldDefinition($type = 'other') {
    $field_definition = $this->getMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->at(0))
      ->method('getType')
      ->willReturn($type);
    $field_definition->expects($this->at(1))
      ->method('getSetting')
      ->with('target_type')
      ->willReturn($type . '_setting');
    $field_definition->expects($this->at(2))
      ->method('getLabel')
      ->willReturn(ucfirst($type) . ' Label');
    return $field_definition;
  }

}
