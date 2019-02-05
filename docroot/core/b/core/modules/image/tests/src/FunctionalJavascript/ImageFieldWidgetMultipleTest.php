<?php

namespace Drupal\Tests\image\FunctionalJavascript;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests the image field widget with multiple ajax uploads.
 *
 * @group image
 */
class ImageFieldWidgetMultipleTest extends JavascriptTestBase {

  use ImageFieldCreationTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $minkDefaultDriverClass = DrupalSelenium2Driver::class;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'image', 'field_ui'];

  /**
   * Tests file widget element allowing multiple images.
   */
  public function testWidgetElementMultipleUploads() {
    $image_factory = \Drupal::service('image.factory');
    $file_system = \Drupal::service('file_system');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $field_name = 'images';
    $storage_settings = ['cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED];
    $field_settings = ['alt_field_required' => 0];
    $this->createImageField($field_name, 'article', $storage_settings, $field_settings);
    $this->drupalLogin($this->drupalCreateUser(['access content', 'create article content']));
    $this->drupalGet('node/add/article');
    $this->xpath('//input[@name="title[0][value]"]')[0]->setValue('Test');

    $images = $this->getTestFiles('image');
    $test_images = [
      $file_system->realpath($images[5]->uri),
      $file_system->realpath($images[7]->uri),
    ];

    // Need for a trick to work around the problem of uploading remote files.
    $remote_paths = [];
    foreach ($test_images as $path) {
      $remote_paths[] = $this->uploadFileRemotePath($path);
    }
    $multiple_field = $this->xpath('//input[@multiple]')[0];
    $multiple_field->setValue(implode("\n", $remote_paths));

    $this->getSession()->getPage()->findButton('Save')->click();

    $node = Node::load(1);
    foreach ($test_images as $delta => $test_image_path) {
      $node_image = $node->{$field_name}[$delta];
      $original_image = $image_factory->get($test_image_path);
      $this->assertEquals($node_image->width, $original_image->getWidth(), "Correct width of image #$delta");
      $this->assertEquals($node_image->height, $original_image->getHeight(), "Correct height of image #$delta");
    }
  }

  /**
   * Uploads a file to the Selenium instance for get remote path.
   *
   * Copied from \Behat\Mink\Driver\Selenium2Driver::uploadFile().
   *
   * @param string $path
   *   The path to the file to upload.
   *
   * @return string
   *   The remote path.
   *
   * @todo: Remove after https://www.drupal.org/project/drupal/issues/2947517.
   */
  protected function uploadFileRemotePath($path) {
    $tempFilename = tempnam('', 'WebDriverZip');
    $archive = new \ZipArchive();
    $archive->open($tempFilename, \ZipArchive::CREATE);
    $archive->addFile($path, basename($path));
    $archive->close();
    $remotePath = $this->getSession()->getDriver()->getWebDriverSession()->file(array('file' => base64_encode(file_get_contents($tempFilename))));
    unlink($tempFilename);
    return $remotePath;
  }

}
