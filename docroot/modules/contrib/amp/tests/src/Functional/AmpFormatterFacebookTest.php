<?php

namespace Drupal\Tests\amp\Functional;

/**
 * Tests AMP Social Post Formatter.
 *
 * @group amp
 */
class AmpFormatterFacebookTest extends AmpFormatterTestBase {

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
    'provider' => ['facebook' => 'facebook'],
    'data-embed-as' => 'post',
    'data-align-center' => '',
    'placeholder' => '',
  ];

  /**
   * {@inheritdoc}
   */
  protected $ampElementName = 'amp-facebook';

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
      ['value' => 'https://www.facebook.com/zuck/posts/10101301165605491'],
      ['value' => 'https://www.facebook.com/20531316728/posts/10154009990506729'],
    ];

    // Create a new node with an social post url.
    $this->node->set($this->fieldName, $values)->save();

    // Test subparts of markup to avoid failures due to line breaks.
    $this->valuesOut = [
      '<amp-facebook layout="responsive" data-embed-as="post" height="9" width="16" data-href="https://www.facebook.com/zuck/posts/10101301165605491">',
      '</amp-facebook>',
      '<amp-facebook layout="responsive" data-embed-as="post" height="9" width="16" data-href="https://www.facebook.com/20531316728/posts/10154009990506729">',
      '</amp-facebook>',
    ];

  }

}
