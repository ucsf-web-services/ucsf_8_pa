<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests AMP Media Image formatter.
 *
 * @group ampnew
 */
class AmpFormatterMediaImageTest extends AmpFormatterTestBase {

  use TestFileCreationTrait;
  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $removedElementName = 'img';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_media';

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

    // Create an image media type.
    $values = [
      'id' => 'image',
      'label' => 'Image',
      'new_revision' => FALSE,
    ];
    $media_type = $this->createMediaType('image', $values);
    drupal_flush_all_caches();

    // Create a media field.
    $target_entity_type = 'media';
    $target_bundles = [
      'image',
    ];
    $formatter_type = 'entity_reference_entity_view';
    $formatter_settings = [
      'view_mode' => 'full',
      'link' => FALSE,
    ];
    $this->createEntityReferenceField($this->fieldName, $target_entity_type, $target_bundles, $formatter_type, $formatter_settings);
    $this->configureDisplay();

  }

  /**
   * {@inheritdoc}
   */
  public function createAmpNode() {

    // Create a node with a media image to test AMP media formatter.
    parent::createAmpNode();

    $images = $this->getTestFiles('image');
    $image = array_pop($images);
    $file = File::create([
      'uri' => $image->uri,
    ]);
    $file->save();

    // Create media.
    $media_field_name = $this->config('media.type.image')
      ->get('source_configuration.source_field');
    $media = Media::create([
      'bundle' => 'image',
      'name' => $file->id(),
      $media_field_name => $file,
    ]);
    $media->save();

    // Create a new node with reference to media.
    $this->node->set($this->fieldName, [
      'target_id' => $media->id(),
    ])->save();

    // Test subparts of markup to avoid failures due to line breaks.
    $file_path = $file->createFileUrl();

    $this->valuesOut = [
      '<amp-img layout="responsive" width="16" height="9" src="' . $file_path . '">',
      '</amp-img>',
    ];

  }

}
