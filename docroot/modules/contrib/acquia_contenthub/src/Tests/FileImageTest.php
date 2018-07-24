<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Component\Serialization\Json;
use Drupal\file\Entity\File;

/**
 * Test that Acquia Content Hub produces a correct CDF of node with image file.
 *
 * @group acquia_contenthub_no_test
 */
class FileImageTest extends WebTestBase {

  use ImageFieldCreationTrait;

  /**
   * An image file path for uploading.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * Admin role.
   *
   * @var string
   */
  protected $adminRole;

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'access content',
    'access administration pages',
    'administer site configuration',
    'administer content types',
    'administer node fields',
    'administer nodes',
    'administer acquia content hub',
    'access administration pages',
  ];

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'node',
    'acquia_contenthub',
    'menu_ui',
    'user',
    'entity',
    'file',
    'image',
    'field_ui',
    'file_entity',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create the users used for the tests.
    $this->adminRole = $this->createAdminRole('administrator', 'Administrator');
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);

    // Create a field image for test purposes.
    $field_name = 'field_image_test';
    $min_resolution = 50;
    $max_resolution = 100;
    $field_settings = [
      'max_resolution' => $max_resolution . 'x' . $max_resolution,
      'min_resolution' => $min_resolution . 'x' . $min_resolution,
      'alt_field' => 0,
    ];
    $this->createImageField($field_name, 'article', [], $field_settings, [], [], 'Image test on [site:name]');
  }

  /**
   * Tests entity CDFs.
   */
  public function testFileImage() {
    $this->drupalLogin($this->adminUser);

    // Create the File Image.
    $image_files = $this->drupalGetTestFiles('image');
    $this->image = File::create((array) current($image_files));
    $this->image->save();

    // Configure Content Hub to export article nodes and image files.
    $this->configureContentHubContentTypes('node', ['article']);
    $this->configureContentHubContentTypes('file', ['image']);
    $this->setRoleFor($this->adminRole);

    // Verify that we can access the image file as a CDF.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/file/' . $this->image->id(), [
      'query' => [
        'entity_type' => 'file',
        'entity_id' => $this->image->id(),
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['0']['uuid'], $this->image->uuid());
    $this->assertEqual($output['entities']['0']['type'], $this->image->getEntityTypeId());
    $this->assertEqual($output['entities']['0']['attributes']['filename']['value']['en'][0], $this->image->get('filename')->getValue()[0]['value']);

    // Test that the file URL generated for a file CDF is ABSOLUTE.
    $file_url = $this->image->get('uri')->getValue()[0]['value'];
    $this->assertEqual($output['entities']['0']['attributes']['url']['value']['en'], file_create_url($file_url));

    // Create a node with a file image.
    $entity = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Title Test',
      'field_image_test' => [
        [
          'target_id' => $this->image->id(),
        ],
      ],
    ]);

    // Render CDF as admin to avoid having to set permissions for entities.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/node/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Verifying node fields in the CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['type']['type'], 'string');
    $this->assertEqual($output['entities']['0']['attributes']['type']['value']['en'], 'article');
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], $entity->label());
    $this->assertNotNull($output['entities']['0']['assets'][0]['url']);
    $this->assertNotNull($output['entities']['0']['assets'][0]['replace-token']);
    $field_image_test = $output['entities']['0']['attributes']['field_image_test']['value']['en'][0];
    $field_image_decoded = Json::decode($field_image_test);
    $this->assertEqual($field_image_decoded['target_uuid'], '[' . $this->image->uuid() . ']');

    // Verifying file image fields in the CDF.
    $this->assertEqual($output['entities']['1']['uuid'], $this->image->uuid());
    $this->assertEqual($output['entities']['1']['type'], $this->image->getEntityTypeId());
    $this->assertEqual($output['entities']['1']['attributes']['filename']['value']['en'][0], $this->image->get('filename')->getValue()[0]['value']);
    $this->assertEqual($output['entities']['1']['attributes']['url']['value']['en'], file_create_url($file_url));
  }

}
