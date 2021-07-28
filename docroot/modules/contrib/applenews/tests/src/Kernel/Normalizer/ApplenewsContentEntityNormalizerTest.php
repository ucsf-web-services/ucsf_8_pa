<?php

namespace Drupal\Tests\applenews\Kernel\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Body;
use ChapterThree\AppleNewsAPI\Document\Components\Title;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\applenews\Traits\AppleNewsTestTrait;
use Drupal\user\Entity\User;

/**
 * Tests the top level normalizer for Apple News.
 *
 * This is as close to an end to end test, without being an integration test,
 * of the Apple News normalizers.
 *
 * @group applenews
 *
 * @coversDefaultClass \Drupal\applenews\Normalizer\ApplenewsContentEntityNormalizer
 */
class ApplenewsContentEntityNormalizerTest extends KernelTestBase {
  use AppleNewsTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field',
    'serialization',
    'node',
    'user',
    'applenews',
    'applenews_test',
  ];

  /**
   * Serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Name of the test user we are using.
   *
   * @var string
   */
  protected $userName;

  /**
   * User entity we are testing with.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->serializer = $this->container->get('serializer');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system', 'field']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    // Create a user to use for testing.
    $this->userName = $this->randomMachineName();
    $account = User::create(['name' => $this->userName, 'status' => 1]);
    $account->enforceIsNew();
    $account->save();
    $this->account = $account;

    // Create the node bundles required for testing.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    // Create a couple fields attached to the node entity type.
    foreach (['field_one', 'field_two'] as $field_name) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => 'string_long',
      ]);
      $field_storage->save();

      $instance = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'article',
        'label' => $this->randomMachineName(),
      ]);
      $instance->save();
    }
  }

  /**
   * Tests the normalize method with real node objects.
   *
   * @covers ::normalize
   */
  public function testNormalize() {
    // Setup a node with some simple values for testing.
    $title = $this->randomString();
    $node = Node::create([
      'title' => $title,
      'type' => 'article',
      'field_one' => [
        'value' => 'This is a value for the first field.',
      ],
      'field_two' => [
        'value' => 'This is a value for the second field.',
      ],
    ]);
    $node->setOwner($this->account);
    $node->save();

    // Setup a template that has the title, and both fields set as Body
    // components.
    $components = $this->getDefaultComponents();
    $component_field_one_uuid = $this->randomMachineName();
    $component_field_two_uuid = $this->randomMachineName();
    $components[$component_field_one_uuid] = [
      'uuid' => $component_field_one_uuid,
      'id' => 'default_text:body',
      'weight' => 1,
      'component_layout' => $this->getDefaultComponentLayout(),
      'component_data' => [
        'text' => [
          'field_name' => 'field_one',
          'field_property' => 'value',
        ],
        'format' => 'none',
      ],
    ];
    $components[$component_field_two_uuid] = [
      'uuid' => $component_field_two_uuid,
      'id' => 'default_text:body',
      'weight' => 1,
      'component_layout' => $this->getDefaultComponentLayout(),
      'component_data' => [
        'text' => [
          'field_name' => 'field_two',
          'field_property' => 'value',
        ],
        'format' => 'none',
      ],
    ];
    $template = $this->createApplenewsTemplate('article', $components);
    $document = $this->serializer->normalize($node, 'applenews', ['template_id' => $template->id()]);
    $this->assertEquals($node->label(), $document['title']);
    $this->assertCount(3, $document['components']);
    $this->assertInstanceOf(Title::class, $document['components'][0]);
    $this->assertInstanceOf(Body::class, $document['components'][1]);
    $this->assertInstanceOf(Body::class, $document['components'][1]);
    $this->assertEquals($node->label(), $document['components'][0]->getText());
    $this->assertEquals($node->field_one->value, $document['components'][1]->getText());
    $this->assertEquals($node->field_two->value, $document['components'][2]->getText());

    // Assert properties added via the event subscriber in applenews_test.
    // @see \Drupal\applenews_test\EventSubscriber\DocumentPostTransformEventSubscriber::documentPostTransform
    $this->assertArrayHasKey('metadata', $document);
    $canonical_url = $node->toUrl()->setAbsolute(TRUE)->toString();
    $this->assertEquals($canonical_url, $document['metadata']->getCanonicalURL());
    $this->assertNotEmpty($document['metadata']->getDateCreated());
    $this->assertNotEmpty($document['metadata']->getDatePublished());
    $this->assertNotEmpty($document['metadata']->getDateModified());
    $this->assertEquals(['Joe Mayo'], $document['metadata']->getAuthors());
  }

}
