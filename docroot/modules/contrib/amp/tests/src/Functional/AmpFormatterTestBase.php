<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP Formatters.
 *
 * @group amp
 */
abstract class AmpFormatterTestBase extends AmpTestBase {

  /**
   * The field name the formatter will be applied to.
   *
   * Set to 'body' if the formatter will be used on the existing body field,
   * otherwise leave empty.
   *
   * @var string
   */
  protected $fieldName = '';

  /**
   * The element created by the default formatter.
   *
   * Leave empty when the default element is not removed by AMP.
   *
   * @var string
   */
  protected $removedElementName = '';

  /**
   * The AMP formatter type used in the test.
   *
   * @var string
   */
  protected $ampFormatterType = '';

  /**
   * The AMP formatter_settings.
   *
   * @var string
   */
  protected $ampFormatterSettings = [];

  /**
   * The element name created by the AMP formatter.
   *
   * Leave empty if the AMP formatter is not creating a new, unique, element.
   *
   * @var string
   */
  protected $ampElementName = '';

  /**
   * The output values created by the AMP formatter.
   *
   * Leave empty if not specifying expected output values.
   *
   * @var array
   */
  protected $valuesOut = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    if (empty($this->fieldName)) {
      $this->fieldName = strtolower($this->randomMachineName());
    }

  }

  /**
   * Configure the view display.
   *
   * Configures the default display and the AMP display.
   */
  protected function configureDisplay() {

    $this->displayRepository->getViewDisplay('node', $this->contentType, 'amp')
      ->removeComponent($this->fieldName)
      ->setComponent($this->fieldName, [
        'type' => $this->ampFormatterType,
        'settings' => $this->ampFormatterSettings,
      ])->save();
  }

  /**
   * Test that the AMP formatter works.
   */
  public function testFormatter() {

    // Create a node with an image to test AMP formatter.
    $this->createAmpNode();

    // Check the normal page is visible and contains the field.
    $this->drupalGet($this->nodeUrl());
    $this->assertSession()->statusCodeEquals(200);
    if (!empty($this->ampElementName)) {
      $this->assertSession()->responseNotContains('<' . $this->ampElementName);
    }

    // Check AMP page is visible and contains the expected markup.
    $this->drupalGet($this->nodeAmpUrl());
    $this->assertSession()->statusCodeEquals(200);
    if (!empty($this->ampElementName)) {
      $this->assertSession()->responseContains('<' . $this->ampElementName);
    }
    if (!empty($this->removedElementName)) {
      $this->assertSession()->responseNotContains('<' . $this->removedElementName);
    }
    if (!empty($this->valuesOut)) {
      foreach ($this->valuesOut as $value) {
        $this->assertSession()->responseContains($value);
      }
    }

  }

}
