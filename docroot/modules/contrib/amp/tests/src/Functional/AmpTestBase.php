<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Core\Url;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\filter\Entity\FilterFormat;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Base AMP testing setup.
 */
abstract class AmpTestBase extends BrowserTestBase {

  use ContentTypeCreationTrait;

  /**
   * The AMP content type used in tests.
   *
   * @var string
   */
  protected $contentType = '';

  /**
   * The node created by tests.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node = NULL;

  /**
   * The display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $displayRepository = NULL;

  /**
   * The path to test fixtures.
   *
   * @var array
   */
  protected $fixturesPath = '';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'amp',
    'metatag',
    'schema_metatag',
    'token',
    'node',
    'field_ui',
    'filter',
    'text',
    'link',
    'file',
    'image',
    'media',
  ];

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'access administration pages',
    'access content overview',
    'view all revisions',
    'administer content types',
    'administer display modes',
    'administer node fields',
    'administer node form display',
    'administer node display',
    'administer site configuration',
    'administer filters',
    'bypass node access',
    'use text format full_html',
    'access media overview',
    'administer media',
    'administer media fields',
    'administer media form display',
    'administer media display',
    'administer media types',
    'view media',
  ];

  /**
   * An user with administration permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();

    $this->fixturesPath = str_replace('/tests/src/Functional', '/tests/fixtures', dirname(__FILE__));

    // Set up full html filter.
    $full_html_format = FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 1,
      'filters' => [],
    ]);
    $full_html_format->save();
    user_role_grant_permissions('authenticated', [$full_html_format->getPermissionName()]);

    // Install the theme.
    // @see https://www.drupal.org/node/2232651
    $this->container->get('theme_installer')
      ->install(['bartik', 'seven', 'ampsubtheme_example']);
    $this->container->get('config.factory')
      ->getEditable('system.theme')
      ->set('default', 'bartik')
      ->set('admin', 'seven')
      ->save();

    // Populate variables.
    $this->contentType = strtolower($this->randomMachineName());
    $this->displayRepository = \Drupal::service('entity_display.repository');

    // Create node type.
    $this->createContentType([
      'type' => $this->contentType,
      'name' => $this->contentType,
    ]);

    // Login as an admin user.
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    // Enable user pictures on nodes.
    $this->config('system.theme.global')->set('features.node_user_picture', TRUE)->save();

    $this->drupalLogin($this->adminUser);
    $this->assertTrue($full_html_format->access('use', $this->adminUser), 'Admin user may use permission: ' . $full_html_format->getPermissionName());

    // Configure AMP.
    $amp_settings_url = Url::fromRoute("amp.settings")->toString();
    $this->drupalGet($amp_settings_url);
    $edit = ['amptheme' => 'ampsubtheme_example'];
    $this->submitForm($edit, t('Save configuration'));

    // Enable AMP display on node content.
    $node_type_url = Url::fromRoute("entity.entity_view_display.node.default", ['node_type' => $this->contentType])->toString();
    $this->drupalGet($node_type_url);
    $this->assertSession()->statusCodeEquals(200);
    $edit = ['display_modes_custom[amp]' => 'amp'];
    $this->submitForm($edit, t('Save'));

    // Configure view modes and field formatters.
    $this->displayRepository->getViewDisplay('node', $this->contentType, 'amp')
      ->setComponent('body', [
        'type' => 'amp_text',
        'settings' => [],
      ])->save();

    $this->displayRepository->getViewDisplay('user', 'user', 'compact')
      ->removeComponent('member_for')
      ->save();

    // Make sure Metatag canonical link is NOT configured, it does not
    // behave properly in tests, although it works on actual sites.
    \Drupal::configFactory()
      ->getEditable('metatag.metatag_defaults.node')
      ->set('tags.canonical_url', '')
      ->save(TRUE);

  }

  /**
   * The url of the test node.
   */
  public function nodeUrl() {
    return Url::fromRoute('entity.node.canonical', ['node' => $this->node->id()], ['absolute' => TRUE])->toString();
  }

  /**
   * The AMP url of the test node.
   */
  public function nodeAmpUrl() {
    return Url::fromRoute('entity.node.canonical', ['node' => $this->node->id()], ['absolute' => TRUE, 'query' => ['amp' => NULL]])->toString();
  }

  /**
   * Create a node with desired content for testing.
   */
  public function createAmpNode() {

    $title = $this->randomMachineName();
    $text = $this->bodyText();
    $node = $this->drupalCreateNode([
      'type' => $this->contentType,
      'title' => $title,
      'body' => [
        'value' => $text,
        'format' => 'full_html',
      ],
    ]);
    $this->node = $node;

  }

  /**
   * Helper function to populate body text.
   */
  protected function bodyText() {
    $words = [];
    $max = mt_rand(10, 15);
    $words = [];
    for ($i = 0; $i < $max; $i++) {
      $words[] = $this->randomMachineName(mt_rand(4, 10));
    }
    $text = '<p>' . ucfirst(implode(' ', $words) . '.</p>');
    return $text;
  }

  /**
   * Adds a text field to the test content type.
   */
  protected function createTextField($field_name) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => FALSE,
      'settings' => [
        'max_length' => 255,
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'bundle' => $this->contentType,
      'entity_type' => 'node',
      'settings' => [],
    ])->save();

    $this->displayRepository->getFormDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => 'string_textfield',
      ])
      ->save();
    $this->displayRepository->getViewDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => 'string',
      ])
      ->save();
  }

  /**
   * Adds a link field to the test content type.
   */
  protected function createLinkField($field_name) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'link',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => FALSE,
      'settings' => [
        'max_length' => 255,
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'bundle' => $this->contentType,
      'entity_type' => 'node',
      'settings' => [],
    ])->save();

    $this->displayRepository->getFormDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => 'link_default',
      ])
      ->save();
    $this->displayRepository->getViewDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => 'link',
      ])
      ->save();
  }

  /**
   * Adds a reference field to the test content type.
   */
  protected function createEntityReferenceField($field_name, $target_entity_type, array $target_bundles, $formatter_type = 'entity_reference_label', array $formatter_settings = []) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => FALSE,
      'settings' => [
        'target_type' => $target_entity_type,
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'bundle' => $this->contentType,
      'entity_type' => 'node',
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => $target_bundles,
        ],
      ],
    ])->save();

    $this->displayRepository->getFormDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => 'entity_reference_autocomplete',
      ])
      ->save();
    $this->displayRepository->getViewDisplay('node', $this->contentType)
      ->setComponent($field_name, [
        'type' => $formatter_type,
        'settings' => $formatter_settings,
      ])
      ->save();
  }

}
