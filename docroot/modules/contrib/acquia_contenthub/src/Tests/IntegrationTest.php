<?php

namespace Drupal\acquia_contenthub\Tests;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\NodeInterface;

/**
 * Tests the overall functionality of the Acquia Content Hub module.
 *
 * @group acquia_contenthub
 */
class IntegrationTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'acquia_contenthub',
    'field',
    'field_test_boolean_access_denied',
  ];

  /**
   * The sample article we generate.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $article;

  /**
   * The sample unpublished article we generate.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $unpublishedArticle;

  /**
   * The sample page we generate.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $page;

  /**
   * Admin role.
   *
   * @var string
   */
  protected $adminRole;

  /**
   * Limited role.
   *
   * @var string
   */
  protected $limitedRole;

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminRole = $this->createAdminRole();
    $this->limitedRole = $this->createRole(['access content']);

    // Create a field to test access.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'test_field_01',
      'entity_type' => 'node',
      'type' => 'boolean',
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_name' => 'test_field_01',
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Test',
      'required' => TRUE,
      'settings' => [
        'on_label' => 'field_test_01_on',
        'off_label' => 'field_test_01_off',
      ],
    ]);
    $this->field->save();

    // Create a display for the full view mode.
    entity_get_display('node', 'article', 'full')
      ->setComponent('test_field_01', [
        'type' => 'boolean',
      ])
      ->save();

    // Create a display for the teaser view mode.
    entity_get_display('node', 'article', 'teaser')
      ->setComponent('test_field_01', [
        'type' => 'boolean',
      ])
      ->save();
  }

  /**
   * Tests various operations via the Acquia Content Hub admin UI.
   */
  public function testFramework() {
    // Enable dumpHeaders when you are having caching issues.
    $this->dumpHeaders = TRUE;
    $this->drupalLogin($this->adminUser);

    // Create sample content.
    $this->createSampleContent();

    // Configure Acquia Content Hub for article nodes with view modes.
    $this->configureContentHubContentTypes('node', ['article']);
    $this->checkCdfOutput($this->article);

    // Check CDF access on published node.
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkCdfAccess($this->article);

    $this->checkCdfFormat($this->article);

    $this->setRoleFor($this->limitedRole);
    $this->checkCdfAccess($this->article);

    $this->setRoleFor($this->adminRole);
    $this->checkCdfAccess($this->article);

    // Check CDF access on unpublished node.
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkCdfAccess($this->unpublishedArticle, FALSE);

    $this->setRoleFor($this->limitedRole);
    $visible_roles = $this->getUserWarningRole();
    $this->assertFalse(array_key_exists($this->limitedRole, $visible_roles), 'The role warning message absent');
    $this->checkCdfAccess($this->unpublishedArticle, FALSE);

    $this->setRoleFor($this->adminRole);
    $visible_roles = $this->getUserWarningRole();
    $this->assertTrue(array_key_exists($this->adminRole, $visible_roles), 'The role warning message present');
    $this->checkCdfAccess($this->unpublishedArticle);

    // Check if the test field presents in CDF attributes.
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkCdfFieldAccess($this->article);
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkCdfFieldAccess($this->article, FALSE, FALSE);
    $this->setRoleFor($this->adminRole);
    $this->checkCdfFieldAccess($this->article);
    $this->setRoleFor($this->adminRole);
    $this->checkCdfFieldAccess($this->article, FALSE, FALSE);

    // Access test cleanup.
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);

    // Enable view-modes for article nodes.
    $this->enableViewModeFor('node', 'article', 'teaser');
    $this->checkCdfOutput($this->article, 'teaser');

    $this->ConfigureAndUsePreviewImageStyle();

    // Access to view modes as admin should be allowed.
    $this->checkAccessViewMode($this->article, 'teaser', TRUE);
    // Access to view modes on unpublished content as admin should be allowed.
    $this->drupalGet("acquia-contenthub/display/node/{$this->unpublishedArticle->id()}/teaser");
    $this->assertResponse(200);
    $this->assertNoText($this->unpublishedArticle->label(), 'An unpublished content should not be rendered for admin user if anonymous role selected');

    // Check if the test field rendered properly.
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkFieldAccessViewMode($this->article, 'teaser');
    $this->checkCdfMarkup($this->article);
    $this->setRoleFor(AccountInterface::ANONYMOUS_ROLE);
    $this->checkFieldAccessViewMode($this->article, 'teaser', FALSE, FALSE);
    $this->setRoleFor($this->adminRole);
    $this->checkFieldAccessViewMode($this->article, 'teaser');
    $this->setRoleFor($this->adminRole);
    $this->checkFieldAccessViewMode($this->article, 'teaser', FALSE, FALSE);

    $this->setRoleFor($this->adminRole);
    // Access to view modes as admin should be allowed.
    $this->checkAccessViewMode($this->article, 'teaser', TRUE);
    // Access to view modes on unpublished content as admin should be allowed.
    $this->checkAccessViewMode($this->unpublishedArticle, 'teaser', TRUE);
    $this->drupalLogout();

    // Access to view modes as anonymous should be denied.
    $this->checkAccessViewMode($this->article, 'teaser', FALSE);
    $this->checkAccessViewMode($this->unpublishedArticle, 'teaser', FALSE);
  }

  /**
   * Ensures the CDF output is present or not depending of selected role.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to be used.
   * @param bool $access
   *   Expected result.
   */
  public function checkCdfAccess(NodeInterface $entity, $access = TRUE) {
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());
    $this->assertResponse(200);

    if ($access) {
      $this->assertTrue(isset($output['entities']['0']), 'CDF is present');
    }
    else {
      $this->assertFalse(isset($output['entities']['0']), 'CDF is not present');
    }
  }

  /**
   * Ensures the CDF output is present for expected formats.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to be used.
   */
  public function checkCdfFormat(NodeInterface $entity) {
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());
    $this->assertResponse(200, 'Accept acquia_contenthub_cdf format.');
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid(), 'CDF is present for acquia_contenthub_cdf format.');
    $output = $this->drupalGetWithFormat('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), 'html');
    $output = Json::decode($output);
    $this->assertResponse(200, 'Accept html format (browser default).');
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid(), 'CDF is present for html format.');
    $output = $this->drupalGetWithFormat('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), 'json');
    $output = Json::decode($output);
    $this->assertResponse(200, 'Accept json format.');
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid(), 'CDF is present for json format.');
    $output = $this->drupalGet('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());
    $output = Json::decode($output);
    $this->assertResponse(200, 'Accept default format (json is default for tests).');
    $this->assertEqual($output['entities']['0']['uuid'], $entity->uuid(), 'CDF is present for default format.');
    $this->drupalGetWithFormat('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id(), 'xml');
    $this->assertResponse(406, 'A 406 response was returned when XML was requested.');
  }

  /**
   * Ensures the rendered view mode have no extra markup.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to be used.
   */
  public function checkCdfMarkup(NodeInterface $entity) {
    $this->enableViewModeFor('node', 'article', ['default', 'full', 'teaser']);
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());

    // Drupal 8.5 has added off-canvas links to the main template.
    // https://www.drupal.org/project/drupal/issues/2784443
    $dialog_off_canvas_openning = ((floatval(\Drupal::VERSION) <= 8.4)) ? '' : '<div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas>';
    $dialog_off_canvas_closing = ((floatval(\Drupal::VERSION) <= 8.4)) ? '' : '</div>';

    $this->setRawContent($output['entities'][0]['metadata']['view_modes']['default']['html']);
    $this->removeWhiteSpace();
    $expected = '</head><body>' . $dialog_off_canvas_openning . '<article role="article" class="node node--type-article node--promoted node--view-mode-default"><h2><a href="/node/1" rel="bookmark"><span class="field field--name-title field--type-string field--label-hidden">';
    $this->assertRaw($expected, 'Default view mode have no extra markup.');
    $expected = '</article>' . $dialog_off_canvas_closing . '<div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'Default view mode footer JS is wrapped into div with data-content-barrier-exclude attribute.');

    $this->setRawContent($output['entities'][0]['metadata']['view_modes']['full']['html']);
    $this->removeWhiteSpace();
    $expected = '</head><body>' . $dialog_off_canvas_openning . '<article role="article" class="node node--type-article node--promoted node--view-mode-full"><h2><a href="/node/1" rel="bookmark"><span class="field field--name-title field--type-string field--label-hidden">';
    $this->assertRaw($expected, 'Full view mode have no extra markup.');
    $expected = '</article>' . $dialog_off_canvas_closing . '<div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'Full view mode footer JS is wrapped into div with data-content-barrier-exclude attribute.');

    $this->setRawContent($output['entities'][0]['metadata']['view_modes']['teaser']['html']);
    $this->removeWhiteSpace();
    $expected = '</head><body>' . $dialog_off_canvas_openning . '<article role="article" class="node node--type-article node--promoted node--view-mode-teaser"><h2><a href="/node/1" rel="bookmark"><span class="field field--name-title field--type-string field--label-hidden">';
    $this->assertRaw($expected, 'Teaser view mode have no extra markup.');
    $expected = '</article>' . $dialog_off_canvas_closing . '<div data-content-barrier-exclude="true"></div></body></html>';
    $this->assertRaw($expected, 'Teaser view mode footer JS is wrapped into div with data-content-barrier-exclude attribute.');

    $this->enableViewModeFor('node', 'article', 'teaser');
  }

  /**
   * Ensures the test field is present or not in CDF output.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to be used.
   * @param bool $access
   *   Expected result.
   * @param bool $field_access
   *   Access to the test field.
   */
  public function checkCdfFieldAccess(NodeInterface $entity, $access = TRUE, $field_access = TRUE) {
    // Tell the test module to disable access to the field.
    \Drupal::state()->set('field.test_boolean_field_access_field', $field_access ? '' : 'test_field_01');
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());
    $this->assertResponse(200);

    if ($access) {
      $this->assertTrue(isset($output['entities']['0']['attributes']['test_field_01']), 'Test Field is present');
    }
    else {
      $this->assertFalse(isset($output['entities']['0']['attributes']['test_field_01']), 'Test Field is not present');
    }
  }

  /**
   * Create some basic sample content so that we can later verify if the CDF.
   */
  public function createSampleContent() {
    // Add one article and a page.
    $this->article = $this->drupalCreateNode(['type' => 'article', 'test_field_01' => ['value' => 1]]);
    $this->page = $this->drupalCreateNode(['type' => 'page']);
    $this->unpublishedArticle = $this->drupalCreateNode(['type' => 'article', 'status' => FALSE]);
  }

  /**
   * Ensures the CDF output is what we expect it to be.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to be used.
   * @param string|null $view_mode
   *   The view mode to check in the CDF.
   */
  public function checkCdfOutput(NodeInterface $entity, $view_mode = NULL) {
    $output = $this->drupalGetCdf('acquia-contenthub-cdf/' . $entity->getEntityTypeId() . '/' . $entity->id());
    $this->assertResponse(200);
    if (!empty($view_mode)) {
      $this->assertTrue(isset($output['entities']['0']['metadata']), 'Metadata is present');
      $this->assertTrue(isset($output['entities']['0']['metadata']['view_modes'][$view_mode]), t('View mode %view_mode is present', ['%view_mode' => $view_mode]));
    }
    else {
      $this->assertFalse(isset($output['entities']['0']['metadata']), 'Metadata is not present');
    }
  }

  /**
   * Enables a view mode to be rendered in CDF.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string|array $view_mode
   *   The view mode(s) to enable.
   */
  public function enableViewModeFor($entity_type, $bundle, $view_mode) {
    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);

    $edit = [
      'entities[' . $entity_type . '][' . $bundle . '][enable_viewmodes]' => TRUE,
    ];
    if (is_array($view_mode)) {
      foreach ($view_mode as $value) {
        $edit['entities[' . $entity_type . '][' . $bundle . '][rendering][]'][$value] = $value;
      }
    }
    else {
      $edit['entities[' . $entity_type . '][' . $bundle . '][rendering][]'] = [$view_mode];
    }

    $this->drupalPostForm(NULL, $edit, $this->t('Save configuration'));
    $this->assertResponse(200);

    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertResponse(200);
  }

  /**
   * Configure and use content hub preview image style.
   */
  public function configureAndUsePreviewImageStyle() {
    $this->drupalGet('admin/config/services/acquia-contenthub/configuration');
    $this->assertRaw('admin/structure/types/manage/article#edit-acquia-contenthub', 'Preview image shortcut links exist on the page.');

    $this->drupalGet('admin/structure/types/manage/article');
    $this->assertText(t('Acquia Content Hub'));
  }

  /**
   * Checks access to View Modes endpoint.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   * @param bool $access
   *   Expected result.
   */
  public function checkAccessViewMode(NodeInterface $entity, $view_mode, $access = TRUE) {
    $this->drupalGet("acquia-contenthub/display/node/{$entity->id()}/{$view_mode}");
    if ($access) {
      $this->assertResponse(200);
      $this->assertText($entity->label());
    }
    else {
      $this->assertResponse(403);
    }
  }

  /**
   * Checks if the Test Field rendered at View Modes endpoint.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   * @param bool $access
   *   Expected result.
   * @param bool $field_access
   *   Access to the field.
   */
  public function checkFieldAccessViewMode(NodeInterface $entity, $view_mode, $access = TRUE, $field_access = TRUE) {
    // Tell the test module to disable access to the field.
    \Drupal::state()->set('field.test_boolean_field_access_field', $field_access ? '' : 'test_field_01');
    $this->drupalGet("acquia-contenthub/display/node/{$entity->id()}/{$view_mode}");
    $this->assertResponse(200);
    if ($access) {
      $this->assertText('field_test_01_on');
    }
    else {
      $this->assertNoText('field_test_01_on');
    }
  }

  /**
   * Returns list of the roles with security implications.
   *
   * @return array
   *   Array of roles.
   */
  public function getUserWarningRole() {
    $markup = $this->xpath('//div[@id="user-warning-role"]');
    $states = (array) json_decode($markup[0]['data-drupal-states']);
    $visible_roles = [];

    if (isset($states['visible'])) {
      foreach ($states['visible'] as $state) {
        $visible_role = (array) $state;
        $visible_roles[] = $visible_role[':input[name="user_role"]']->value;
      }

      return array_flip($visible_roles);
    }

    return [];
  }

}
