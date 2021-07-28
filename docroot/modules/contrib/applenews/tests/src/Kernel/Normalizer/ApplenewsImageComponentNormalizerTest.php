<?php

namespace Drupal\Tests\applenews\Kernel\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Photo;
use Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests the ApplenewsImageComponentNormalizer class.
 *
 * @group applenews
 *
 * @coversDefaultClass \Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer
 */
class ApplenewsImageComponentNormalizerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'serialization',
    'applenews',
  ];

  /**
   * Applenews image component normalizer under test.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer
   */
  protected $applenewsImageComponentNormalizer;

  /**
   * Mock serializer object.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Partially mock ApplenewsImageComponentNormalizer as our SUT as we don't
    // care about what file_create_url returns and want to mock that.
    $this->applenewsImageComponentNormalizer = $this->getMockBuilder(ApplenewsImageComponentNormalizer::class)
      ->enableOriginalConstructor()
      ->setConstructorArgs([$this->container->get('plugin.manager.applenews_component_type')])
      ->onlyMethods(['fileCreateUrl'])
      ->getMock();
    $this->applenewsImageComponentNormalizer
      ->method('fileCreateUrl')
      ->willReturn('http://example.com/test/example.jpg');
    $this->serializer = $this->getMockBuilder(Serializer::class)
      ->getMock();
    $this->applenewsImageComponentNormalizer->setSerializer($this->serializer);
  }

  /**
   * Test the normalize method for the empty case.
   *
   * @covers ::normalize
   * @covers ::getUrl
   */
  public function testNormalizeEmptyImage() {
    $data = $this->getDataStub();
    $context = [
      'entity' => $this->createMock(FieldableEntityInterface::class),
    ];
    $this->serializer->method('normalize')->willReturn(NULL);
    $this->assertNull($this->applenewsImageComponentNormalizer->normalize($data, 'applenews', $context));
  }

  /**
   * Test the normalize method for the file entity case.
   *
   * @covers ::normalize
   * @covers ::getUrl
   */
  public function testNormalizeFileEntity() {
    $data = $this->getDataStub();
    $context = [
      'entity' => $this->createMock(FieldableEntityInterface::class),
    ];
    $normalized_file_field = [
      'uri' => [
        [
          'value' => 'public://test/example.jpg',
          'url' => '/sites/default/files/test/example.jpg',
        ],
      ],
    ];
    // Mock a normalized file field and empty caption return.
    $this->serializer->method('normalize')
      ->willReturnOnConsecutiveCalls($normalized_file_field, NULL);
    /** @var \ChapterThree\AppleNewsAPI\Document\Components\Photo $component */
    $component = $this->applenewsImageComponentNormalizer->normalize($data, 'applenews', $context);
    $this->assertInstanceOf(Photo::class, $component);
    $this->assertEquals('http://example.com/test/example.jpg', $component->getUrl());
  }

  /**
   * Test the normalize method when the field normalizes to an entity id.
   *
   * @covers ::normalize
   * @covers ::getUrl
   */
  public function testNormalizeEntityId() {
    $data = $this->getDataStub();
    $context = [
      'entity' => $this->createMock(FieldableEntityInterface::class),
    ];
    $normalized_file_field = '1234';
    // Mock a normalized file field and empty caption return.
    $this->serializer->method('normalize')
      ->willReturnOnConsecutiveCalls($normalized_file_field, NULL);
    $this->assertNull($this->applenewsImageComponentNormalizer->normalize($data, 'applenews', $context));
  }

  /**
   * Get a stub $data array object.
   *
   * For call to
   * \Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer::normalize.
   *
   * @return array
   *   Stub $data array for use in call to
   *   \Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer::normalize
   */
  protected function getDataStub() {
    return [
      'id' => 'default_image:photo',
      'component_data' => [
        'URL' => [
          'field_name' => 'field_image',
          'field_property' => 'entity',
        ],
        'caption' => [
          'field_name' => 'title',
          'field_property' => 'base',
        ],
      ],
      'component_layout' => [
        'column_start' => 0,
        'column_span' => 7,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'ignore_margin' => 'none',
        'ignore_gutter' => 'none',
        'minimum_height' => 10,
        'minimum_height_unit' => 'pt',
        'maximum_width' => NULL,
        'maximum_width_unit' => 'pt',
      ],
    ];
  }

}
