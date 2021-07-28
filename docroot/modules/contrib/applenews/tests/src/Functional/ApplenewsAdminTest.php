<?php

namespace Drupal\Tests\applenews\Functional;

/**
 * Tests node administration page functionality.
 *
 * @group applenews
 */
class ApplenewsAdminTest extends ApplenewsTestBase {

  /**
   * Tests admin pages.
   */
  public function testAppleNewsAdminPages() {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    // Verify overview page.
    $this->drupalGet('/admin/config');
    $assert_session->statusCodeEquals(200);
    $assert_session->linkExists('Apple News');

    $this->drupalGet('/admin/config/services/applenews/settings');
    $assert_session->statusCodeEquals(200);
    $assert_session->fieldExists('endpoint');
    $assert_session->fieldExists('api_key');
    $assert_session->fieldExists('api_secret');

    $assert_session->pageTextContains('Advanced');
    $assert_session->fieldExists('ssl');
    $assert_session->fieldExists('proxy');
    $assert_session->fieldExists('proxy_port');
  }

  /**
   * Tests settings form submit.
   */
  public function testAdminFormSubmit() {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $api_key = $this->randomString();
    $api_secret = $this->randomString();
    $proxy = $this->randomString();
    $proxy_port = $this->randomString(5);

    $edit = [
      'endpoint' => 'https://example.com',
      'api_key' => $api_key,
      'api_secret' => $api_secret,
      'proxy' => $proxy,
      'proxy_port' => $proxy_port,
    ];
    $this->drupalGet('/admin/config/services/applenews/settings');
    $this->submitForm($edit, 'Save configuration');
    $assert_session->pageTextContains('The configuration options have been saved.');
    foreach ($edit as $field => $value) {
      if ($field === 'api_secret') {
        $assert_session->fieldValueNotEquals($field, $value);
      }
      else {
        $assert_session->fieldValueEquals($field, $value);
      }
    }

    // Test that the trailing slash is trimmed off.
    $edit['endpoint'] = 'https://example.com/';
    $this->submitForm($edit, 'Save configuration');
    $assert_session->pageTextContains('The configuration options have been saved.');
    $assert_session->fieldValueEquals('endpoint', 'https://example.com');

    // Test that an error is thrown for invalid URL.
    $edit['endpoint'] = 'derp';
    $this->submitForm($edit, 'Save configuration');
    $assert_session->pageTextContains('The URL derp is not valid');

    // Submit the form with an empty API secret, ensure that the API secret
    // does not get replaced.
    $edit['endpoint'] = 'https://example.com';
    $edit['api_secret'] = '';
    $this->submitForm($edit, 'Save configuration');
    $assert_session->pageTextContains('The configuration options have been saved.');
    $this->assertEqual(\Drupal::config('applenews.settings')->get('api_secret'), $api_secret);
  }

}
