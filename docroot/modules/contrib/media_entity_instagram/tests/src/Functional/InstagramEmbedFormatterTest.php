<?php

namespace Drupal\Tests\media_entity_instagram\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests for Instagram embed formatter.
 *
 * @group media_entity_instagram
 */
class InstagramEmbedFormatterTest extends BrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'media_entity_instagram',
    'media',
    'node',
    'field_ui',
    'views_ui',
    'block',
  ];

  /**
   * The test media type.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $testBundle;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    \Drupal::configFactory()
      ->getEditable('media.settings')
      ->set('standalone_url', TRUE)
      ->save(TRUE);
    \Drupal::service('router.builder')->rebuild();
    $this->testBundle = $this->createMediaType('instagram', ['id' => 'instagram']);
    $this->drupalPlaceBlock('local_actions_block');
    $account = $this->drupalCreateUser([
      'administer media',
      'administer media types',
      'administer media fields',
      'administer media form display',
      'administer media display',
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      // Other permissions.
      'administer views',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Tests adding and editing an instagram embed formatter.
   */
  public function testFieldFormatter() {
    // Test and create one media bundle.
    $bundle = $this->testBundle;

    $assert = $this->assertSession();

    // Assert that the media bundle has the expected values before proceeding.
    $this->drupalGet('admin/structure/media/manage/' . $bundle->id());
    $assert->fieldValueEquals('label', $bundle->label());
    $assert->fieldValueEquals('source', 'instagram');
    $assert->pageTextContains('Instagram field is used to store the essential information about the media item.');
    $assert->buttonExists('Save')->press();
    $assert->pageTextContains('The media type ' . $bundle->label() . ' has been updated.');

    \Drupal::service('entity_display.repository')->getViewDisplay('media', $bundle->id(), 'default')
      ->setComponent('field_media_instagram', [
        'label' => 'above',
        'type' => 'instagram_embed',
        'settings' => [
          'hidecaption' => FALSE,
        ],
      ])
      ->save();

    // Set and save the settings of the new field.
    $this->drupalGet('admin/structure/media/manage/' . $bundle->id() . '/display');
    $assert->pageTextContains('Caption: Visible');

    // Create and save the media with an instagram media code.
    $this->drupalGet('media/add/' . $bundle->id());

    $assert->fieldExists('Name')->setValue('My test instagram');
    // Example instagram from https://www.instagram.com/developer/embedding
    $assert->fieldExists('Instagram')->setValue('https://www.instagram.com/p/B2huuS8AQVq/');
    $assert->buttonExists('Save')->press();

    // Assert that the media has been successfully saved.
    $assert->pageTextContains('My test instagram');
    $assert->pageTextContains('Instagram');

    // Assert that the formatter exists on this page and that it has absolute
    // size.
    $assert->elementExists('css', 'blockquote');
    $assert->responseContains('platform.instagram.com/en_US/embeds.js');
  }

}
