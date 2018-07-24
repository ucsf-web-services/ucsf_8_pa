<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Component\Serialization\Json;

/**
 * Test that Acquia Content Hub respects Field Сlearing.
 *
 * @group acquia_contenthub
 */
class FieldClearingNodeTest extends WebTestBase {

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
  ];

  /**
   * Configure content hub node form.
   */
  public function testFieldClearing() {
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

    // Rendering the CDF, we can see the "Body" Field.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Check the base fields in CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], 'Test Title');

    // Check the body in CDF.
    $json_body = $output['entities']['0']['attributes']['body']['value']['en'][0];
    $body = Json::decode($json_body);
    $this->assertEqual($body['value'], 'Test Body');

    // Now modify article "Body" Field to have clear it.
    $node_edit_url = 'node/' . $entity->id() . '/edit';
    $edit = [];
    $edit['body[0][value]'] = '';
    $this->drupalPostForm($node_edit_url, $edit, t('Save'));

    // Rendering the CDF, we can see the "Body" Field.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Check the base fields in CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['title']['value']['en'], 'Test Title');

    // Check the body in CDF.
    $json_body = $output['entities']['0']['attributes']['body']['value']['en'][0];
    $body = Json::decode($json_body);
    $this->assertEqual($body['value'], NULL);
  }

}
