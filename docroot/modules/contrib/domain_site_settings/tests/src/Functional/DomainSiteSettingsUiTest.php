<?php

namespace Drupal\Tests\domain_site_settings\Functional;

use Drupal\domain\Entity\Domain;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests UI pages.
 *
 * @group domain_site_settings
 */
class DomainSiteSettingsUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['domain_site_settings'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Administrator user for tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $permissions = [
      'administer site configuration',
      'domain site settings',
    ];
    $this->adminUser = $this->drupalCreateUser($permissions);
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testAdminList() {
    // The anonymous user doesn't have the permission to access this page.
    $this->drupalGet('admin/config/domain/domain_site_settings');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/domain/domain_site_settings');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Domains sites list');
    $this->assertSession()->pageTextNotContains('Foo');

    // Create a new domain.
    $domain = Domain::create([
      'id' => 'foo',
      'hostname' => 'foo.example.com',
      'name' => 'Foo',
      'scheme' => 'http',
    ]);
    $domain->save();

    // Check domain specific content.
    $this->drupalGet('admin/config/domain/domain_site_settings');
    $this->assertSession()->pageTextContains('Foo');
  }

  /**
   * Test the settings form.
   */
  public function testSettingsForm() {
    // Create a new domain.
    $domain = Domain::create([
      'id' => 'foo',
      'hostname' => 'foo.example.com',
      'name' => 'Foo',
      'scheme' => 'http',
    ]);
    $domain->save();

    // The anonymous user doesn't have the permission to access this page.
    $this->drupalGet('admin/config/domain/domain_site_settings/foo/edit');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/domain/domain_site_settings/foo/edit');
    $this->assertSession()->statusCodeEquals(200);

    // Save the form and check if the values are correctly stored in config.
    $this->submitForm([
      'site_name' => 'Site name',
      'site_slogan' => 'Slogan',
      'site_mail' => 'sitemail@example.com',
      'site_frontpage' => '/user',
      'site_403' => '/user?404',
      'site_404' => '/user?403',
    ],
    'Save configuration');

    $config = $this->config('domain_site_settings.domainconfigsettings');
    $this->assertEquals('Site name', $config->get('foo.site_name'));
    $this->assertEquals('Slogan', $config->get('foo.site_slogan'));
    $this->assertEquals('sitemail@example.com', $config->get('foo.site_mail'));
    $this->assertEquals('/user', $config->get('foo.site_frontpage'));
    $this->assertEquals('/user?404', $config->get('foo.site_403'));
    $this->assertEquals('/user?403', $config->get('foo.site_404'));
  }

}
