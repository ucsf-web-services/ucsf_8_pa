<?php

namespace Drupal\Tests\entity_clone\Functional;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * Test whether cloning an entity also clones its created date.
 *
 * @group entity_clone
 */
class EntityCloneContentCreatedDate extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_clone', 'node'];

  /**
   * The user that we will use to execute the functional test.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $sutUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->sutUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'clone node entity',
    ]);
  }

  /**
   * Test that an entity's created date is not cloned.
   */
  public function testCreatedDateIsNotCloned() {
    // Log in.
    $this->drupalLogin($this->sutUser);

    // Create the original node.
    $originalNodeCreatedDate = new \DateTimeImmutable('1 year 1 month 1 day ago');
    $originalNode = $this->drupalCreateNode([
      'created' => $originalNodeCreatedDate->getTimestamp(),
    ]);
    $this->assertEquals($originalNodeCreatedDate->getTimestamp(), $originalNode->getCreatedTime());

    // Clone the node.
    $this->drupalGet(Url::fromRoute('entity.node.clone_form', [
      'node' => $originalNode->id(),
    ])->toString());
    $this->getSession()->getPage()->pressButton('Clone');

    // Find the cloned node.
    $originalNodeClones = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => sprintf('%s - Cloned', $originalNode->label()),
      ]);
    $this->assertGreaterThanOrEqual(1, count($originalNodeClones));
    $clonedNode = reset($originalNodeClones);

    // Validate the cloned node's created time is more recent than the original
    // node.
    $this->assertNotEquals($originalNode->getCreatedTime(), $clonedNode->getCreatedTime());
    $this->assertGreaterThanOrEqual($originalNode->getCreatedTime(), $clonedNode->getCreatedTime());
  }

}
