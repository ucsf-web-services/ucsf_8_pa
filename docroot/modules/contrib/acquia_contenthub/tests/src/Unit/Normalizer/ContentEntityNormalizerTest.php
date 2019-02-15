<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Normalizer;

use Acquia\ContentHubClient\Entity;
use Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer;
use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;

require_once __DIR__ . '/../Polyfill/Drupal.php';

/**
 * PHPUnit test for the ContentEntityNormalizer class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer
 *
 * @group acquia_contenthub
 */
class ContentEntityNormalizerTest extends UnitTestCase {

  /**
   * Enable or disable the backup and restoration of the $GLOBALS array.
   *
   * @var bool
   */
  protected $backupGlobals = FALSE;

  /**
   * The acquia_contenthub.entity_config array.
   *
   * @var array
   */
  protected $configEntity = [
    'dependency_depth' => 3,
    'user_role'        => AccountInterface::ANONYMOUS_ROLE,
  ];

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The mock serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $serializer;

  /**
   * The normalizer under test.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer
   */
  protected $contentEntityNormalizer;

  /**
   * The mock view modes extractor.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractor
   */
  protected $contentEntityViewModesExtractor;

  /**
   * The mock config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mock module handler factory.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Entity Repository Interface.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The Kernel Interface.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $kernel;

  /**
   * The Renderer Interface.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * Entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|/PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeRepository;

  /**
   * The Entity Type Manager Interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $loggerFactory;

  /**
   * The Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * User Context (Expected to be Anonymous for this test).
   *
   * @var \Drupal\acquia_contenthub\Session\ContentHubUserSession
   */
  protected $userContext;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($this->container);

    $this->configFactory = $this->getMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->configFactory
      ->method('get')
      ->willReturnCallback(function ($argument) {
        if ($argument == 'acquia_contenthub.admin_settings') {
          return $this->createMockForContentHubAdminConfig();
        }
        elseif ($argument == 'acquia_contenthub.entity_config') {
          return $this->createMockForContentHubEntityConfig();
        }
        return NULL;
      });

    $this->loggerFactory = $this->getMockBuilder('Drupal\Core\Logger\LoggerChannelFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentEntityViewModesExtractor = $this->getMock('Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractorInterface');
    $this->moduleHandler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $this->entityRepository = $this->getMock('Drupal\Core\Entity\EntityRepositoryInterface');
    $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    $this->renderer = $this->getMock('Drupal\Core\Render\RendererInterface');

    $this->entityManager = $this->getMockBuilder('Drupal\acquia_contenthub\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityTypeManager = $this->getMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $entity_type = $this->prophesize(EntityTypeInterface::class);
    $entity_type->getKey('bundle')->willReturn('type');
    $entity_type->getKey('langcode')->willReturn('langcode');
    $this->entityTypeManager->method('getDefinition')->willReturn($entity_type->reveal());

    $this->languageManager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');

    $this->userContext = new ContentHubUserSession(AccountInterface::ANONYMOUS_ROLE);

    $this->contentEntityNormalizer = new ContentEntityCdfNormalizer($this->configFactory, $this->contentEntityViewModesExtractor, $this->moduleHandler, $this->entityRepository, $this->kernel, $this->renderer, $this->entityManager, $this->entityTypeManager, $this->loggerFactory, $this->languageManager);
  }

  /**
   * Test the supportsNormalization method.
   *
   * @covers ::supportsNormalization
   */
  public function testSupportsNormalization() {
    $content_mock = $this->getMock('Drupal\Core\Entity\ContentEntityInterface');
    $config_mock = $this->getMock('Drupal\Core\Entity\ConfigEntityInterface');
    $this->assertTrue($this->contentEntityNormalizer->supportsNormalization($content_mock));
    $this->assertFalse($this->contentEntityNormalizer->supportsNormalization($config_mock));
  }

  /**
   * Test the getBaseRoot function.
   *
   * @covers ::getBaseRoot
   */
  public function testGetBaseRoot() {
    // With the global set.
    $GLOBALS['base_root'] = 'test';
    $this->assertEquals('test', $this->contentEntityNormalizer->getBaseRoot());
    unset($GLOBALS['base_root']);

    // Without the global set.
    $this->assertEquals('', $this->contentEntityNormalizer->getBaseRoot());
  }

  /**
   * Tests the normalize() method.
   *
   * Tests to see if it errors on the wrong object.
   *
   * @covers ::normalize
   */
  public function testNormalizeIncompatibleClass() {
    // Create a config entity class.
    $config_mock = $this->getMock('Drupal\Core\Entity\ConfigEntityInterface');
    // Normalize the Config Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($config_mock, 'acquia_contenthub_cdf');
    // Make sure it didn't do anything.
    $this->assertNull($normalized);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 field and checks if it appears in the normalized result.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeOneField() {

    $this->mockContainerResponseForNormalize();

    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);

    // Check the UUID property.
    $this->assertEquals('custom-uuid', $normalized_entity->getUuid());
    // Check if there was a created date set.
    $this->assertNotEmpty($normalized_entity->getCreated());
    // Check if there was a modified date set.
    $this->assertNotEmpty($normalized_entity->getModified());
    // Check if there was an origin property set.
    $this->assertEquals('test-origin', $normalized_entity->getOrigin());
    // Check if there was a type property set to the entity type.
    $this->assertEquals('node', $normalized_entity->getType());
    // Check if the field has the given value.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['test']]);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 field with multiple values and checks if it appears in the
   * normalized result. Also adds multiple languages to see if it properly
   * combines them.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   * @covers ::appendToAttribute
   */
  public function testNormalizeOneFieldMultiValued() {
    $this->mockContainerResponseForNormalize();
    $this->container->expects($this->at(2))->method('get')->with('entity_type.manager')->willReturn($this->entityTypeManager);

    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, [['value' => 'test'], ['value' => 'test2']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en', 'nl']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);

    // Check the UUID property.
    $this->assertEquals('custom-uuid', $normalized_entity->getUuid());
    // Check if there was a created date set.
    $this->assertNotEmpty($normalized_entity->getCreated());
    // Check if there was a modified date set.
    $this->assertNotEmpty($normalized_entity->getModified());
    // Check if there was an origin property set.
    $this->assertEquals('test-origin', $normalized_entity->getOrigin());
    // Check if there was a type property set to the entity type.
    $this->assertEquals('node', $normalized_entity->getType());
    // Check if the field has the given value.
    $expected_output = [
      'en' => ['test', 'test2'],
      'nl' => ['test', 'test2'],
    ];
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), $expected_output);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 field and the created and changed fields. Make sure there is
   * no changed or created field in the final attributes as those are
   * excluded.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   * @covers ::excludedProperties
   */
  public function testNormalizeWithCreatedAndChanged() {
    $this->mockContainerResponseForNormalize();

    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test']]),
      'created' => $this->createMockFieldListItem('created', 'timestamp', TRUE, $this->userContext, ['0' => ['value' => '1458811508']]),
      'changed' => $this->createMockFieldListItem('changed', 'timestamp', TRUE, $this->userContext, ['0' => ['value' => '1458811509']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if there was a created date set.
    $this->assertEquals($normalized_entity->getCreated(), date('c', 1458811508));
    // Check if there was a modified date set.
    // We assure that the date set will be the one when the CDF was modified
    // and sent to Content Hub, not when the drupal entity was las modified.
    $this->assertEquals($normalized_entity->getModified(), date('c'));
    // Check if field_1 has the correct values.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['test']]);
    // Field created should not be part of the normalizer.
    $this->assertFalse($normalized_entity->getAttribute('created'));
    // Field changed should not be part of the normalizer.
    $this->assertFalse($normalized_entity->getAttribute('changed'));
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 field but with any content in it. The field should not be present
   * and should be ignored.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithNoFieldValue() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, []),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if the field has the given value.
    $expected_output = [
      'en' => NULL,
    ];
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), $expected_output);
  }

  /**
   * Tests the normalize() method.
   *
   * Test that we can also map field names. The field type String maps to the
   * content hub type array<string> while the field name title field is
   * explicitely mapped to the singular version "string" for the content hub
   * types. Test that this is actually the case.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithFieldNameAsType() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'title' => $this->createMockFieldListItem('title', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values
    // Different expected value. Title is never plural.
    $this->assertEquals($normalized_entity->getAttribute('title')->getValues(), ['en' => 'test']);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests that we support other field types such as boolean, etc..
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithNonStringFieldType() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'voted' => $this->createMockFieldListItem('voted', 'boolean', TRUE, $this->userContext, ['0' => ['value' => TRUE]]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values
    // Different expected value. Title is never plural.
    $this->assertEquals($normalized_entity->getAttribute('voted')->getValues(), ['en' => [TRUE]]);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests that we support complex fields with more than just a value key.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithComplexFieldValues() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test', 'random_key' => 'random_data']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['{"value":"test","random_key":"random_data"}']]);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 2 fields. The user has access to 1 field but not the other.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithFieldWithoutAccess() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test']]),
      'field_2' => $this->createMockFieldListItem('field_2', 'string', FALSE, $this->userContext, ['0' => ['value' => 'test']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['test']]);
    // Field 2 should not be part of the normalizer.
    $this->assertFalse($normalized_entity->getAttribute('field_2'));
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 2 fields given a passed user context. Field 1 is accessible, but
   * field 2 is not.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithAccountContext() {
    $this->mockContainerResponseForNormalize();
    $mock_account = $this->getMock('Drupal\Core\Session\AccountInterface');
    $context = ['account' => $mock_account];

    // The mock account should get passed directly into the access() method on
    // field items from $context['account'].
    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $mock_account, ['0' => ['value' => 'test']]),
      'field_2' => $this->createMockFieldListItem('field_2', 'string', FALSE, $mock_account, ['0' => ['value' => 'test']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $mock_account);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity with English support.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf', $context);

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['test']]);
    // Field 2 should not be part of the resultset.
    $this->assertFalse($normalized_entity->getAttribute('field_2'));
  }

  /**
   * Tests the normalize() method for node revisions.
   *
   * Tests 2 fields given a passed user context. Field 1 is accessible, field 2
   * is a node revision id 'vid' which should be stripped out.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeWithRevisionId() {
    $this->mockContainerResponseForNormalize();
    $mock_account = $this->getMock('Drupal\Core\Session\AccountInterface');
    $context = ['account' => $mock_account];

    // The mock account should get passed directly into the access() method on
    // field items from $context['account'].
    $definitions = [
      'field_1' => $this->createMockFieldListItem('field_1', 'string', TRUE, $mock_account, ['0' => ['value' => 'test']]),
      'vid' => $this->createMockFieldListItem('vid', 'string', TRUE, $mock_account, ['0' => ['value' => '1']]),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $mock_account);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity with English support.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf', $context);

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);
    // Check if field_1 has the correct values.
    $this->assertEquals($normalized_entity->getAttribute('field_1')->getValues(), ['en' => ['test']]);
    // Field 2 should not be part of the resultset.
    $this->assertFalse($normalized_entity->getAttribute('vid'));
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 entity reference field and checks if it appears in the normalized
   * result. It should return the UUID of the referenced item.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeReferenceField() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'field_ref' => $this->createMockEntityReferenceFieldItemList('field_ref', TRUE, $this->userContext),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);

    // Check if the field has the given value.
    $this->assertEquals($normalized_entity->getAttribute('field_ref')->getValues(), ['en' => ['test-uuid-reference-1', 'test-uuid-reference-2']]);
  }

  /**
   * Tests the normalize() method for image references.
   *
   * Tests 1 entity reference field and checks if it appears in the normalized
   * result. It should return the normalized image, including alt/title
   * attributes.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeImageReferenceField() {
    $this->mockContainerResponseForNormalize();
    $definitions = [
      'field_image' => $this->createMockImageEntityReferenceFieldItemList('field_image', TRUE, $this->userContext),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);

    // Check if the field has the given value.
    $this->assertEquals($normalized_entity->getAttribute('field_image')->getValues(), ['en' => [0 => '{"alt":"test-alt-image-value","title":"test-alt-image-text","target_uuid":"[test-uuid-image-1]"}']]);
  }

  /**
   * Tests the normalize() method.
   *
   * Tests 1 entity reference field and checks if it appears in the normalized
   * result. It should return the id of the referenced item.
   *
   * @covers ::normalize
   * @covers ::addFieldsToContentHubEntity
   */
  public function testNormalizeTypeReferenceField() {
    $this->mockContainerResponseForNormalize();
    // NOTE: If you set the machine name of the mock field to 'type' things
    // don't work. Going with 'field_ref'.
    $definitions = [
      'field_ref' => $this->createMockEntityReferenceFieldItemList('field_ref', TRUE, $this->userContext),
    ];

    // Set our Serializer and expected serialized return value for the given
    // fields.
    $serializer = $this->getFieldsSerializer($definitions, $this->userContext);
    $this->contentEntityNormalizer->setSerializer($serializer);

    // Create our Content Entity.
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);

    // Normalize the Content Entity with the class that we are testing.
    $normalized = $this->contentEntityNormalizer->normalize($content_entity_mock, 'acquia_contenthub_cdf');

    // Check if valid result.
    $this->doTestValidResultForOneEntity($normalized);
    // Get our Content Hub Entity out of the result.
    $normalized_entity = $this->getContentHubEntityFromResult($normalized);

    // Check if the reference field has the right type in the CDF.
    $this->assertEquals($normalized_entity->getAttribute('field_ref')->getType(), 'array<reference>');
  }

  /**
   * Test the getFieldTypeMapping method.
   *
   * @covers ::getFieldTypeMapping
   */
  public function testGetFieldTypeMapping() {
    $definitions = [
      'title' => $this->createMockFieldListItem('title', 'string', TRUE, $this->userContext, ['0' => ['value' => 'test']]),
    ];
    $content_entity_mock = $this->createMockForContentEntity($definitions, ['en']);
    $mapping = $this->contentEntityNormalizer->getFieldTypeMapping($content_entity_mock);
    $this->assertNotEmpty($mapping);
    $this->assertEquals('array<boolean>', $mapping['boolean']);
    $this->assertEquals(NULL, $mapping['password']);
    $this->assertEquals('array<number>', $mapping['decimal']);
    $this->assertEquals('array<reference>', $mapping['entity_reference']);
    $this->assertEquals('array<string>', $mapping['fallback']);
    $this->assertEquals('string', $mapping['title']);
    $this->assertEquals('string', $mapping['langcode']);
  }

  /**
   * Test the denormalize method.
   *
   * @covers ::denormalize
   */
  public function testDenormalize() {
    $denormalized = $this->contentEntityNormalizer->denormalize(NULL, NULL);
    $this->assertNull($denormalized);
  }

  /**
   * Check if the base result set is correctly set to 1 entity.
   */
  private function doTestValidResultForOneEntity($normalized) {
    // Start testing our result set.
    $this->assertArrayHasKey('entities', $normalized);
    // We want 1 result in there.
    $this->assertCount(1, $normalized['entities']);
  }

  /**
   * Get the Content Hub Entity from our normalized array.
   *
   * @param array $normalized
   *   The normalized array structure containing the content hub entity
   *   objects.
   *
   * @return \Acquia\ContentHubClient\Entity
   *   The first ContentHub Entity from the resultset.
   */
  private function getContentHubEntityFromResult(array $normalized) {
    // Since there is only 1 entity, we are fairly certain the first one is
    // ours.
    /** @var \Acquia\ContentHubClient\Entity $normalized_entity */
    $normalized_entity = array_pop($normalized['entities']);
    // Check if it is of the expected class.
    $this->assertTrue($normalized_entity instanceof Entity);
    return $normalized_entity;
  }

  /**
   * Make sure we return the expected normalization results.
   *
   * For all the given definitions of fields with their respective values, we
   * need to be sure that when ->normalize is executed, it returns the expected
   * results.
   *
   * @param array $definitions
   *   The field definitions.
   * @param \Drupal\Core\Session\AccountInterface $user_context
   *   The user context such as the account.
   *
   * @return \Symfony\Component\Serializer\Serializer|\PHPUnit_Framework_MockObject_MockObject
   *   The Serializer.
   */
  protected function getFieldsSerializer(array $definitions, AccountInterface $user_context = NULL) {
    $serializer = $this->getMockBuilder('Symfony\Component\Serializer\Serializer')
      ->disableOriginalConstructor()
      ->setMethods(['normalize'])
      ->getMock();

    $serializer
      ->method('normalize')
      ->with($this->containsOnlyInstancesOf(
        'Drupal\Core\Field\FieldItemListInterface'),
        'json',
        [
          'account' => $user_context,
          'query_params' => [],
          'entity_type' => 'node',
        ]
      )
      ->willReturnCallback(function ($field, $format, $context) {
        if ($field) {
          return $field->getValue();
        }
        return NULL;
      });

    return $serializer;
  }

  /**
   * Creates a mock content entity.
   *
   * @param array $definitions
   *   The field definitions.
   * @param array $languages
   *   The languages that this fake entity should have.
   * @param bool $is_new
   *   TRUE if the entity is new, FALSE otherwise.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The fake ContentEntity.
   */
  public function createMockForContentEntity(array $definitions, array $languages, $is_new = FALSE) {
    $enabled_methods = [
      'getFields',
      'getEntityTypeId',
      'uuid',
      'get',
      'getTranslationLanguages',
      'getTranslation',
      'hasField',
      'toUrl',
      'access',
      'hasLinkTemplate',
      'isNew',
    ];

    $content_entity_mock = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityBase')
      ->disableOriginalConstructor()
      ->setMethods($enabled_methods)
      ->getMockForAbstractClass();

    $content_entity_mock->method('getFields')->willReturn($definitions);
    $content_entity_mock->method('isNew')->willReturn($is_new);

    // Return the given content.
    $content_entity_mock->method('get')->willReturnCallback(function ($name) use ($definitions) {
      if (isset($definitions[$name])) {
        return $definitions[$name];
      }
      return NULL;
    });

    $content_entity_mock->method(('hasField'))->willReturnCallback(function ($name) use ($definitions) {
      if (isset($definitions[$name])) {
        return TRUE;
      }
      return FALSE;
    });

    $content_entity_mock->method('getEntityTypeId')->willReturn('node');

    $content_entity_mock->method('uuid')->willReturn('custom-uuid');

    $content_entity_mock->method('getTranslation')->willReturn($content_entity_mock);

    $languages = $this->createMockLanguageList($languages);
    $content_entity_mock->method('getTranslationLanguages')->willReturn($languages);

    $url = $this->getMockBuilder('Drupal\Core\Url')->disableOriginalConstructor()->getMock();
    $url->method('toString')->willReturn('http://localhost/node/1');
    $content_entity_mock->method('toUrl')->willReturn($url);

    $access = $this->getMock('Drupal\Core\Access\AccessResultInterface');
    $access->method('isAllowed')->willReturn(TRUE);
    $content_entity_mock->method('access')->withAnyParameters()->willReturn($access);

    $content_entity_mock->method('hasLinkTemplate')->willReturn(TRUE);

    return $content_entity_mock;
  }

  /**
   * Returns a fake ContentHubAdminConfig object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The fake config.
   */
  public function createMockForContentHubAdminConfig() {
    $contenthub_admin_config = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();
    $contenthub_admin_config->method('get')->with('origin')->willReturn('test-origin');

    return $contenthub_admin_config;
  }

  /**
   * Returns a fake ContentHubEntityConfig object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The fake config.
   */
  public function createMockForContentHubEntityConfig() {
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
   * Creates a mock field list item.
   *
   * @param string $name
   *   Name of the mock field.
   * @param string $type
   *   Type of the mock field.
   * @param bool $access
   *   Defines whether anyone has access to this field or not.
   * @param bool $user_context
   *   The user context used to view the field.
   * @param array $return_value
   *   Expected return value.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\PHPUnit_Framework_MockObject_MockObject
   *   The mocked field items.
   */
  protected function createMockFieldListItem($name, $type = 'string', $access = TRUE, $user_context = NULL, array $return_value = []) {
    $mock = $this->getMock('Drupal\Core\Field\FieldItemListInterface');
    $mock->method('access')
      ->with('view', $user_context)
      ->will($this->returnValue($access));

    $field_def = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_def->method('getName')->willReturn($name);
    $field_def->method('getType')->willReturn($type);

    $mock->method('getValue')->willReturn($return_value);

    $mock->method('getFieldDefinition')->willReturn($field_def);

    return $mock;
  }

  /**
   * Creates a mock field entity reference field item list.
   *
   * @param string $name
   *   Name of the mock field.
   * @param bool $access
   *   Defines whether anyone has access to this field or not.
   * @param bool $user_context
   *   The user context used to view the field.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\PHPUnit_Framework_MockObject_MockObject
   *   The mocked field items.
   */
  protected function createMockEntityReferenceFieldItemList($name, $access = TRUE, $user_context = NULL) {
    $mock = $this->getMock('Drupal\Core\Field\EntityReferenceFieldItemListInterface');
    $mock->method('access')
      ->with('view', $user_context)
      ->will($this->returnValue($access));

    $field_def = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_def->method('getName')->willReturn($name);
    $field_def->method('getType')->willReturn('entity_reference');

    $mock->method('getValue')->willReturn('bla');

    $referenced_entities = [];
    $entity1 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity1->method('id')->willReturn('test-id-reference-1');
    $entity1->method('uuid')->willReturn('test-uuid-reference-1');
    $referenced_entities[] = $entity1;

    $entity2 = $this->getMock('\Drupal\Core\Entity\EntityInterface');
    $entity2->method('id')->willReturn('test-id-reference-2');
    $entity2->method('uuid')->willReturn('test-uuid-reference-2');
    $referenced_entities[] = $entity2;

    $mock->method('getFieldDefinition')->willReturn($field_def);
    $mock->method('referencedEntities')->willReturn($referenced_entities);

    return $mock;
  }

  /**
   * Creates a mock field entity reference field item list with an image item.
   *
   * @param string $name
   *   Name of the mock field.
   * @param bool $access
   *   Defines whether anyone has access to this field or not.
   * @param bool $user_context
   *   The user context used to view the field.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\PHPUnit_Framework_MockObject_MockObject
   *   The mocked field items.
   */
  protected function createMockImageEntityReferenceFieldItemList($name, $access = TRUE, $user_context = NULL) {
    $mock = $this->getMock('Drupal\Core\Field\EntityReferenceFieldItemListInterface');
    $mock->method('access')
      ->with('view', $user_context)
      ->will($this->returnValue($access));

    $field_def = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_def->method('getName')->willReturn($name);
    $field_def->method('getType')->willReturn('image');
    $mock->method('getValue')->willReturn([
      0 => [
        'target_id' => 'test-id-image-1',
        'alt' => 'test-alt-image-value',
        'title' => 'test-alt-image-text',
        'width' => '100',
        'height' => '100',
      ],
    ]);

    $referenced_entities = [];
    $methods = [
      'id',
      'uuid',
      'getFileUri',
    ];
    $image1 = $this->getMockBuilder('\Drupal\image\Plugin\Field\FieldType\ImageItem')->disableOriginalConstructor()->setMethods($methods)->getMock();
    $image1->method('id')->willReturn('test-id-image-1');
    $image1->method('uuid')->willReturn('test-uuid-image-1');
    $image1->method('getFileUri')->willReturn('public://test-image-1.jpg');
    $referenced_entities[] = $image1;

    $mock->method('getFieldDefinition')->willReturn($field_def);
    $mock->method('referencedEntities')->willReturn($referenced_entities);
    return $mock;
  }

  /**
   * Creates a mock language list.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]|\PHPUnit_Framework_MockObject_MockObject
   *   The mocked Languages.
   */
  protected function createMockLanguageList($languages = ['en']) {
    $language_objects = [];
    foreach ($languages as $language) {
      $mock = $this->getMock('Drupal\Core\Language\LanguageInterface');
      $mock->method('getId')->willReturn($language);
      $language_objects[$language] = $mock;
    }

    return $language_objects;
  }

  /**
   * Mock container response for normalize().
   */
  protected function mockContainerResponseForNormalize() {
    $request_stack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');
    $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

    $request
      ->method('getRequestUri')
      ->willReturn('http://localhost/node/1');

    $request_stack
      ->method('getCurrentRequest')
      ->willReturn($request);

    $url_generator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $url_generator
      ->method('generateFromRoute')
      ->with('entity.node.canonical', ['node' => 1], [], FALSE)
      ->willReturn('http://localhost/node/1');

    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->any())
      ->method('getKeys')
      ->willReturn([
        'uid' => 'uid',
        'id' => 'nid',
        'revision' => 'vid',
        'uuid' => 'uuid',
      ]);

    $this->entityTypeManager = $this->getMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->entityTypeManager
      ->method('getDefinition')
      ->with('node')
      ->willReturn($entity_type);

    // Defining some services.
    $this->container->expects($this->at(0))->method('get')->with('request_stack')->willReturn($request_stack);
    $this->container->expects($this->at(1))->method('get')->with('entity_type.manager')->willReturn($this->entityTypeManager);
  }

}
