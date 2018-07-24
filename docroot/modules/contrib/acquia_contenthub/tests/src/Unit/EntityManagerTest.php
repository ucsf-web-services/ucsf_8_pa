<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig;
use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\EntityManager;

/**
 * PHPUnit for the EntityManager class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\EntityManager
 *
 * @group acquia_contenthub
 */
class EntityManagerTest extends UnitTestCase {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $loggerFactory;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $configFactory;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager|\PHPUnit_Framework_MockObject_MockObject
   */
  private $clientManager;

  /**
   * The Content Hub Imported Entities Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contentHubEntitiesTracking;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $entityTypeManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $entityTypeBundleInfoManager;

  /**
   * The Basic HTTP Kernel to make requests.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $kernel;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit_Framework_MockObject_MockObject
   */
  private $settings;

  /**
   * Content Entity Type.
   *
   * @var \Drupal\Core\Entity\ContentEntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contentEntityType;

  /**
   * Config Entity Type.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $configEntityType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->loggerFactory = $this->getMockBuilder('Drupal\Core\Logger\LoggerChannelFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory = $this->getMockBuilder('Drupal\Core\Config\ConfigFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->clientManager = $this->getMock('Drupal\acquia_contenthub\Client\ClientManagerInterface');
    $this->contentHubEntitiesTracking = $this->getMockBuilder('Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();
    $this->entityTypeManager = $this->getMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->entityTypeBundleInfoManager = $this->getMock('Drupal\Core\Entity\EntityTypeBundleInfoInterface');
    $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

    $this->settings = $this->getMockBuilder('Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $this->configFactory->expects($this->at(0))
      ->method('get')
      ->with('acquia_contenthub.admin_settings')
      ->willReturn($this->settings);

    $this->contentEntityType = $this->getMock('Drupal\Core\Entity\ContentEntityTypeInterface');
    $this->configEntityType = $this->getMock('Drupal\Core\Config\Entity\ConfigEntityTypeInterface');
  }

  /**
   * Call protected/private method of a class.
   *
   * @param object &$object
   *   Instantiated object that we will run method on.
   * @param string $methodName
   *   Method name to call.
   * @param array $parameters
   *   Array of parameters to pass into method.
   *
   * @return mixed
   *   Method return.
   */
  public function invokeMethod(&$object, $methodName, array $parameters = []) {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(TRUE);

    return $method->invokeArgs($object, $parameters);
  }

  /**
   * Builds a ContentHubEntityTypeConfig entity.
   *
   * @param string $id
   *   The Configuration entity ID.
   *
   * @return \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig
   *   The Configuration entity.
   */
  protected function getContentHubEntityTypeConfigEntity($id) {
    $bundles = [
      'entity_type_1' => [
        'bundle_11' => [
          'enable_index' => 1,
          'enable_viewmodes' => 1,
          'rendering' => [
            'view_1',
            'view_2',
            'view_3',
          ],
        ],
      ],
      'entity_type_2' => [
        'bundle_21' => [
          'enable_index' => 1,
          'enable_viewmodes' => 0,
          'rendering' => [],
        ],
        'bundle_22' => [
          'enable_index' => 0,
          'enable_viewmodes' => 0,
          'rendering' => [
            'view_4',
          ],
        ],
        'bundle_23' => [
          'enable_index' => 1,
          'enable_viewmodes' => 1,
          'rendering' => [],
        ],
      ],
      'entity_type_3' => [
        'bundle_31' => [
          'enable_index' => 0,
          'enable_viewmodes' => 0,
          'rendering' => [],
        ],
      ],
    ];
    $values = [
      'id' => $id,
    ];
    $config_entity = new ContentHubEntityTypeConfig($values, 'acquia_contenthub_entity_config');
    $config_entity->setBundles($bundles[$id]);
    return $config_entity;
  }

  /**
   * Defines the Content Hub Entity Types Configuration.
   *
   * @return array
   *   An array of Content Hub Entity Types configuration.
   */
  private function getContentHubConfigStorage() {
    // Creating Configuration Entities.
    $config_entity1 = $this->getContentHubEntityTypeConfigEntity('entity_type_1');
    $config_entity2 = $this->getContentHubEntityTypeConfigEntity('entity_type_2');
    $config_entity3 = $this->getContentHubEntityTypeConfigEntity('entity_type_3');

    // Grouping configuration entities.
    $config_entities = [
      'entity_type_1' => $config_entity1,
      'entity_type_2' => $config_entity2,
      'entity_type_3' => $config_entity3,
    ];
    $config_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $config_storage->method('loadMultiple')->willReturn($config_entities);
    return $config_storage;
  }

  /**
   * Test for getContentHubEnabledEntityTypeIds() method.
   *
   * @covers ::getContentHubEnabledEntityTypeIds
   */
  public function testGetContentHubEnabledEntityTypeIds() {
    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    $config_storage = $this->getContentHubConfigStorage();
    $this->entityTypeManager->method('getStorage')->with('acquia_contenthub_entity_config')->willReturn($config_storage);

    $enabled_entity_type_ids = $entity_manager->getContentHubEnabledEntityTypeIds();
    $expected_entity_type_ids = [
      'entity_type_1',
      'entity_type_2',
    ];
    $this->assertEquals($expected_entity_type_ids, $enabled_entity_type_ids);
  }

  /**
   * Test for getAllowedEntityTypes() method.
   *
   * @covers ::getAllowedEntityTypes
   */
  public function testGetAllowedEntityTypes() {

    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    $entity_types = [
      'content_entity_1' => $this->contentEntityType,
      'content_entity_2' => $this->contentEntityType,
      'comment' => $this->contentEntityType,
      'user' => $this->contentEntityType,
      'config_entity_1' => $this->configEntityType,
      'config_entity_2' => $this->configEntityType,
    ];

    $this->entityTypeManager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn($entity_types);

    $bundles = [
      'bundle1' => [
        'label' => 'bundle1',
      ],
      'bundle2' => [
        'label' => 'bundle2',
      ],
    ];

    $this->entityTypeBundleInfoManager->expects($this->at(0))
      ->method('getBundleInfo')
      ->with('content_entity_1')
      ->willReturn($bundles);

    // Second content entity does not have bundles.
    $this->entityTypeBundleInfoManager->expects($this->at(1))
      ->method('getBundleInfo')
      ->with('content_entity_2')
      ->willReturn(NULL);

    $entity_types = $entity_manager->getAllowedEntityTypes();

    // We expect that an entity without bundles shouldn't show up in the list.
    $expected_entity_types = [
      'content_entity_1' => [
        'bundle1' => 'bundle1',
        'bundle2' => 'bundle2',
      ],
    ];
    $this->assertEquals($expected_entity_types, $entity_types);
  }

  /**
   * Test for isSupportedContentHubEntity() method.
   *
   * @covers ::isSupportedContentHubEntity
   */
  public function testSupportedContentHubEntity() {
    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    // Defining a Content Entity.
    $enabled_methods = [
      'getEntityTypeId',
    ];
    $content_entity = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityBase')
      ->disableOriginalConstructor()
      ->setMethods($enabled_methods)
      ->getMockForAbstractClass();
    // Testing node is a supported Content Hub entity.
    $content_entity->expects($this->at(0))
      ->method('getEntityTypeId')
      ->willReturn('node');
    // Testing taxonomy_term is a supported Content Hub entity.
    $content_entity->expects($this->at(1))
      ->method('getEntityTypeId')
      ->willReturn('taxonomy_term');
    // Testing user is not a supported Content Hub entity.
    $content_entity->expects($this->at(2))
      ->method('getEntityTypeId')
      ->willReturn('user');
    $this->assertTrue($entity_manager->isSupportedContentHubEntity($content_entity));
    $this->assertTrue($entity_manager->isSupportedContentHubEntity($content_entity));
    $this->assertFalse($entity_manager->isSupportedContentHubEntity($content_entity));
  }

  /**
   * Test for getBulkResourceUrl() method.
   *
   * @covers ::getBulkResourceUrl
   */
  public function testGetBulkResourceUrl() {
    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    $container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($container);
    $bulk_route_name = 'acquia_contenthub.acquia_contenthub_bulk_cdf';
    $url_options = ['option1' => 'option_value_1'];
    $url_generator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $url_generator
      ->method('generateFromRoute')
      ->with($bulk_route_name, $url_options)
      ->willReturn('/node/1');
    $container->expects($this->once())
      ->method('get')
      ->with('url_generator')
      ->willReturn($url_generator);
    $this->settings->expects($this->once())
      ->method('get')
      ->with('rewrite_domain')
      ->willReturn('http://my-rewrite-domain.com');

    $result_url = $entity_manager->getBulkResourceUrl($url_options);

    $expected_url = 'http://my-rewrite-domain.com/node/1';
    $this->assertEquals($expected_url, $result_url);
  }

  /**
   * Test for getBulkResourceUrl() method, path is already external.
   *
   * @covers ::getBulkResourceUrl
   */
  public function testGetBulkResourceUrlIsExternal() {
    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    $container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($container);
    $bulk_route_name = 'acquia_contenthub.acquia_contenthub_bulk_cdf';
    $url_options = ['option1' => 'option_value_1'];
    $url_generator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $url_generator
      ->method('generateFromRoute')
      ->with($bulk_route_name, $url_options)
      ->willReturn('http://localhost/node/1');
    $container->expects($this->once())
      ->method('get')
      ->with('url_generator')
      ->willReturn($url_generator);
    $this->settings->expects($this->once())
      ->method('get')
      ->with('rewrite_domain')
      ->willReturn('http://my-rewrite-domain.com');

    $result_url = $entity_manager->getBulkResourceUrl($url_options);

    $expected_url = 'http://localhost/node/1';
    $this->assertEquals($expected_url, $result_url);
  }

  /**
   * Generate a list of config entity configurations.
   */
  private function getContentHubEntityTypeConfigEntityId($id) {
    $bundles = [
      'node' => [
        'article' => [
          'enable_index' => 1,
          'enable_viewmodes' => 1,
          'rendering' => [
            'view_node1',
            'view_node2',
            'view_node3',
          ],
        ],
      ],
      'file' => [
        'image' => [
          'enable_index' => 1,
          'enable_viewmodes' => 1,
          'rendering' => [
            'view_file1',
            'view_file2',
            'view_file3',
          ],
        ],
      ],
    ];
    $values = [
      'id' => $id,
    ];
    $config_entity = new ContentHubEntityTypeConfig($values, 'acquia_contenthub_entity_config');
    $config_entity->setBundles($bundles[$id]);
    return $config_entity;
  }

  /**
   * Test for isEligibleDependency(), isEligibleEntity() methods.
   *
   * @covers ::isEligibleDependency
   */
  public function testIsEligibleDependency() {
    // Initializing Container.
    $module_handler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $module_handler->expects($this->any())->method('invokeAll')->willReturn([]);
    $container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($container);
    $container->expects($this->any())
      ->method('get')
      ->with('module_handler')
      ->willReturn($module_handler);

    // Defining configuration entities.
    $node_config_entity = $this->getContentHubEntityTypeConfigEntityId('node');
    $file_config_entity = $this->getContentHubEntityTypeConfigEntityId('file');
    $config_entities = [
      'node' => $node_config_entity,
      'file' => $file_config_entity,
    ];
    $config_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $config_storage->method('loadMultiple')->willReturn($config_entities);
    $this->entityTypeManager->method('getStorage')->with('acquia_contenthub_entity_config')->willReturn($config_storage);

    $entity_manager = new EntityManager($this->loggerFactory, $this->configFactory, $this->clientManager, $this->contentHubEntitiesTracking, $this->entityTypeManager, $this->entityTypeBundleInfoManager, $this->kernel);

    // Testing a Node that has been already synchronized.
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->method('id')->willReturn(1);
    $node->expects($this->any())->method('getEntityTypeId')->willReturn('node');
    $node->__contenthub_entity_syncing = TRUE;
    $result = $entity_manager->isEligibleDependency($node);
    $this->assertFalse($result);

    // Testing a bundle that is not configured to be exportable.
    unset($node->__contenthub_entity_syncing);
    $node->expects($this->at(0))->method('bundle')->willReturn('page');
    $result = $entity_manager->isEligibleDependency($node);
    $this->assertFalse($result);

    // Testing an qualified entity that was not previously imported or exported.
    $type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

    $type->expects($this->any())->method('hasKey')->willReturnMap([['status', TRUE], ['revision', TRUE]]);
    $type->expects($this->any())->method('getKey')->willReturnMap([['status', 'status'], ['revision', 'vid']]);
    $field_storage = $this->getMock('\Drupal\Core\Field\FieldStorageDefinitionInterface');
    $field_storage->expects($this->any())->method('getMainPropertyName')->willReturn('value');
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->any())->method('getFieldStorageDefinition')->willReturn($field_storage);
    $node->expects($this->any())->method('getFieldDefinition')->with('status')->willReturn($field_definition);
    $status_field = new \stdClass();
    $status_field->value = TRUE;
    $node->expects($this->any())->method('get')->with('status')->willReturn($status_field);

    $node->expects($this->any())->method('getEntityType')->willReturn($type);
    $node->expects($this->at(1))->method('bundle')->willReturn('article');
    $this->contentHubEntitiesTracking->expects($this->any())->method('loadImportedByDrupalEntity')->willReturn(FALSE);
    $this->contentHubEntitiesTracking->expects($this->any())->method('loadExportedByUuid')->willReturn(FALSE);
    $result = $entity_manager->isEligibleDependency($node);
    $this->assertTrue($result);

    // Testing a file entity with status = TEMPORARY.
    $file = $this->getMock('\Drupal\file\FileInterface');
    $file->method('id')->willReturn(1);
    $file->expects($this->any())->method('getEntityTypeId')->willReturn('file');
    $file->expects($this->any())->method('bundle')->willReturn('image');
    $file->expects($this->at(2))->method('isTemporary')->willReturn(TRUE);
    $file->expects($this->at(3))->method('isTemporary')->willReturn(FALSE);
    $result = $entity_manager->isEligibleDependency($file);
    $this->assertFalse($result);

    // Testing a file entity with status = PERMANENT.
    // Testing an qualified entity that was not previously imported or exported.
    $type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $type->expects($this->any())->method('hasKey')->willReturnMap([['status', FALSE], ['revision', FALSE]]);
    $file->expects($this->any())->method('getEntityType')->willReturn($type);
    $result = $entity_manager->isEligibleDependency($file);
    $this->assertTrue($result);
  }

}
