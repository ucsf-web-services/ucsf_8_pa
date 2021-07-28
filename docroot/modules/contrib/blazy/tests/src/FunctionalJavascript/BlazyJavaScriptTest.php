<?php

namespace Drupal\Tests\blazy\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Drupal\Tests\blazy\Traits\BlazyCreationTestTrait;

/**
 * Tests the Blazy JavaScript using PhantomJS, or Chromedriver.
 *
 * @group blazy
 */
class BlazyJavaScriptTest extends WebDriverTestBase {

  use BlazyUnitTestTrait;
  use BlazyCreationTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'filter',
    'image',
    'node',
    'text',
    'blazy',
    'blazy_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();

    $this->root                   = $this->container->get('app.root');
    $this->fileSystem             = $this->container->get('file_system');
    $this->entityManager          = $this->container->get('entity.manager');
    $this->entityFieldManager     = $this->container->get('entity_field.manager');
    $this->formatterPluginManager = $this->container->get('plugin.manager.field.formatter');
    $this->blazyAdmin             = $this->container->get('blazy.admin');
    $this->blazyManager           = $this->container->get('blazy.manager');
    $this->scriptLoader           = 'blazy';
    $this->maxParagraphs          = 180;
  }

  /**
   * Test the Blazy element from loading to loaded states.
   */
  public function testFormatterDisplay() {
    $data['settings']['blazy'] = TRUE;
    $data['settings']['ratio'] = '';
    $data['settings']['image_style'] = 'thumbnail';

    $this->setUpContentTypeTest($this->bundle);
    $this->setUpFormatterDisplay($this->bundle, $data);
    $this->setUpContentWithItems($this->bundle);
    $image_path = $this->getImagePath(TRUE);

    $this->drupalGet('node/' . $this->entity->id());

    // Capture the initial page load moment.
    $this->createScreenshot($image_path . '/' . $this->scriptLoader . '_1_initial.png');
    $this->assertSession()->elementExists('css', '.b-lazy');

    // Trigger Blazy to load images by scrolling down window.
    $this->getSession()->executeScript('window.scrollTo(0, document.body.scrollHeight);');

    // Capture the loading moment after scrolling down the window.
    $this->createScreenshot($image_path . '/' . $this->scriptLoader . '_2_loading.png');

    // Wait a moment.
    $this->getSession()->wait(3000);

    // Verifies that one of the images is there once loaded.
    $this->assertNotEmpty($this->assertSession()->waitForElement('css', '.b-loaded'));

    // Capture the loaded moment.
    // The screenshots are at sites/default/files/simpletest/blazy.
    $this->createScreenshot($image_path . '/' . $this->scriptLoader . '_3_loaded.png');
  }

}
