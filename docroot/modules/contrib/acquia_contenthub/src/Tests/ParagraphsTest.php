<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\simpletest\WebTestBase as SimpletestWebTestBase;
use Drupal\Component\Uuid\Uuid;
use Drupal\Component\Serialization\Json;

/**
 * Tests paragraphs support in Acquia Content Hub module.
 *
 * @group acquia_contenthub
 */
class ParagraphsTest extends SimpletestWebTestBase {

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'administer nodes',
    'bypass node access',
    'administer acquia content hub',
    'access administration pages',
  ];

  /**
   * An admin user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'node',
    'paragraphs',
    'acquia_contenthub',
    'ch_node_with_paragraphs',
  ];

  /**
   * A node to test.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create the users used for the tests.
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
  }

  /**
   * Configures Entity Settings for Node and Paragraphs.
   */
  protected function setContentHubEntitySettings() {
    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $edit = [
      'entities[node][ch_node_with_paragraphs][enable_index]' => 1,
      'entities[node][ch_node_with_paragraphs][enable_viewmodes]' => 1,
      'entities[paragraph][addresses][enable_index]' => 1,
      'entities[paragraph][client_data][enable_index]' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));
    $this->assertResponse(200);
  }

  /**
   * Tests acquia_contenthub_cdf format for paragraphs entities inside a node.
   */
  public function testParagraphsCdf() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('node/add/ch_node_with_paragraphs');
    $this->drupalPostAjaxForm(NULL, [], 'field_client_data_client_data_add_more');
    $this->drupalPostAjaxForm(NULL, [], 'field_client_data_0_subform_field_client_location_addresses_add_more');
    $this->drupalPostAjaxForm(NULL, [], 'field_client_data_0_subform_field_client_location_addresses_add_more');
    $title = 'Page_title';
    $edit = [
      'title[0][value]' => $title,
      'field_client_data[0][subform][field_client_name][0][value]' => 'Client Name',
      'field_client_data[0][subform][field_client_tags][0][target_id]' => 'test_term',
      'field_client_data[0][subform][field_client_email][0][value]' => 'test@test.com',
      'field_client_data[0][subform][field_client_location][0][subform][field_address_type]' => 'Work Address',
      'field_client_data[0][subform][field_client_location][0][subform][field_address][0][value]' => 'Test Address',
      'field_client_data[0][subform][field_client_location][1][subform][field_address_type]' => 'Shipping Address',
      'field_client_data[0][subform][field_client_location][1][subform][field_address][0][value]' => 'Another Test Address',
    ];

    // 8.3 has the label 'Save and publish'.
    if ((floatval(\Drupal::VERSION) <= 8.3)) {
      $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    }
    else {
      $this->drupalPostForm(NULL, $edit, t('Save'));
    }
    $this->node = $this->getNodeByTitle($title);

    // Check format acquia_contenthub_cdf.
    $this->setContentHubEntitySettings();
    $this->checkCdfOutput();
  }

  /**
   * Ensures the CDF output is what we expect it to be.
   */
  public function checkCdfOutput() {
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $this->node->getEntityTypeId() . '/' . $this->node->id());
    $this->assertResponse(200);

    // Obtaining paragraphs' IDs and UUIDs to verify later.
    $paragraphs = $this->node->get('field_client_data')->getValue();
    $paragraphs_id_0 = $paragraphs[0]['target_id'];
    $paragraphs_uuid_0 = $output['entities']['0']['attributes']['field_client_data']['value']['en']['0'];

    // Check that paragraphs are included in the CDF.
    $this->assertTrue(isset($output['entities']['0']['metadata']), 'Metadata is present');
    $this->assertTrue(isset($output['entities']['0']['metadata']['view_modes']['default']), t('View mode %view_mode is present', ['%view_mode' => 'default']));
    $this->assertEqual($output['entities']['0']['attributes']['field_client_data']['type'], 'array<reference>');
    $this->assertTrue(Uuid::isValid($paragraphs_uuid_0));

    $output = $this->drupalGetCdf('acquia-contenthub-cdf/entity/paragraph/' . $paragraphs_id_0, [
      'query' => [
        'entity_type' => 'paragraph',
        'entity_id' => $paragraphs_id_0,
      ],
    ]);
    $this->assertResponse(200);

    // Obtaining nested paragraph's IDs and UUIDs to verify later.
    $client_data = \Drupal::entityTypeManager()->getStorage("paragraph")->load($paragraphs_id_0);
    $field_client_location = $client_data->get('field_client_location')->getValue();
    $paragraphs_id_1 = $field_client_location[0]['target_id'];
    $paragraphs_id_2 = $field_client_location[1]['target_id'];
    $paragraphs_uuid_1 = $output['entities']['0']['attributes']['field_client_location']['value']['en']['0'];
    $paragraphs_uuid_2 = $output['entities']['0']['attributes']['field_client_location']['value']['en']['1'];

    // Check that paragraphs have the correct values in the CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $paragraphs_uuid_0);
    $this->assertEqual($output['entities']['0']['type'], 'paragraph');
    $this->assertEqual($output['entities']['0']['attributes']['type']['value']['en'], 'client_data');
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['type'], 'array<string>');
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['value']['en']['0'], 'node');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['type'], 'string');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['value']['en'], $this->node->uuid());
    $this->assertEqual($output['entities']['0']['attributes']['parent_field_name']['value']['en']['0'], 'field_client_data');
    $this->assertEqual($output['entities']['0']['attributes']['field_client_email']['value']['en']['0'], 'test@test.com');
    $this->assertEqual($output['entities']['0']['attributes']['field_client_name']['value']['en']['0'], 'Client Name');
    $this->assertTrue(Uuid::isValid($output['entities']['0']['attributes']['field_client_tags']['value']['en']['0']));
    $this->assertTrue(Uuid::isValid($paragraphs_uuid_1));
    $this->assertTrue(Uuid::isValid($paragraphs_uuid_2));

    $output = $this->drupalGetCdf('acquia-contenthub-cdf/entity/paragraph/' . $paragraphs_id_1, [
      'query' => [
        'entity_type' => 'paragraph',
        'entity_id' => $paragraphs_id_1,
      ],
    ]);
    $this->assertResponse(200);

    // Check that paragraphs have the correct values in the CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $paragraphs_uuid_1);
    $this->assertEqual($output['entities']['0']['type'], 'paragraph');
    $this->assertEqual($output['entities']['0']['attributes']['type']['value']['en'], 'addresses');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['type'], 'string');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['value']['en'], $paragraphs_uuid_0);
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['value']['en']['0'], 'paragraph');
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['type'], 'array<string>');
    $this->assertEqual($output['entities']['0']['attributes']['parent_field_name']['value']['en']['0'], 'field_client_location');
    $this->assertEqual($output['entities']['0']['attributes']['field_address']['value']['en']['0'], 'Test Address');
    $this->assertEqual($output['entities']['0']['attributes']['field_address_type']['value']['en']['0'], 'Work Address');

    $output = $this->drupalGetCdf('acquia-contenthub-cdf/entity/paragraph/' . $paragraphs_id_2, [
      'query' => [
        'entity_type' => 'paragraph',
        'entity_id' => $paragraphs_id_2,
      ],
    ]);
    $this->assertResponse(200);

    // Check that paragraphs have the correct values in the CDF.
    $this->assertEqual($output['entities']['0']['uuid'], $paragraphs_uuid_2);
    $this->assertEqual($output['entities']['0']['type'], 'paragraph');
    $this->assertEqual($output['entities']['0']['attributes']['type']['value']['en'], 'addresses');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['type'], 'string');
    $this->assertEqual($output['entities']['0']['attributes']['parent_uuid']['value']['en'], $paragraphs_uuid_0);
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['value']['en']['0'], 'paragraph');
    $this->assertEqual($output['entities']['0']['attributes']['parent_type']['type'], 'array<string>');
    $this->assertEqual($output['entities']['0']['attributes']['parent_field_name']['value']['en']['0'], 'field_client_location');
    $this->assertEqual($output['entities']['0']['attributes']['field_address']['value']['en']['0'], 'Another Test Address');
    $this->assertEqual($output['entities']['0']['attributes']['field_address_type']['value']['en']['0'], 'Shipping Address');
  }

  /**
   * Retrieves a Drupal or an absolute CDF path and JSON decodes the result.
   *
   * @param \Drupal\Core\Url|string $path
   *   Drupal path or URL to request AJAX from.
   * @param array $options
   *   Array of URL options.
   * @param array $headers
   *   Array of headers. Eg array('Accept: application/vnd.drupal-ajax').
   *
   * @return array
   *   Decoded json.
   */
  protected function drupalGetCdf($path, array $options = [], array $headers = []) {
    return Json::decode($this->drupalGetWithFormat($path, 'acquia_contenthub_cdf', $options, $headers));
  }

}
