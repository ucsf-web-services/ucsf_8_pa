<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP view mode.
 *
 * @group amp
 */
class AmpFormatterTextTest extends AmpFormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldName = 'body';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_text';

  /**
   * {@inheritdoc}
   */
  public function createAmpNode() {

    parent::createAmpNode();

    // Create some input/output values.
    $header = '<h2>AMP body transform</h2>';
    $text = $this->bodyText();
    $image = trim(file_get_contents($this->fixturesPath . '/img-test-fragment.html'));
    $amp_image = trim(file_get_contents($this->fixturesPath . '/img-test-fragment.html.out'));
    $social = trim(file_get_contents($this->fixturesPath . '/facebook-iframe-fragment.html'));
    $amp_social = trim(file_get_contents($this->fixturesPath . '/facebook-iframe-fragment.html.out'));

    $values = [
      [
        'value' => $header . $text . $image . $social,
        'format' => 'full_html',
      ],
    ];
    $this->node->set($this->fieldName, $values)->save();

    $this->valuesOut = [
      $header,
      $amp_image,
      $amp_social,
    ];

  }

}
