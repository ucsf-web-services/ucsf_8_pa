<?php

namespace Drupal\Tests\applenews\Functional;

use Drupal\applenews_test\Publisher;
use Drupal\node\Entity\Node;
use Drupal\Tests\applenews\Traits\AppleNewsTestTrait;

/**
 * Tests for the Apple News widget.
 *
 * @group applenews
 */
class AppleNewsWidgetTest extends ApplenewsTestBase {
  use AppleNewsTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'applenews_test',
  ];

  /**
   * Apple news field storage.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $applenewsFieldStorage;

  /**
   * Apple news field config.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $applenewsFieldConfig;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'bypass node access',
      'administer nodes',
    ]);

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    // Create a field storage and instance for Apple News on the article bundle.
    [
      $this->applenewsFieldStorage,
      $this->applenewsFieldConfig,
    ] = $this->createAppleNewsField('node', 'article');

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = \Drupal::service('entity_display.repository');
    $entity_display_repository
      ->getFormDisplay('node', 'article')
      ->setComponent($this->applenewsFieldStorage->getName(), [
        'type' => 'applenews_default',
      ])
      ->save();
  }

  /**
   * Test output of Apple News widget when no channels or templates.
   */
  public function testNotConfigured() {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/article');
    $assert_session->pageTextContains('Apple News settings');
    $assert_session->pageTextContains('No channels available');
  }

  /**
   * Test Apple News widget when only channels, no templates are configured.
   */
  public function testOnlyChannelConfigured() {
    // Create a default Apple News channel.
    $this->createApplenewsChannel();
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/article');
    $assert_session->pageTextContains('Apple News settings');
    $assert_session->pageTextContains('No templates available');
  }

  /**
   * Test Apple News widget when only templates, no channels are configured.
   */
  public function testOnlyTemplateConfigured() {
    // Create an Apple News template.
    $template = $this->createApplenewsTemplate();

    $assert_session = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/article');
    $assert_session->pageTextContains('Apple News settings');
    $assert_session->pageTextContains('No channels available');
    $assert_session->pageTextNotContains('Download the Apple News generated document');

    // Create an article and then edit it, we should see download links now.
    $node = Node::create([
      'title' => $this->randomString(),
      'type' => 'article',
    ]);
    $node->save();
    $this->drupalGet('node/' . $node->id() . '/edit');
    $assert_session->pageTextContains('Apple News settings');
    $assert_session->pageTextContains('No channels available');
    $assert_session->pageTextContains('Download the Apple News generated document');
    $assert_session->pageTextContains($template->label());
    $this->assertRaw(sprintf('admin/config/services/applenews/node/%s/%s/%s/download', $node->id(), $node->getLoadedRevisionId(), $template->id()));
  }

  /**
   * Test the apple news widget with full configured template and channel.
   */
  public function testFullyConfigured() {
    // Setup a default channel and template.
    $channel = $this->createApplenewsChannel();
    $sections = $channel->getSections();
    $section_ids = array_keys($sections);
    $template = $this->createApplenewsTemplate();

    $assert_session = $this->assertSession();

    // Make sure we see the expected fields that are part of the Apple News
    // widget.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/article');
    $field_name = $this->applenewsFieldStorage->getName();
    $assert_session->fieldExists(sprintf('%s[0][template]', $field_name));
    $assert_session->optionExists(sprintf('%s[0][template]', $field_name), $template->id());
    $assert_session->fieldExists(sprintf('%s[0][status]', $field_name));
    $assert_session->fieldExists(sprintf('%s[0][channels][%s]', $field_name, $channel->getChannelId()));
    $assert_session->fieldExists(sprintf('%s[0][sections][%s-section-%s]', $field_name, $channel->getChannelId(), reset($section_ids)));
    $assert_session->fieldExists(sprintf('%s[0][is_preview]', $field_name));

    // Create a node set for publish to Apple News, but without channel or
    // section selected.
    $edit = [
      'title[0][value]' => $this->randomMachineName(8),
      'body[0][value]' => $this->randomMachineName(16),
      sprintf('%s[0][status]', $field_name) => '1',
      sprintf('%s[0][template]', $field_name) => $template->id(),
    ];
    $this->submitForm($edit, t('Save'));
    $assert_session->pageTextContains(t('Apple News: At least one channel and a section should be selected to publish.'));

    // Create a node set for publish to Apple News, but without a section
    // selected.
    $edit[sprintf('%s[0][channels][%s]', $field_name, $channel->getChannelId())] = $channel->getChannelId();
    $this->submitForm($edit, t('Save'));
    $assert_session->pageTextContains(t('Apple News: At least one section should be selected to publish.'));

    // Create a node set for publish to Apple News with channel and section
    // selected. Setup a mock response for publishing to Apple News.
    Publisher::setResponse(file_get_contents(__DIR__ . '/../../fixtures/article_create_response.json'));
    $edit[sprintf('%s[0][sections][%s-section-%s]', $field_name, $channel->getChannelId(), reset($section_ids))] = '1';
    $this->submitForm($edit, t('Save'));
    $assert_session->pageTextContains(t('@post @title has been created.', [
      '@post' => 'Article',
      '@title' => $edit['title[0][value]'],
    ]));

    // Assert that the Apple News article is saved to the database.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $storage = \Drupal::entityTypeManager()->getStorage('applenews_article');
    $applenews_article = $storage->loadByProperties(['entity_id' => $node->id()]);
    $this->assertNotEmpty($applenews_article);

    // Assert that we see the expected widget state when we edit the node.
    $this->drupalGet(sprintf('node/%s/edit', $node->id()));
    $assert_session->fieldValueEquals(sprintf('%s[0][status]', $field_name), '1');
    $assert_session->fieldValueEquals(sprintf('%s[0][template]', $field_name), $template->id());
    $assert_session->fieldValueEquals(sprintf('%s[0][channels][%s]', $field_name, $channel->getChannelId()), '1');
    $assert_session->fieldValueEquals(sprintf('%s[0][sections][%s-section-%s]', $field_name, $channel->getChannelId(), reset($section_ids)), '1');
    // Check for the Delete link to delete the article from Apple News.
    $assert_session->linkByHrefExists(sprintf('/admin/config/services/applenews/remote/node/%s/delete', $node->id()));
    // Check for the Apple News URL. This is the shareUrl in the fixture used
    // above.
    $assert_session->linkByHrefExists('https://apple.news/ArRPpLPE9QXu3sehS0rvxvA');
  }

}
