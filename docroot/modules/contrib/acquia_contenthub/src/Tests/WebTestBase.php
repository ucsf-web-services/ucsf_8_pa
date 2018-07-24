<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase as SimpletestWebTestBase;
use Drupal\Component\Serialization\Json;

/**
 * Provides the base class for web tests for Search API.
 */
abstract class WebTestBase extends SimpletestWebTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = ['node', 'acquia_contenthub'];

  /**
   * An admin user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'bypass node access',
    'administer acquia content hub',
    'administer content types',
    'access administration pages',
  ];

  /**
   * A user without Acquia Content Hub admin permission.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $unauthorizedUser;

  /**
   * The anonymous user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $anonymousUser;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The Configuration Object for acquia_contenthub.admin_settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create the users used for the tests.
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
    $this->unauthorizedUser = $this->drupalCreateUser(['access administration pages']);
    $this->anonymousUser = $this->drupalCreateUser();

    // Get the URL generator.
    $this->urlGenerator = $this->container->get('url_generator');

    // Set up Configuration Object.
    $this->config = $this->container->get('config.factory')->getEditable('acquia_contenthub.admin_settings');

    // Sets Content Hub Connection.
    $this->setContentHubConnection();

    // Create a node article type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    // Create a node page type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Page',
    ]);
  }

  /**
   * Configures Content types to be exported to Content Hub.
   *
   * @param string $entity_type
   *   The entity type the bundles belong to.
   * @param array $bundles
   *   The bundles to enable.
   */
  public function configureContentHubContentTypes($entity_type, array $bundles) {
    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);

    $edit = [];
    foreach ($bundles as $bundle) {
      $edit['entities[' . $entity_type . '][' . $bundle . '][enable_index]'] = 1;
    }

    $this->drupalPostForm(NULL, $edit, $this->t('Save configuration'));
    $this->assertResponse(200);

    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);
  }

  /**
   * Configures the Content Hub Connection.
   */
  public function setContentHubConnection() {
    $config = [
      'hostname' => \Drupal::request()->getHost(),
      'api' => '11111111-0000-0000-0000-000000000000',
      'secret' => '22222222-0000-0000-0000-000000000000',
      'origin' => '00000000-0000-0000-0000-000000000000',
      'client_name' => 'mytestsite',
    ];

    // Save configuration variables.
    $this->config->set('hostname', $config['hostname']);
    $this->config->set('api_key', $config['api']);
    $this->config->set('secret_key', $config['secret']);
    $this->config->set('origin', $config['origin']);
    $this->config->set('client_name', $config['client_name']);
    $this->config->save();

    // Obtain configuration variables.
    $hostname = $this->config->get('hostname');
    $api = $this->config->get('api_key');
    $secret = $this->config->get('secret_key');
    $origin = $this->config->get('origin');
    $client_name = $this->config->get('client_name');

    // Check config variables are correctly set.
    $this->assertEqual($hostname, $config['hostname']);
    $this->assertEqual($api, $config['api']);
    $this->assertEqual($secret, $config['secret']);
    $this->assertEqual($origin, $config['origin']);
    $this->assertEqual($client_name, $config['client_name']);

    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = $this->container->get('acquia_contenthub.client_manager');
    $client_manager->resetConnection($config);
    $this->assertTrue($client_manager->isConnected(), 'Content Hub Client is connected successfully.');
  }

  /**
   * Sets a role to be used in CDF render.
   *
   * @param string $role
   *   The role.
   */
  public function setRoleFor($role) {
    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);

    $edit = [
      'user_role' => $role,
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save configuration'));
    $this->assertResponse(200);

    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);
  }

  /**
   * Retrieves a Drupal or an absolute CDF path and JSON decodes the result.
   *
   * @param \Drupal\Core\Url|string $path
   *   Drupal path or URL to request AJAX from.
   * @param array $options
   *   Array of URL options.
   * @param array $headers
   *   Array of headers. Eg array('Accept: application/vnd.drupal-ajax').
   *
   * @return array
   *   Decoded json.
   */
  protected function drupalGetCdf($path, array $options = [], array $headers = []) {
    return Json::decode($this->drupalGetWithFormat($path, 'acquia_contenthub_cdf', $options, $headers));
  }

}
