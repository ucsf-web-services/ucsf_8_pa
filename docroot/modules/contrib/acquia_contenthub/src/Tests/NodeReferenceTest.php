<?php

namespace Drupal\acquia_contenthub\Tests;

/**
 * Test Acquia Content Hub node reference.
 *
 * @group acquia_contenthub_no_test
 */
class NodeReferenceTest extends WebTestBase {

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'node',
    'acquia_contenthub',
    'node_with_references',
  ];

  /**
   * Configure content hub node form.
   */
  public function testNodeReferences() {
    $this->drupalLogin($this->adminUser);
    $entity1 = $this->drupalCreateNode([
      'type' => 'node_with_references',
      'title' => 'Title 1',
    ]);
    $entity2 = $this->drupalCreateNode([
      'type' => 'node_with_references',
      'title' => 'Title 2',
      'field_reference' => [
        [
          'target_id' => $entity1->id(),
        ],
      ],
    ]);
    $entity3 = $this->drupalCreateNode([
      'type' => 'node_with_references',
      'title' => 'Title 3',
      'field_reference' => [
        [
          'target_id' => $entity2->id(),
        ],
      ],
    ]);
    $entity4 = $this->drupalCreateNode([
      'type' => 'node_with_references',
      'title' => 'Title 4',
      'field_reference' => [
        [
          'target_id' => $entity3->id(),
        ],
      ],
    ]);
    $entity5 = $this->drupalCreateNode([
      'type' => 'node_with_references',
      'title' => 'Title 5',
      'field_reference' => [
        [
          'target_id' => $entity4->id(),
        ],
      ],
    ]);

    $this->configureContentHubContentTypes('node', ['node_with_references']);

    // The CDF Output for entity 5 should not show entity 1 due to the
    // maximum default dependency depth of 3.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity5->getEntityTypeId() . '/' . $entity5->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['1']['uuid'], $entity4->uuid());
    $this->assertEqual($output['entities']['2']['uuid'], $entity3->uuid());
    $this->assertEqual($output['entities']['3']['uuid'], $entity2->uuid());
    $this->assertFalse($this->findEntityUuid($entity1->uuid(), $output), $entity1->uuid() . ' not found.');

    // The CDF Output for entity 4 should show entity 1 because it includes that
    // entity by using maximum dependency depth of 3.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity4->getEntityTypeId() . '/' . $entity4->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['1']['uuid'], $entity3->uuid());
    $this->assertEqual($output['entities']['2']['uuid'], $entity2->uuid());
    $this->assertEqual($output['entities']['3']['uuid'], $entity1->uuid());

    // Increasing dependency depth to 4.
    $config = \Drupal::configFactory()->getEditable('acquia_contenthub.entity_config');
    $config->set('dependency_depth', 4);
    $config->save();
    drupal_flush_all_caches();

    // The CDF Output for entity 5 should now show entity 1 too due to the
    // maximum dependency depth of 4.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity5->getEntityTypeId() . '/' . $entity5->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['1']['uuid'], $entity4->uuid());
    $this->assertEqual($output['entities']['2']['uuid'], $entity3->uuid());
    $this->assertEqual($output['entities']['3']['uuid'], $entity2->uuid());
    $this->assertEqual($output['entities']['4']['uuid'], $entity1->uuid());

    // Decreasing dependency depth to 2.
    $config->set('dependency_depth', 2);
    $config->save();
    drupal_flush_all_caches();

    // The CDF Output for entity 5 should not show entity 1 nor entity 2 due
    // to the maximum dependency depth of 2.
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity5->getEntityTypeId() . '/' . $entity5->id(), [
      'query' => [
        'include_references' => 'true',
      ],
    ]);
    $this->assertResponse(200);
    $this->assertEqual($output['entities']['1']['uuid'], $entity4->uuid());
    $this->assertEqual($output['entities']['2']['uuid'], $entity3->uuid());
    $this->assertFalse($this->findEntityUuid($entity2->uuid(), $output), $entity2->uuid() . ' not found.');
    $this->assertFalse($this->findEntityUuid($entity1->uuid(), $output), $entity1->uuid() . ' not found.');
  }

  /**
   * Finds an entity uuid in the output json array.
   *
   * @param string $uuid
   *   The Entity UUID to search.
   * @param array $entities
   *   The array of entities converted from the json output.
   *
   * @return bool
   *   TRUE if the Entity UUID was found, FALSE otherwise.
   */
  protected function findEntityUuid($uuid, array $entities) {
    foreach ($entities['entities'] as $entity) {
      if ($entity['uuid'] == $uuid) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
