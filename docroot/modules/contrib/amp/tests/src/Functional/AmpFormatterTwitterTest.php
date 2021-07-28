<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP Social Post Formatter.
 *
 * @group amp
 */
class AmpFormatterTwitterTest extends AmpFormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_social_post_formatter';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterSettings = [
    'layout' => 'responsive',
    'width' => 16,
    'height' => 9,
    'provider' => ['twitter' => 'twitter'],
    'data-embed-as' => 'post',
    'data-align-center' => '',
    'placeholder' => '',
  ];

  /**
   * {@inheritdoc}
   */
  protected $ampElementName = 'amp-twitter';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add field to the test content type.
    $this->createTextField($this->fieldName);
    $this->configureDisplay();

  }

  /**
   * {@inheritdoc}
   */
  public function createAmpNode() {

    parent::createAmpNode();

    $values = [
      ['value' => 'https://twitter.com/andyserkis/status/704420904437043200'],
      ['value' => 'https://twitter.com/andyserkis/statuses/704420904437043200'],
    ];

    // Create a new node with an social post url.
    $this->node->set($this->fieldName, $values)->save();

    // Test subparts of markup to avoid failures due to line breaks.
    $this->valuesOut = [
      '<amp-twitter layout="responsive" data-embed-as="post" height="9" width="16" data-tweetid="704420904437043200">',
      '</amp-twitter>',
      '<amp-twitter layout="responsive" data-embed-as="post" height="9" width="16" data-tweetid="704420904437043200">',
      '</amp-twitter>',
    ];

  }

}
