<?php

namespace Drupal\acquia_contenthub\Tests;

/**
 * Test Acquia Content Hub node form.
 *
 * @group acquia_contenthub
 */
class NodeFormTest extends WebTestBase {

  use ContentHubEntityTrait;

  /**
   * The sample article we generate.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $article;

  /**
   * Configure content hub node form.
   */
  public function testNodeForm() {
    $this->drupalLogin($this->adminUser);
    $this->article = $this->drupalCreateNode(['type' => 'article']);

    // A normal node should not have Acquia Content Hub settings.
    $this->drupalGet('node/' . $this->article->id() . '/edit');
    $this->assertNoText(t('Acquia Content Hub settings'));

    // Convert the node into a Content Hub node.
    $this->convertToContentHubEntity($this->article);

    // A Content Hub node should have Acquia Content Hub settings.
    // Form should have option, and default to "enabled".
    $node_edit_url = 'node/' . $this->article->id() . '/edit';
    $this->drupalGet($node_edit_url);
    $this->assertText(t('Acquia Content Hub settings'));
    $this->assertFieldChecked('edit-acquia-contenthub-auto-update', 'Automatic updates is enabled by default');

    // Disable automatic update.
    $edit = [];
    $edit['acquia_contenthub[auto_update]'] = FALSE;
    $this->drupalPostForm($node_edit_url, $edit, t('Save'));

    // Option should now set to "disabled".
    $this->drupalGet($node_edit_url);
    $this->assertNoFieldChecked('edit-acquia-contenthub-auto-update', 'Automatic updates is enabled by default');
  }

  /**
   * Configure content hub node form, auto setting to "having local change".
   */
  public function testNodeFormIntroduceLocalChange() {
    $this->drupalLogin($this->adminUser);
    $this->article = $this->drupalCreateNode(['type' => 'article']);

    // Convert the node into a Content Hub node.
    $this->convertToContentHubEntity($this->article);

    // A Content Hub node should have Acquia Content Hub settings.
    // Form should have option, and default to "enabled".
    $node_edit_url = 'node/' . $this->article->id() . '/edit';
    $this->drupalGet($node_edit_url);
    $this->assertNoText(t('This content has been modified.'));
    $this->assertFieldChecked('edit-acquia-contenthub-auto-update', 'Automatic updates is enabled by default');

    // Don't edit anything, save.
    $this->drupalPostForm($node_edit_url, [], t('Save'));

    // Option should still be set to "enabled".
    $this->drupalGet($node_edit_url);
    $this->assertNoText(t('This content has been modified.'));
    $this->assertFieldChecked('edit-acquia-contenthub-auto-update', 'Automatic updates is enabled by default');

    // Edit title.
    $edit = [];
    $edit['title[0][value]'] = 'my new title';
    $this->drupalPostForm($node_edit_url, $edit, t('Save'));

    // Option should now set to "disabled, as having local changes".
    $this->drupalGet($node_edit_url);
    $this->assertText(t('This content has been modified.'));
    $this->assertNoFieldChecked('edit-acquia-contenthub-auto-update', 'Automatic updates is enabled by default');
  }

}
