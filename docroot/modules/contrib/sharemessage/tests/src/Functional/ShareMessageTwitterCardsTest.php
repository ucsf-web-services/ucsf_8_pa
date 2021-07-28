<?php

namespace Drupal\Tests\sharemessage\Functional;

/**
 * Verifies the output of ShareMessage twitter card meta tags.
 *
 * @group sharemessage
 */
class ShareMessageTwitterCardsTest extends ShareMessageTestBase {

  /**
   * Checks whether twitter card meta tags get rendered.
   */
  public function testShareMessageTwitterCards() {
    // Enable rendering of twitter cards meta tags.
    $user_name = 'amazingLlama';
    $edit = [
      'add_twitter_card' => TRUE,
      'twitter_user' => $user_name,
    ];
    $this->drupalPostForm('admin/config/services/sharemessage/sharemessage-settings', $edit, t('Save configuration'));

    // Create a share message in the UI.
    $this->drupalGet('admin/config/services/sharemessage/add');
    $edit = [
      'label' => 'ShareMessage Test Label',
      'id' => 'sharemessage_test_label',
      'plugin' => 'addthis',
      'title' => 'Share Message Test Title',
      'message_long' => 'Share Message Test Long Description',
      'message_short' => 'Share Message Test Short Description',
      'image_url' => 'http://www.example.com/drupal.jpg',
      'share_url' => 'http://www.example.com',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Share Message @label has been added.', ['@label' => $edit['label']]), 'Share Message is successfully saved.');

    // Display share message and verify the twitter card meta tags.
    $this->drupalGet('sharemessage-test/sharemessage_test_label');

    $meta = '<meta name="twitter:card" content="summary_large_image" />';
    $this->assertRaw($meta);
    $meta = '<meta name="twitter:site" content="' . $user_name . '" />';
    $this->assertRaw($meta);
    $meta = '<meta name="twitter:description" content="' . $edit['message_long'] . '" />';
    $this->assertRaw($meta);
    $meta = '<meta name="twitter:image" content="' . $edit['image_url'] . '" />';
    $this->assertRaw($meta);

    // Disable rendering of twitter cards meta tags.
    $edit = [
      'add_twitter_card' => FALSE,
    ];
    $this->drupalPostForm('admin/config/services/sharemessage/sharemessage-settings', $edit, t('Save configuration'));

    $this->drupalGet('sharemessage-test/sharemessage_test_label');
    $this->assertNoRaw('<meta name="twitter:card"');
    $this->assertNoRaw('<meta name="twitter:site"');
    $this->assertNoRaw('<meta name="twitter:description"');
  }

}
