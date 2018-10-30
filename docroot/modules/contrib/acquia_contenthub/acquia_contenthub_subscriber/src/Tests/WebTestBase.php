<?php

namespace Drupal\acquia_contenthub_subscriber\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase as SimpletestWebTestBase;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Session\AccountInterface;

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
  public static $modules = [
    'acquia_contenthub',
    'acquia_contenthub_subscriber',
  ];

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
    'administer acquia content hub',
    'access administration pages',
    'restful get contenthub_filter',
    'restful post contenthub_filter',
    'restful patch contenthub_filter',
    'restful delete contenthub_filter',
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
   * The Content Hub Filter config storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityConfigStorage;

  /**
   * The default serialization format to use for testing REST operations.
   *
   * @var string
   */
  protected $defaultFormat;

  /**
   * The default MIME type to use for testing REST operations.
   *
   * @var string
   */
  protected $defaultMimeType;

  /**
   * The entity type to use for testing.
   *
   * @var string
   */
  protected $testEntityType = 'contenthub_filter';

  /**
   * The default authentication provider to use for testing REST operations.
   *
   * @var array
   */
  protected $defaultAuth;

  /**
   * The raw response body from http request operations.
   *
   * @var array
   */
  protected $responseBody;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->defaultFormat = 'json';
    $this->defaultMimeType = 'application/json';
    $this->defaultAuth = ['cookie'];
    $this->entityConfigStorage = $this->container->get('entity_type.manager')->getStorage('contenthub_filter');

    // Create the users used for the tests.
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
    $this->unauthorizedUser = $this->drupalCreateUser(['access administration pages']);
    $this->anonymousUser = $this->drupalCreateUser();

    // Get the URL generator.
    $this->urlGenerator = $this->container->get('url_generator');

  }

  /**
   * Helper function to issue a HTTP request with simpletest's cURL.
   *
   * @param string|\Drupal\Core\Url $url
   *   A Url object or system path.
   * @param string $method
   *   HTTP method, one of GET, POST, PUT or DELETE.
   * @param string $body
   *   The body for POST and PUT.
   * @param string $mime_type
   *   The MIME type of the transmitted content.
   * @param bool $csrf_token
   *   If NULL, a CSRF token will be retrieved and used. If FALSE, omit the
   *   X-CSRF-Token request header (to simulate developer error). Otherwise, the
   *   passed in value will be used as the value for the X-CSRF-Token request
   *   header (to simulate developer error, by sending an invalid CSRF token).
   *
   * @return string
   *   The content returned from the request.
   */
  protected function httpRequest($url, $method, $body = NULL, $mime_type = NULL, $csrf_token = NULL) {
    if (!isset($mime_type)) {
      $mime_type = $this->defaultMimeType;
    }
    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS', 'TRACE'])) {
      // GET the CSRF token first for writing requests.
      $requested_token = $this->drupalGet('/rest/session/token');
    }

    $url = $this->buildUrl($url) . '?_format=json';

    $curl_options = [];
    switch ($method) {
      case 'GET':
        // Set query if there are additional GET parameters.
        $curl_options = [
          CURLOPT_HTTPGET => TRUE,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_URL => $url,
          CURLOPT_NOBODY => FALSE,
          CURLOPT_HTTPHEADER => [
            'Accept: ' . $mime_type,
          ],
        ];
        break;

      case 'POST':
        $curl_options = [
          CURLOPT_HTTPGET => FALSE,
          CURLOPT_POST => TRUE,
          CURLOPT_POSTFIELDS => $body,
          CURLOPT_URL => $url,
          CURLOPT_NOBODY => FALSE,
          CURLOPT_HTTPHEADER => $csrf_token !== FALSE ? [
            'Content-Type: ' . $mime_type,
            'X-CSRF-Token: ' . ($csrf_token === NULL ? $requested_token : $csrf_token),
          ] : [
            'Content-Type: ' . $mime_type,
          ],
        ];
        break;

      case 'PATCH':
        $curl_options = [
          CURLOPT_HTTPGET => FALSE,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_POSTFIELDS => $body,
          CURLOPT_URL => $url,
          CURLOPT_NOBODY => FALSE,
          CURLOPT_HTTPHEADER => $csrf_token !== FALSE ? [
            'Content-Type: ' . $mime_type,
            'X-CSRF-Token: ' . ($csrf_token === NULL ? $requested_token : $csrf_token),
          ] : [
            'Content-Type: ' . $mime_type,
          ],
        ];
        break;

      case 'DELETE':
        $curl_options = [
          CURLOPT_HTTPGET => FALSE,
          CURLOPT_CUSTOMREQUEST => 'DELETE',
          CURLOPT_URL => $url,
          CURLOPT_NOBODY => FALSE,
          CURLOPT_HTTPHEADER => $csrf_token !== FALSE ? [
            'X-CSRF-Token: ' . ($csrf_token === NULL ? $requested_token : $csrf_token),
          ] : [],
        ];
        break;
    }

    if ($mime_type === 'none') {
      unset($curl_options[CURLOPT_HTTPHEADER]['Content-Type']);
    }

    $this->responseBody = $this->curlExec($curl_options, TRUE);

    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();

    $headers = $this->drupalGetHeaders();

    $this->verbose($method . ' request to: ' . $url .
      '<hr />Code: ' . curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE) .
      (isset($curl_options[CURLOPT_HTTPHEADER]) ? '<hr />Request headers: ' . nl2br(print_r($curl_options[CURLOPT_HTTPHEADER], TRUE)) : '') .
      (isset($curl_options[CURLOPT_POSTFIELDS]) ? '<hr />Request body: ' . nl2br(print_r($curl_options[CURLOPT_POSTFIELDS], TRUE)) : '') .
      '<hr />Response headers: ' . nl2br(print_r($headers, TRUE)) .
      '<hr />Response body: ' . $this->responseBody);

    return $this->responseBody;
  }

  /**
   * {@inheritdoc}
   *
   * This method is overridden to deal with a cURL quirk: the usage of
   * CURLOPT_CUSTOMREQUEST cannot be unset on the cURL handle, so we need to
   * override it every time it is omitted.
   */
  protected function curlExec($curl_options, $redirect = FALSE) {
    if (!isset($curl_options[CURLOPT_CUSTOMREQUEST])) {
      if (!empty($curl_options[CURLOPT_HTTPGET])) {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
      }
      if (!empty($curl_options[CURLOPT_POST])) {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'POST';
      }
    }
    return parent::curlExec($curl_options, $redirect);
  }

  /**
   * Check if the HTTP request response body is identical to the expected value.
   *
   * @param string $expected
   *   The first value to check.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Utility\SafeMarkup::format() to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   * @param string $group
   *   (optional) The group this message is in, which is displayed in a column
   *   in test output. Use 'Debug' to indicate this is debugging output. Do not
   *   translate this string. Defaults to 'Other'; most tests do not override
   *   this default.
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  protected function assertResponseBody($expected, $message = '', $group = 'REST Response') {
    return $this->assertIdentical($expected, $this->responseBody, $message ? $message : strtr('Response body @expected (expected) is equal to @response (actual).', [
      '@expected' => var_export($expected, TRUE),
      '@response' => var_export($this->responseBody, TRUE),
    ]
    ), $group);
  }

  /**
   * Provides an array of suitable property values for an entity type.
   *
   * Required properties differ from entity type to entity type, so we keep a
   * minimum mapping here.
   *
   * @param string $entity_type_id
   *   The ID of the type of entity that should be created.
   *
   * @return array
   *   An array of values keyed by property name.
   */
  protected function entityValues($entity_type_id) {
    switch ($entity_type_id) {
      case 'node':
        return ['title' => $this->randomString(), 'type' => 'resttest'];

      case 'node_type':
        return [
          'type' => 'article',
          'name' => $this->randomMachineName(),
        ];

      case 'user':
        return ['name' => $this->randomMachineName()];

      case 'taxonomy_vocabulary':
        return [
          'vid' => 'tags',
          'name' => $this->randomMachineName(),
        ];

      default:
        if ($this->isConfigEntity($entity_type_id)) {
          return $this->configEntityValues($entity_type_id);
        }
        return [];
    }
  }

  /**
   * Checks if an entity type id is for a Config Entity.
   *
   * @param string $entity_type_id
   *   The entity type ID to check.
   *
   * @return bool
   *   TRUE if the entity is a Config Entity, FALSE otherwise.
   */
  protected function isConfigEntity($entity_type_id) {
    return \Drupal::entityTypeManager()->getDefinition($entity_type_id) instanceof ConfigEntityType;
  }

  /**
   * Creates entity objects based on their types.
   *
   * @param string $entity_type
   *   The type of the entity that should be created.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account that will own this entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The new entity object.
   */
  protected function entityCreate($entity_type, AccountInterface $account = NULL) {
    return $this->container->get('entity_type.manager')
      ->getStorage($entity_type)
      ->create($this->configEntityValues($entity_type, $account));
  }

  /**
   * Provides an array of suitable property values for a config entity type.
   *
   * Config entities have some common keys that need to be created. Required
   * properties differ among config entity types, so we keep a minimum mapping
   * here.
   *
   * @param string $entity_type_id
   *   The ID of the type of entity that should be created.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account that will own this entity.
   *
   * @return array
   *   An array of values keyed by property name.
   */
  protected function configEntityValues($entity_type_id, AccountInterface $account = NULL) {
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    $keys = $entity_type->getKeys();
    $values = [];
    // Fill out known key values that are shared across entity types.
    foreach ($keys as $key) {
      if ($key === 'id' || $key === 'label') {
        $values[$key] = $this->randomMachineName();
      }
    }
    // Add extra values for particular entity types.
    switch ($entity_type_id) {
      case 'contenthub_filter':
        $publish_settings = [
          'none',
          'import',
          'publish',
        ];
        $values['name'] = 'Name for ' . $values['id'];
        $values['publish_setting'] = $publish_settings[random_int(0, 2)];
        $values['search_term'] = $this->randomMachineName();
        $author = $account !== NULL ? $account->id() : $this->adminUser->id();
        $values['author'] = intval($author);
        $values['entity_types'] = [
          'node',
        ];
        $values['bundles'] = [
          'article',
          'page',
        ];
        break;
    }
    return $values;
  }

}
