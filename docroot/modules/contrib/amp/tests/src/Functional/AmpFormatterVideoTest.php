<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\file\Entity\File;
use Drupal\Tests\file\Functional\FileFieldCreationTrait;
use Drupal\Core\StreamWrapper\PublicStream;

/**
 * Tests AMP Video Formatter.
 *
 * @group amp
 */
class AmpFormatterVideoTest extends AmpFormatterTestBase {

  use FileFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $removedElementName = 'video';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterType = 'amp_video';

  /**
   * {@inheritdoc}
   */
  protected $ampFormatterSettings = [
    'height' => 9,
    'width' => 16,
    'layout' => 'responsive',
    'autoplay' => FALSE,
    'controls' => FALSE,
    'loop' => FALSE,
  ];

  /**
   * {@inheritdoc}
   */
  protected $ampElementName = 'amp-video';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();

    // Add field to the test content type.
    $fieldSettings = [
      'file_directory' => 'testing',
      'file_extensions' => 'mp4',
    ];
    $this->createFileField($this->fieldName, 'node', $this->contentType, [], $fieldSettings);
    $this->configureDisplay();

  }

  /**
   * {@inheritdoc}
   */
  public function createAmpNode() {

    parent::createAmpNode();

    $file_system = \Drupal::service('file_system');
    $source_path = 'https://amp.dev/static/samples/video/tokyo.mp4';
    $video_path = system_retrieve_file($source_path, PublicStream::basePath());
    $file = File::create([
      'uri' => $video_path,
    ]);
    $file->save();

    // Create a new node with video attached.
    $this->node->set($this->fieldName, [
      'target_id' => $file->id(),
    ])->save();

    // Test subparts of markup to avoid failures due to line breaks.
    $file_path = $file->createFileUrl();
    $this->valuesOut = [
      '<amp-video',
      'src="' . $file_path . '"',
      '<div fallback>',
      '<p>Your browser doesn’t support HTML5 video</p>',
      '</div>',
      '</amp-video>',
    ];

  }

}
