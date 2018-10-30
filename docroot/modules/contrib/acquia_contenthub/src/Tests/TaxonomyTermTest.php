<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;

/**
 * Test that Acquia Content Hub respects Taxonomy Term.
 *
 * @group acquia_contenthub
 */
class TaxonomyTermTest extends WebTestBase {

  use TaxonomyTestTrait;
  use EntityReferenceTestTrait;

  /**
   * Vocabulary for testing.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * Taxonomy term reference field for testing.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $field;

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'administer acquia content hub',
    'access administration pages',
    'administer taxonomy',
  ];

  /**
   * Modules to enable for this test.
   *
   * @var array
   */
  public static $modules = [
    'acquia_contenthub',
    'user',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Login user and create vocabulary.
    $this->drupalLogin($this->adminUser);
    $this->vocabulary = $this->createVocabulary();

    // Enable Publish options for new vocabulary.
    $this->configureContentHubContentTypes('taxonomy_term', [$this->vocabulary->get('vid')]);
  }

  /**
   * Tests that adding "type" fields that does not contain bundle should work.
   *
   * This tests adds an entity_reference field to a taxonomy term and tests that
   * this is converted to 'array<reference>' pointing to a UUID.
   */
  public function testTaxonomyTermFields() {
    // Create and enable Publish options for target vocabulary.
    $vocabulary_target = $this->createVocabulary();

    // Create a taxonomy term entity reference field that points to an entity
    // from the other vocabulary.
    $field_name = 'type';
    $handler_settings = [
      'target_bundles' => [
        $vocabulary_target->id() => $vocabulary_target->id(),
      ],
    ];

    $this->createEntityReferenceField('taxonomy_term', $this->vocabulary->id(), $field_name, $this->randomString(), 'taxonomy_term', 'default', $handler_settings);
    entity_get_display('taxonomy_term', $this->vocabulary->id(), 'default')
      ->setComponent('type')
      ->save();
    entity_get_form_display('taxonomy_term', $this->vocabulary->id(), 'default')
      ->setComponent($field_name, ['type' => 'entity_reference_autocomplete'])
      ->save();

    // Create term1.
    $term1 = $this->createTerm($this->vocabulary);
    $term2 = $this->createTerm($vocabulary_target);
    $term1->set('type', $term2->id());
    $term1->save();

    $this->configureContentHubContentTypes('taxonomy_term', [$vocabulary_target->get('vid')]);

    // Check CH cdf response.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/taxonomy/term/' . $term1->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Check that main taxonomy_term exists, with 'type' attribute.
    $this->assertTrue(isset($output['entities']['0']['attributes']['type']), 'Type attribute is present.');
    $this->assertEqual($term1->uuid(), $output['entities']['0']['uuid']);
    $this->assertEqual($this->vocabulary->id(), $output['entities']['0']['attributes']['vocabulary']['value']['und']);
    $this->assertEqual('array<reference>', $output['entities']['0']['attributes']['type']['type']);
    $this->assertEqual($term2->uuid(), $output['entities']['0']['attributes']['type']['value']['und'][0]);

    // Check that the taxonomy_term dependency has been included in the CDF.
    $this->assertEqual($term2->uuid(), $output['entities']['1']['uuid']);
    $this->assertEqual($vocabulary_target->id(), $output['entities']['1']['attributes']['vocabulary']['value']['und']);
  }

  /**
   * Test terms in a single and multiple hierarchy.
   */
  public function testTaxonomyTermHierarchy() {
    // Create two taxonomy terms.
    $term1 = $this->createTerm($this->vocabulary);
    $term2 = $this->createTerm($this->vocabulary);

    // Edit $term2, setting $term1 as parent.
    $edit = [];
    $edit['parent[]'] = [$term1->id()];
    $this->drupalPostForm('taxonomy/term/' . $term2->id() . '/edit', $edit, t('Save'));

    // Check CH cdf response.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/taxonomy/term/' . $term2->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);

    // Check cdf format.
    $this->assertTrue(isset($output['entities']['0']['attributes']['parent']), 'Parent field is present.');
    $this->assertTrue(is_array($output['entities']['0']['attributes']['parent']), 'Parent field is array.');

    // Collect data about parent entity.
    $type = $output['entities']['0']['attributes']['parent']['type'];
    $value = $output['entities']['0']['attributes']['parent']['value'];

    // Check parent field format mapping.
    $this->assertEqual($type, 'array<reference>', 'Field type looks correct.');

    // Extract first uuid from parent field.
    $parent_uuid = '';
    if ($value) {
      $parent_lang = array_pop($value);
      $parent_uuid = array_pop($parent_lang);
    }

    // Compare first uuid from response with term1 uuid.
    $this->assertEqual($term1->uuid(), $parent_uuid, 'Parent term looks correct.');

    // Check that the parent is included in the CDF.
    $this->assertEqual($term1->uuid(), $output['entities']['1']['uuid'], 'Parent Entity included in the CDF as a dependency.');
    $this->assertEqual($term1->label(), $output['entities']['1']['attributes']['name']['value']['und'][0], 'Parent Name coincides with expected name.');
  }

}
