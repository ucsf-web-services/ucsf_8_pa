<?php

namespace Drupal\Tests\cloudflare\Functional;

use Drupal\cloudflare_form_tester\Mocks\ComposerDependenciesCheckMock;
use Drupal\Core\Url;
use Drupal\cloudflare_form_tester\Mocks\ZoneMock;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests \Drupal\purge_ui\Form\CloudFlareAdminSettingsForm.
 *
 * @group cloudflare
 */
#[\AllowDynamicProperties]
class CloudFlareAdminSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['cloudflare', 'cloudflare_form_tester', 'ctools'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An admin user that has been setup for the test.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Route providing the main configuration form of the cloudflare module.
   *
   * @var string
   */
  protected $route = 'cloudflare.admin_settings_form';

  /**
   * Setup the test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['administer cloudflare']);
    $this->drupalLogin($this->adminUser);
    $this->formUrl = Url::fromRoute($this->route);

    ZoneMock::mockAssertValidCredentials(TRUE);
    ComposerDependenciesCheckMock::mockComposerDependenciesMet(TRUE);
  }

  /**
   * Test posting an invalid host to the form.
   */
  public function testValidCredentials() {
    $edit = [
      'apikey' => '68ow48650j63zfzx1w9jd29cr367u0ezb6a4g',
      'email' => 'test@test.com',
    ];
    $this->drupalGet($this->formUrl);
    $this->submitForm($edit, 'Next');
    $this->assertSession()->addressEquals('/admin/config/services/cloudflare/two?js=nojs');
    $this->submitForm([], 'Finish');
    $this->assertSession()->responseContains('68ow48650j63zfzx1w9jd29cr367u0ezb6a4g');
    $this->assertSession()->responseContains('test@test.com');
    $this->assertSession()->responseContains('testdomain.com');
  }

  /**
   * Test posting an invalid host to the form.
   */
  public function testMultiZoneSelection() {
    $edit = [
      'apikey' => '68ow48650j63zfzx1w9jd29cr367u0ezb6a4g',
      'email' => 'test@test.com',
    ];
    ZoneMock::mockMultiZoneAccount(TRUE);
    $this->drupalGet($this->formUrl);
    $this->submitForm($edit, 'Next');
    $this->assertSession()->addressEquals('/admin/config/services/cloudflare/two?js=nojs');
    $this->submitForm(['zone_selection[]' => '123456789999'], 'Finish');
    $this->assertSession()->responseContains('68ow48650j63zfzx1w9jd29cr367u0ezb6a4g');
    $this->assertSession()->responseContains('testdomain2.com');
  }

  /**
   * Test posting an invalid host with https protocol to the form.
   */
  public function testInvalidBypassHostWithHttps() {
    $edit = [
      'apikey' => '68ow48650j63zfzx1w9jd29cr367u0ezb6a4g',
      'email' => 'test@test.com',
      'client_ip_restore_enabled' => TRUE,
      'bypass_host' => 'https://blah.com',
    ];
    $this->drupalGet($this->formUrl);
    $this->submitForm($edit, 'Next');
    $this->assertSession()->pageTextContains('Please enter a host without http/https');
  }

  /**
   * Test posting an invalid host with http protocol to the form.
   */
  public function testInvalidBypassHostWithHttp() {
    $edit = [
      'apikey' => '68ow48650j63zfzx1w9jd29cr367u0ezb6a4g',
      'email' => 'test@test.com',
      'client_ip_restore_enabled' => TRUE,
      'bypass_host' => 'http://blah.com',
    ];
    $this->drupalGet($this->formUrl);
    $this->submitForm($edit, 'Next');
    $this->assertSession()->pageTextContains('Please enter a host without http/https');
  }

  /**
   * Test posting an invalid host to the form.
   */
  public function testInvalidBypassHost() {
    $edit = [
      'apikey' => '68ow48650j63zfzx1w9jd29cr367u0ezb6a4g',
      'email' => 'test@test.com',
      'client_ip_restore_enabled' => TRUE,
      'bypass_host' => 'blah!@#!@',
    ];
    $this->drupalGet($this->formUrl);
    $this->submitForm($edit, 'Next');
    $this->assertSession()->pageTextContains('You have entered an invalid host.');
  }

}
