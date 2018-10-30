<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Component\Serialization\Json;

/**
 * Test that Acquia Content Hub produces a correct Media Entity CDF.
 *
 * @group acquia_contenthub_no_test
 */
class MediaEntityImageTest extends WebTestBase {

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
    'administer acquia content hub',
    'access administration pages',
    'view media',
    'create media',
    'update media',
    'update any media',
    'delete media',
    'delete any media',
    'access media overview',
  ];

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'acquia_contenthub',
    'user',
    'entity',
    'media_entity',
    'entity_browser',
    'media_entity_image',
    'media_entity_image_ch',
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
   * Tests a Media entity CDF.
   */
  public function testMediaEntityImage() {
    $this->drupalLogin($this->adminUser);

    // Create the Image Media Entity.
    $this->drupalGet('media/add/image');
    $this->assertResponse(200);
    $edit = [
      'name[0][value]' => 'Media Test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $this->configureContentHubContentTypes('media', ['image']);
    $this->setRoleFor($this->adminRole);

    // Render CDF as admin to avoid having to set permissions for media entity.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/media/1', [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['0']['attributes']['bundle']['type'], 'array<string>');
    $this->assertEqual($output['entities']['0']['attributes']['bundle']['value']['en'][0], 'image');
    $this->assertEqual($output['entities']['0']['attributes']['name']['value']['en'][0], 'Media Test');
    $this->assertNotNull($output['entities']['0']['assets'][0]['url']);
    $this->assertNotNull($output['entities']['0']['assets'][0]['replace-token']);
    $thumbnail = $output['entities']['0']['attributes']['thumbnail']['value']['en'][0];
    $thumbnail = Json::decode($thumbnail);
    $this->assertEqual($thumbnail['title'], 'Media Test');
  }

}
