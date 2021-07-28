<?php

namespace Drupal\acquia_contenthub\Tests;

/**
 * Test Acquia Content Hub node reference.
 *
 * @group acquia_contenthub
 */
class ViewModesUtf8EncodedTest extends WebTestBase {

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'access content',
    'access administration pages',
    'administer site configuration',
    'administer content types',
    'administer languages',
    'translate interface',
    'translate any entity',
    'administer content translation',
    'create content translations',
    'update content translations',
    'administer nodes',
    'administer acquia content hub',
    'access administration pages',
  ];

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'node',
    'acquia_contenthub',
    'locale',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create the users used for the tests.
    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
    $this->drupalLogin($this->adminUser);

    $this->configureContentHubContentTypes('node', ['article']);
    $this->enableViewModeFor('node', 'article', 'teaser');
  }

  /**
   * Configure content hub node form.
   */
  public function testUTF8EncodedViewModes() { // @codingStandardsIgnoreLine
    // Adding portuguese language.
    $this->drupalGet('admin/config/regional/language/add');
    $this->assertResponse(200);
    $edit = [
      'predefined_langcode' => 'pt-pt',
    ];
    $this->drupalPostForm(NULL, $edit, t('Add language'));
    $this->assertResponse(200);

    $this->drupalGet('admin/config/regional/language');
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/types/manage/article');
    $edit = [
      'language_configuration[langcode]' => 'pt-pt',
      'language_configuration[language_alterable]' => 1,
      'language_configuration[content_translation]' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save content type'));
    $this->assertResponse(200);

    $this->drupalGet('node/add/article');
    $this->assertResponse(200);
    $title = 'Salas de isolamento e contactos de urgência. Escolas com orientações para planos de contingência';
    $body = 'A Direção-Geral dos Estabelecimentos de Ensino enviou recomendações a todas as escolas, que devem desenvolver os seus próprios planos de contingência nos próximos dias para o covid-19. Duas das nove pessoas infetadas em Portugal são professores.';
    $edit = [
      'title[0][value]' => $title,
      'body[0][value]' => $body,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet("acquia-contenthub/display/node/1/teaser");
    $this->assertResponse(200);
    $text = $this->xpath('/html/body/article/h2/a/span/text()');
    $view_mode_title = $text[0]->__toString();
    $text = $this->xpath('/html/body/article/div/div[2]/p/text()');
    $view_mode_body = $text[0]->__toString();

    $this->assertEqual($title, $view_mode_title);
    $this->assertEqual($body, $view_mode_body);
  }

}
