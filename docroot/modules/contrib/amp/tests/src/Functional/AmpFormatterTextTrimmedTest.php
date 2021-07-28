<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP view mode.
 *
 * @group amp
 */
class AmpFormatterTextTrimmedTest extends AmpFormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldName = 'body';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_text_trimmed';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterSettings = [
    'trim_length' => 600,
  ];

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
        'value' => $header . $image . $text . $social,
        'format' => 'full_html',
      ],
    ];
    $this->node->set($this->fieldName, $values)->save();

    $this->valuesOut = [
      text_summary($header . $amp_image . $text . $amp_social, 'full_html', 600),
    ];

  }

}
