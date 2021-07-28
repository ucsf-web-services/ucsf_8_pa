<?php

namespace Drupal\Tests\applenews\Kernel;

use Drupal\applenews_test\Publisher;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\applenews\Traits\AppleNewsTestTrait;
use Drupal\user\Entity\User;

/**
 * Tests the ApplenewsManager.
 *
 * @group applenews
 *
 * @coversDefaultClass \Drupal\applenews\ApplenewsManager
 */
class ApplenewsManagerTest extends KernelTestBase {
  use AppleNewsTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field',
    'serialization',
    'node',
    'user',
    'applenews',
    'applenews_test',
  ];

  /**
   * Name of the test user we are using.
   *
   * @var string
   */
  protected $userName;

  /**
   * User entity we are testing with.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * Apple news field storage.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $applenewsFieldStorage;

  /**
   * Apple News channel for testing.
   *
   * @var \Drupal\applenews\Entity\ApplenewsChannel
   */
  protected $applenewsChannel;

  /**
   * Apple News template for testing.
   *
   * @var \Drupal\applenews\Entity\ApplenewsTemplate
   */
  protected $applenewsTemplate;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('system', 'sequences');
    $this->installSchema('node', 'node_access');
    $this->installConfig(['system', 'field']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('applenews_channel');
    $this->installEntitySchema('applenews_article');

    // Create a user to use for testing.
    $this->userName = $this->randomMachineName();
    $account = User::create(['name' => $this->userName, 'status' => 1]);
    $account->enforceIsNew();
    $account->save();
    $this->account = $account;

    // Create the node bundles required for testing.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    // Create a field storage and instance for Apple News on the article bundle.
    [$this->applenewsFieldStorage] = $this->createAppleNewsField('node', 'article');

    $this->applenewsChannel = $this->createApplenewsChannel();
    $this->applenewsTemplate = $this->createApplenewsTemplate();
  }

  /**
   * Test Apple News article is removed after delete.
   *
   * For a node that is published to Apple News, and then deleted, the
   * corresponding Apple News article should be deleted when the article already
   * was deleted in the Apple News UI.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testDeleteNotFound() {
    // Create the node.
    $node = $this->createNodePublishedToAppleNews($this->applenewsChannel, $this->applenewsTemplate, $this->applenewsFieldStorage);
    // Mock a successful create response from the Apple News Publisher API.
    Publisher::setResponse(file_get_contents(__DIR__ . '/../../fixtures/article_create_response.json'));
    $node->save();

    // Assert that the Apple News article is saved to the database.
    $this->assertAppleNewsArticleExistsForEntity($node);

    // Mock a not found response from Apple News for the delete attempt. This
    // signals an editor already deleted the article in the Apple News UI.
    Publisher::setResponse(file_get_contents(__DIR__ . '/../../fixtures/delete_not_found_response.json'));
    $node->delete();

    // Assert that the Apple News article is gone from the database.
    $this->assertAppleNewsArticleNotExistsForEntity($node);
  }

  /**
   * Test Apple News article removed after unpublish.
   *
   * For a node that is published to Apple News, and then marked as removed
   * using the status flag, that Apple News article should be removed even when
   * the article has already been removed upstream.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testDeleteNotFoundUnpublish() {
    // Create the node.
    $node = $this->createNodePublishedToAppleNews($this->applenewsChannel, $this->applenewsTemplate, $this->applenewsFieldStorage);
    // Mock a successful create response from the Apple News Publisher API.
    Publisher::setResponse(file_get_contents(__DIR__ . '/../../fixtures/article_create_response.json'));
    $node->save();

    // Mock a not found response from Apple News for the delete attempt. This
    // signals an editor already deleted the article in the Apple News UI.
    Publisher::setResponse(file_get_contents(__DIR__ . '/../../fixtures/delete_not_found_response.json'));
    $node->{$this->applenewsFieldStorage->getName()}->status = '0';
    $node->save();

    // Assert that the Apple News article is gone from the database.
    $this->assertAppleNewsArticleNotExistsForEntity($node);
  }

}
