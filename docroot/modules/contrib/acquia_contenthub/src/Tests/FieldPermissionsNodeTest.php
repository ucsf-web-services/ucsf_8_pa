<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Component\Serialization\Json;
use Drupal\field_permissions\Plugin\FieldPermissionTypeInterface;

/**
 * Test that Acquia Content Hub respects Field Permissions.
 *
 * @group acquia_contenthub_no_test
 */
class FieldPermissionsNodeTest extends WebTestBase {

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
    'administer node fields',
    'administer field permissions',
    'access private fields',
  ];

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
    'acquia_contenthub',
    'field',
    'field_ui',
    'user',
    'field_permissions',
  ];

  /**
   * Configure content hub node form.
   */
  public function testFieldPermissions() {
    $this->adminRole = $this->createAdminRole('administrator', 'Administrator');
    $this->drupalLogin($this->adminUser);

    // Create Article Node.
    $entity = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test Title',
      'body'      => [[
        'value' => 'Test Body',
        'format' => filter_default_format(),
      ],
      ],
    ]);

    $this->configureContentHubContentTypes('node', ['article']);

    // Rendering the CDF as anonymous, we can see the "Body" Field.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], 'Test Title');

    // Obtaining body.
    $json_body = $output['entities']['0']['attributes']['body']['value']['en'][0];
    $body = Json::decode($json_body);
    $this->assertEqual($body['value'], 'Test Body');

    // Now modify article "Body" Field to have private access (only admin and
    // owner can view/edit the field).
    $this->drupalGet('admin/structure/types/manage/article/fields/node.article.body');
    $this->assertResponse(200);
    $this->assertText('Field visibility and permissions');

    $perm = FieldPermissionTypeInterface::ACCESS_PRIVATE;
    $edit = ['type' => $perm];
    $this->drupalPostForm(NULL, $edit, $this->t('Save settings'));
    $this->assertResponse(200);
    $this->assertText('Saved Body configuration');

    // Check field permissions settings for Body.
    $this->drupalGet('admin/structure/types/manage/article/fields/node.article.body');
    $this->assertResponse(200);
    $this->assertFieldChecked('edit-type-private', 'Field visibility and permissions should be Private.');

    // Clear cache because configuration was changed.
    drupal_flush_all_caches();

    // Rendering CDF as anonymous, we cannot see the "Body" field.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], 'Test Title');
    $this->assertTrue(empty($output['entities']['0']['attributes']['body']), 'Body Field not present in CDF.');

    $this->setRoleFor($this->adminRole);

    // Rendering the CDF using the admin role, we can see the "Body" Field.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], 'Test Title');

    // Obtaining body.
    $json_body = $output['entities']['0']['attributes']['body']['value']['en'][0];
    $body = Json::decode($json_body);
    $this->assertEqual($body['value'], 'Test Body');
  }

}
