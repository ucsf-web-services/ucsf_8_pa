<?php

namespace Drupal\sharemessage\Tests;

/**
 * Check if default Share Message settings work correctly.
 *
 * @group sharemessage
 */
class ShareMessageSettingsTest extends ShareMessageTestBase {

  /**
   * Tests if default and Share Message specific settings work correctly.
   */
  public function testShareMessageSettings() {

    // Step 1: Setup default settings.
    $this->drupalGet('admin/config/services/sharemessage/addthis-settings');
    $default_settings = [
      'default_services[]' => [
        'facebook',
        'facebook_like',
      ],
      'default_additional_services' => FALSE,
      'default_icon_style' => 'addthis_16x16_style',
    ];
    $this->drupalPostForm(NULL, $default_settings, t('Save configuration'));

    // Step 2: Create Share Message with customized settings.
    $this->drupalGet('admin/config/services/sharemessage/add');
    $sharemessage = [
      'label' => 'Share Message Test Label',
      'id' => 'sharemessage_test_label',
      'settings[override_default_settings]' => 1,
      'settings[services][]' => [
        'facebook',
      ],
      'settings[additional_services]' => 1,
      'settings[icon_style]' => 'addthis_32x32_style',
    ];
    $this->drupalPostForm(NULL, $sharemessage, t('Save'));
    $this->assertText(t('Share Message @label has been added.', ['@label' => $sharemessage['label']]));

    // Step 3: Verify that settings are overridden
    // (services, additional_services and icon_style).
    $this->drupalGet('sharemessage-test/sharemessage_test_label');
    $raw_html_services = '<a class="addthis_button_facebook_like"></a>';
    $raw_html_additional_services = '<a class="addthis_button_compact"></a>';

    // Check services (facebook_like button should not be displayed).
    $this->assertNoRaw($raw_html_services, 'Facebook like button that is globally enabled is not displayed on the page, so that the global settings are overridden.');

    // Additional services should be displayed.
    $this->assertRaw($raw_html_additional_services, 'Additional services button is displayed, so that the global settings are overridden.');

    // Check if the icon style has been changed so that the global settings are overridden.
    $this->assertShareButtons($sharemessage, $sharemessage['settings[icon_style]'], TRUE);

    // Step 4: Uncheck "Override default settings" checkbox.
    $this->drupalGet('admin/config/services/sharemessage/manage/' . $sharemessage['id']);
    $edit = [
      'settings[override_default_settings]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Share Message @label has been updated.', ['@label' => $sharemessage['label']]));

    // Step 5: Check that addThis widget is displayed with default settings.
    $this->drupalGet('sharemessage-test/sharemessage_test_label');

    // Check services (facebook_like button should be displayed).
    $this->assertRaw($raw_html_services, 'Facebook like button is displayed as it is globally configured.');

    // Additional services button should not be displayed.
    $this->assertNoRaw($raw_html_additional_services, 'Additional services button is not displayed as it is globally configured.');

    // Check icon style (should be addthis_16x16_style).
    $this->assertShareButtons($sharemessage, $default_settings['default_icon_style'], TRUE);

    // Step 1: Setup Sharrre plugin settings.
    $this->drupalGet('admin/config/services/sharemessage/sharrre-settings');

    // Check if configuration is correct (library placeholder from CDN).
    $this->assertNoText('Either set the library locally (in /libraries/sharrre) and enable the libraries module or enter the remote URL on Sharrre settings page.');
    $this->assertNoLinkByHref('admin/config/services/sharemessage/sharrre-settings');

    // Test for empty remote library URL.
    $settings_without_url = [
      'default_services[]' => [
        'googlePlus',
      ],
      'library_url' => '',
    ];
    $this->drupalPostForm(NULL, $settings_without_url, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
    $this->assertText('Either set the library locally (in /libraries/sharrre) and enable the libraries module or enter the remote URL on Sharrre settings page.');
    $this->assertLinkByHref('admin/config/services/sharemessage/sharrre-settings');

    // Test for wrong naming of remote library.
    $this->drupalGet('admin/config/services/sharemessage/sharrre-settings');
    $settings_with_wrong_url = [
      'default_services[]' => [
        'googlePlus',
      ],
      'library_url' => 'test/sharrre.js',
    ];
    $this->drupalPostForm(NULL, $settings_with_wrong_url, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
    $this->assertText('The remote URL is unexpected. Please, provide the correct URL to the minimized version of the library found on Sharrre CDN.');

    $default_settings = [
      'default_services[]' => [
        'googlePlus',
        'facebook',
        'twitter',
        'linkedin',
        'pinterest',
      ],
      'library_url' => '//cdn.jsdelivr.net/sharrre/1.3.4/jquery.sharrre-1.3.4.min.js',
      'shorter_total' => TRUE,
      'enable_hover' => TRUE,
      'enable_counter' => FALSE,
    ];
    $this->drupalPostForm(NULL, $default_settings, t('Save configuration'));

    // Step 2: Switch to Sharrre plugin.
    $this->drupalGet('admin/config/services/sharemessage');
    $this->clickLink('Edit');
    $sharemessage = [
      'label' => 'Share Message Sharrre Test Label',
      'plugin' => 'sharrre',
      'title' => 'Sharrre',
      'message_long' => 'Test long message',
      'message_short' => 'Test short message',
    ];
    $this->drupalPostForm(NULL, $sharemessage, t('Save'));
    $this->assertText(t('Share Message @label has been updated.', ['@label' => $sharemessage['label']]));

    $this->drupalGet('sharemessage-test/sharemessage_test_label');
    $this->assertRaw('"library_url":"\/\/cdn.jsdelivr.net\/sharrre\/1.3.4\/jquery.sharrre-1.3.4.min.js"');
    $this->assertRaw('"googlePlus":"googlePlus","facebook":"facebook","twitter":"twitter","linkedin":"linkedin","pinterest":"pinterest"');

    // Test the naming of the file warning.
    $this->drupalGet('admin/config/services/sharemessage/sharrre-settings');
    $settings_with_wrong_library_naming = [
      'default_services[]' => [
        'facebook'
      ],
      'library_url' => '//cdn.jsdelivr.net/sharrre/1.3.4/jquery.sharrre.js',
    ];
    $this->drupalPostForm(NULL, $settings_with_wrong_library_naming, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
    $this->assertText('The naming of the library is unexpected. Double check that this is the real Sharrre library. The URL for the minimized version of the library can be found on Sharrre CDN.');

    // Test if preg match for the naming of the library works correctly.
    $settings_with_correct_library_naming = [
      'default_services[]' => [
        'facebook'
      ],
      'library_url' => '//cdn.jsdelivr.net/sharrre/1.3.4/jquery.sharrre-10.130.1234.min.js',
    ];
    $this->drupalPostForm(NULL, $settings_with_correct_library_naming, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
    $this->assertNoText('The naming of the library is unexpected. Double check that this is the real Sharrre library. The URL for the minimized version of the library can be found on Sharrre CDN.');

    // Test UrlCurl script for Sharrre.
    // This test requires capability to connect to Google Plus, Stumbleupon,
    // Pinterest platforms.
    $this->drupalGet('/sharemessage/sharrre/counter', ['query' => ['url' => 'https://www.drupal.org/', 'type' => 'googlePlus']]);
    $json_content = json_decode($this->getRawContent(), TRUE);
    $this->assertTrue(isset($json_content['count']), 'googlePlus count found and has a non-zero value: ' . $json_content['count']);
    $this->drupalGet('/sharemessage/sharrre/counter', ['query' => ['url' => 'https://www.drupal.org/', 'type' => 'stumbleupon']]);
    $json_content = json_decode($this->getRawContent(), TRUE);
    $this->assertTrue(isset($json_content['count']), 'stumbleupon count found and has a non-zero value: ' . $json_content['count']);

    // Test Social Share Privacy.
    $this->drupalGet('admin/config/services/sharemessage/socialshareprivacy-settings');
    // Step 1: Set up default settings for Social Share Privacy.
    $default_settings = [
      'services[]' => [
        'gplus',
        'twitter',
      ],
    ];
    $this->drupalPostForm(NULL, $default_settings, t('Save configuration'));

    $this->drupalGet('admin/config/services/sharemessage/add');
    // Step 2: Create Social Share Privacy with customized settings.
    $sharemessage = [
      'label' => 'Social Share Privacy Test Label',
      'id' => 'socialshareprivacy_test_label',
      'settings[override_default_settings]' => 1,
      'settings[services][]' => [
        'facebook',
      ],
      'plugin' => 'socialshareprivacy',
    ];
    $this->drupalPostForm(NULL, $sharemessage, t('Save'));
    $this->assertText(t('Share Message @label has been added.', ['@label' => $sharemessage['label']]));

    // Step 3: Verify that settings are overridden.
    $this->drupalGet('sharemessage-test/socialshareprivacy_test_label');
    $this->assertRaw('"facebook":{"status":true');
    // Step 4: Uncheck "Override default settings" checkbox.
    $this->drupalGet('admin/config/services/sharemessage/manage/' . $sharemessage['id']);
    $edit = [
      'settings[override_default_settings]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Share Message @label has been updated.', ['@label' => $sharemessage['label']]));
    // Step 5: Check default settings of Social Share Privacy is displayed.
    $this->drupalGet('sharemessage-test/socialshareprivacy_test_label');
    // Check services (googlePlus and twitter should be displayed).
    $this->assertRaw('"gplus":{"status":true');
    $this->assertRaw('"twitter":{"status":true');
    $this->assertRaw('"facebook":{"status":false');

    // Test sharemessage global settings.
    $this->drupalGet('admin/config/services/sharemessage/sharemessage-settings');
    $edit = [
      'add_twitter_card' => TRUE,
      'twitter_user' => 'fancy_twitter_name',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));
    $this->assertFieldByName('add_twitter_card', TRUE);
    $this->assertFieldByName('twitter_user', 'fancy_twitter_name');
  }

  /**
   * Tests the Share Message delete and cancel button functionality.
   */
  function testShareMessageDeleteCancel() {
    // Create a Share Message.
    $this->drupalGet('admin/config/services/sharemessage/add');
    $sharemessage = [
      'label' => 'Share Message Test Label',
      'id' => 'sharemessage_test_label',
      'settings[override_default_settings]' => 1,
      'settings[services][]' => [
        'facebook',
      ],
      'settings[additional_services]' => 1,
      'settings[icon_style]' => 'addthis_32x32_style',
    ];
    $this->drupalPostForm(NULL, $sharemessage, t('Save'));

    // Check newly created Share Message on list page.
    $this->drupalGet('admin/config/services/sharemessage');
    $this->assertText($sharemessage['label'], 'Newly created sharemessage found.');
    // Check for Edit link.
    $this->assertLink('Edit');
    // Check for the Delete link.
    $this->assertLink('Delete');

    // Click delete link on admin ui.
    $this->clickLink('Delete');
    $this->assertText(t('Are you sure you want to delete the share message @label?', ['@label' => $sharemessage['label']]));

    // Check if cancel button is present or not.
    $this->assertLink('Cancel');

    // Delete the Share Message.
    $this->drupalPostForm(NULL, [], t('Delete'));
    $this->assertText(t('The share message @label has been deleted.', ['@label' => $sharemessage['label']]));

    // Check if removed from listing page as well.
    $this->drupalGet('admin/config/services/sharemessage');
    $this->assertNoText($sharemessage['label'], 'Not found the deleted message.');
  }

  /**
   * Test case for special characters encoding.
   */
  public function testShareMessageSpecialCharsEncoding() {
    // Create Share Message with AddThis as plugin.
    $this->drupalGet('admin/config/services/sharemessage/add');
    $sharemessage = [
      'label' => 'Special characters encoding test',
      'id' => 'sharemessage_test_special_chars',
      'plugin' => 'addthis',
      'title' => 'Inondations sur la Côte d\'Azur: «C’est apocalyptique, c’est Tchernobyl»',
      'message_long' => 'Long description',
      'message_short' => 'Short description',
    ];
    $this->drupalPostForm(NULL, $sharemessage, t('Save'));
    $this->assertText(t('Share Message @label has been added.', ['@label' => $sharemessage['label']]));
    $this->drupalGet('sharemessage-test/sharemessage_test_special_chars');
    // Check for correct encoding in meta tags.
    $this->assertOGTags('og:title', 'Inondations sur la Côte d&#039;Azur: «C’est apocalyptique, c’est Tchernobyl»');
    $this->assertNoOGTags('og:title', 'Inondations sur la Côte d\'Azur: «C’est apocalyptique, c’est Tchernobyl»');
    $this->assertOGTags('og:url', $this->url);
    $this->assertOGTags('og:description', 'Long description');
    $this->assertOGTags('og:type', 'website');

    $this->drupalGet('admin/config/services/sharemessage/add');
    $sharemessage2 = [
      'label' => 'Special characters encoding test 2',
      'id' => 'sharemessage_test_special_chars_2',
      'plugin' => 'addthis',
      'title' => 'This is a second test with quotes "',
      'message_long' => 'Long description 2',
      'message_short' => 'Short description 2',
    ];
    $this->drupalPostForm(NULL, $sharemessage2, t('Save'));
    $this->drupalGet('sharemessage-test/sharemessage_test_special_chars_2');
    $this->assertOGTags('og:title', 'This is a second test with quotes &quot;');
    $this->assertNoOGTags('og:title', 'This is a second test with quotes "');
  }

}
