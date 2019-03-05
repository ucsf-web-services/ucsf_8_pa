<?php

namespace Drupal\file\Event;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * An event during file upload that lets subscribers sanitize the filename.
 *
 * The original file extension is preserved, and when dispatching this event,
 * the caller should use self::getFilenameWithExtension() to get the full
 * filename. self::getFilename() and self::setFilename() only operate on the
 * part of the filename without the extension (what pathinfo() returns via
 * PATHINFO_FILENAME). The original extension and language ID of the uploaded
 * file are passed as context, but cannot be modified by subscribers.
 *
 * @see pathinfo()
 * @see _file_save_upload_single()
 * @see \Drupal\file\Plugin\rest\resource\FileUploadResource::prepareFilename()
 * @see \Drupal\Core\File\FileSystemInterface::basename()
 */
class FileUploadSanitizeNameEvent extends SymfonyEvent {

  /**
   * Name of the event fired when uploading a file to sanitize the filename.
   *
   * @Event
   *
   * @var string
   */
  const SANITIZE = 'file.upload.sanitize.name';

  /**
   * The filename (without extension) being uploaded.
   *
   * @var string
   */
  protected $filename;

  /**
   * The (read-only) extension of the file being uploaded.
   *
   * @var string
   */
  protected $extension;

  /**
   * The language ID of the file being uploaded.
   *
   * @var string
   */
  protected $languageId;

  /**
   * Constructs a file upload event object.
   *
   * @param string $filename
   *   The full filename (with extension, but not directory) being uploaded.
   * @param string $language_id
   *   The language ID of the file being uploaded.
   */
  public function __construct($filename, $language_id) {
    $this->extension = pathinfo($filename, PATHINFO_EXTENSION);
    if ($this->extension !== '') {
      // Remove the extension using preg_replace() to prevent issues with
      // multi-byte characters in filenames due to
      // https://bugs.php.net/bug.php?id=77239.
      $this->filename = preg_replace('@' . preg_quote('.' . $this->extension, '@') . '$@u', '', $filename);
    }
    else {
      $this->filename = $filename;
    }
    $this->languageId = $language_id;
  }

  /**
   * Gets the file extension.
   *
   * @return string
   *   The extension (if any) of the file being uploaded.
   */
  public function getExtension() {
    return $this->extension;
  }

  /**
   * Gets the filename (without extension).
   *
   * @return string
   *   The filename (without extension) to use.
   */
  public function getFilename() {
    return $this->filename;
  }

  /**
   * Gets the full filename with extension (if any).
   *
   * @return string
   *   The full filename to use.
   */
  public function getFilenameWithExtension() {
    return $this->filename . ($this->extension !== '' ? ('.' . $this->extension) : '');
  }

  /**
   * Gets the language ID.
   *
   * @return string
   *   The lanuage ID of the file being uploaded.
   */
  public function getLanguageId() {
    return $this->languageId;
  }

  /**
   * Sets the filename.
   *
   * @param string $filename
   *   The filename to use for the uploaded file.
   */
  public function setFilename($filename) {
    $this->filename = $filename;
  }

}
