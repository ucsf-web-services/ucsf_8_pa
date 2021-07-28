<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;

/**
 * Tests AMP Image Carousel formatter.
 *
 * @group amp
 */
class AmpFormatterImageCarouselTest extends AmpFormatterTestBase {

  use TestFileCreationTrait;
  use ImageFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $removedElementName = 'img';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_image_carousel';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterSettings = [
    'type' => 'slides',
    'layout' => 'responsive',
    'width' => 16,
    'height' => 9,
    'autoplay' => FALSE,
    'controls' => FALSE,
    'loop' => FALSE,
  ];

  /**
   * {@inheritdoc}
   */
  protected $ampElementName = 'amp-carousel';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();

    // Add field to the test content type.
    $fieldSettings = [
      'max_resolution' => '100x100',
      'min_resolution' => '50x50',
      'alt_field' => 1,
      'file_directory' => 'testing',
    ];
    $this->createImageField($this->fieldName, $this->contentType, [], $fieldSettings);
    $this->configureDisplay();

  }

  /**
   * {@inheritdoc}
   */
  public function createAmpNode() {

    // Create a node with an image to test AMP image formatter.
    parent::createAmpNode();

    $images = $this->getTestFiles('image');
    $image = array_pop($images);
    $file = File::create([
      'uri' => $image->uri,
    ]);
    $file->save();

    // Create a new node with an image attached.
    $this->node->set($this->fieldName, [
      'target_id' => $file->id(),
      'alt' => 'alt text',
    ])->save();

    // Test subparts of markup to avoid failures due to line breaks.
    $file_path = $file->createFileUrl();
    $this->valuesOut = [
      '<amp-img layout="responsive" width="16" height="9" src="' . $file_path . '" alt="alt text">',
      '</amp-img>',
    ];
    $this->drupalGet($file_path);

  }

}
