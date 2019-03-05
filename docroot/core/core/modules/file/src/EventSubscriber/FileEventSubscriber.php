<?php

namespace Drupal\file\EventSubscriber;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\Event\FileUploadSanitizeNameEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FileEventSubscriber.
 *
 * @package Drupal\file\EventSubscriber
 */
class FileEventSubscriber implements EventSubscriberInterface {

  /**
   * The file configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * Constructs a new file event listener.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TransliterationInterface $transliteration) {
    $this->config = $config_factory->get('file.settings');
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FileUploadSanitizeNameEvent::SANITIZE => 'sanitizeFilename',
    ];
  }

  /**
   * Sanitize the filename of a file being uploaded.
   *
   * @param \Drupal\file\Event\FileUploadSanitizeNameEvent $event
   *   File upload sanitize name event.
   *
   * @see file_form_system_file_system_settings_alter()
   */
  public function sanitizeFilename(FileUploadSanitizeNameEvent $event) {
    $filename = $event->getFilename();
    $language_id = $event->getLanguageId();
    // Sanitize the filename according to configuration.
    $sanitization_options = $this->config->get('filename_sanitization');
    if ($sanitization_options['transliterate']) {
      $filename = $this->transliteration->transliterate($filename, $language_id, $sanitization_options['replacement_character']);
      if (mb_strlen($filename) === 0) {
        // If transliteration has resulted in a zero length string enable the
        // 'replace_non_alphanumeric' option and start again.
        $filename = $event->getFilename();
        $sanitization_options['replace_non_alphanumeric'] = TRUE;
      }
    }
    if ($sanitization_options['replace_whitespace']) {
      $filename = preg_replace('/\s/u', $sanitization_options['replacement_character'], trim($filename));
    }
    // Only honor replace_non_alphanumeric if transliterate is enabled.
    if ($sanitization_options['transliterate'] && $sanitization_options['replace_non_alphanumeric']) {
      $filename = preg_replace('/[^0-9A-Za-z_.-]/u', $sanitization_options['replacement_character'], $filename);
    }
    if ($sanitization_options['dedupe_separators']) {
      $filename = preg_replace('/(_)_+|(\.)\.+|(-)-+/u', '\\1\\2\\3', $filename);
      // If there is an extension remove dots from the end of the filename to
      // prevent duplicate dots.
      if ($event->getExtension() !== '') {
        $filename = rtrim($filename, '.');
      }
    }
    if ($sanitization_options['lowercase']) {
      // Force lowercase to prevent issues on case-insensitive file systems.
      $filename = mb_strtolower($filename);
    }
    $event->setFilename($filename);
  }

}
