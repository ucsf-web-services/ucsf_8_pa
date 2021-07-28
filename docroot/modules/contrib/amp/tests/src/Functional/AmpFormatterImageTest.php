<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;

/**
 * Tests AMP Image Formatter.
 *
 * @group amp
 */
class AmpFormatterImageTest extends AmpFormatterTestBase {

  use TestFileCreationTrait;
  use ImageFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $removedElementName = 'img';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_image';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterSettings = [
    'layout' => 'responsive',
    'width' => 16,
    'height' => 9,
  ];

  /**
   * {@inheritdoc}
   */
  protected $ampElementName = 'amp-img';

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

  }

}
