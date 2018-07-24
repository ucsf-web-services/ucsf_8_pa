<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Core\Language\Language;
use Drupal\file\Entity\File;
use Drupal\media_entity\Entity\Media;

/**
 * Test Acquia Content Hub entity_embed.
 *
 * @group acquia_contenthub_no_test
 */
class EntityEmbedTest extends WebTestBase {

  /**
   * Admin role.
   *
   * @var string
   */
  protected $adminRole;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'node',
    'embed',
    'image',
    'filter',
    'entity_embed',
    'media_entity',
    'acquia_contenthub',
    'media_entity_image',
    'media_entity_image_ch',
  ];

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'bypass node access',
    'administer acquia content hub',
    'administer content types',
    'access administration pages',
    'administer filters',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create the users used for the tests.
    $this->adminRole = $this->createAdminRole('administrator', 'Administrator');
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
  }

  /**
   * Tests acquia_contenthub_cdf format for entity_embed entities inside a node.
   */
  public function testEntityEmbedCdf() {
    $this->drupalLogin($this->adminUser);
    $this->setRoleFor($this->adminRole);

    // Create image file.
    $image = current($this->drupalGetTestFiles('image'));
    $image_file = File::create(
      (array) $image
    );
    $image_file->save();

    // Create media entity with image.
    $values = [
      'name' => 'Unnamed',
      'bundle' => 'image',
      'langcode' => Language::LANGCODE_DEFAULT,
      'status' => Media::PUBLISHED,
      'uid' => 1,
      'field_media_in_library' => TRUE,
      'image' => [[
        'target_id' => $image_file->id(),
      ],
      ],
    ];
    $image_entity = Media::create($values);
    $image_entity->save();

    // Prepare embed markup.
    $bodyText = '<drupal-entity 
        data-entity-embed-display="media_image" 
        data-entity-type="media" 
        data-entity-uuid="' . $image_entity->uuid() . '">
    </drupal-entity>';

    // Add new format to handle embed entities.
    $this->drupalGet('admin/config/content/formats/add');
    $this->assertResponse(200);
    $this->assertText('Add text format');

    $filter = [
      'name' => 'test_filter',
      'format' => 'test_filter',
      'roles[authenticated]' => TRUE,
      'filters[entity_embed][status]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $filter, $this->t('Save configuration'));
    $this->assertResponse(200);
    $this->assertText('Added text format test_filter.');

    // Create test entity with body which contain embed image.
    $entity = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Title',
      'body' => [
        [
          'value' => $bodyText,
          'format' => 'test_filter',
        ],
      ],
    ]);

    // Configure Content Hub entities export.
    $this->configureContentHubContentTypes('file', ['file']);
    $this->configureContentHubContentTypes('media', ['image']);
    $this->configureContentHubContentTypes('node', ['article']);

    // Check response.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Check CDF format for node.
    $this->assertEqual($output['entities']['0']['type'], 'node');
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());

    // Check CDF format for media.
    $this->assertEqual($output['entities']['1']['type'], 'media');
    $this->assertEqual($output['entities']['1']['uuid'], $image_entity->uuid());

    // Check CDF format for file.
    $this->assertEqual($output['entities']['2']['type'], 'file');
    $this->assertEqual($output['entities']['2']['uuid'], $image_file->uuid());
  }

}
