<?php

namespace Drupal\Tests\file\Unit;

use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\file\Event\FileUploadSanitizeNameEvent;
use Drupal\file\EventSubscriber\FileEventSubscriber;
use Drupal\Tests\UnitTestCase;

/**
 * Filename sanitization tests.
 *
 * @group file
 */
class SanitizeNameTest extends UnitTestCase {

  /**
   * Test file name sanitization
   *
   * @param string $original
   *   The original filename.
   * @param string $expected
   *   The expected filename.
   * @param array $options
   *   Array of filename sanitization options, in this order:
   *   0: boolean Transliterate.
   *   1: string Replace whitespace.
   *   2: string Replace non-alphanumeric characters.
   *   3: boolean De-duplicate separators.
   *   4: boolean Convert to lowercase.
   * @param string $language_id
   *   Optional language code for transliteration. Defaults to 'en'.
   *
   * @dataProvider provideFilenames
   *
   * @covers \Drupal\file\EventSubscriber\FileEventSubscriber::sanitizeFilename
   * @covers \Drupal\file\Event\FileUploadSanitizeNameEvent::__construct
   */
  public function testFileNameTransliteration($original, $expected, array $options, $language_id = 'en') {
    $sanitization_options = [
      'transliterate' => $options[0],
      'replacement_character' => $options[1],
      'replace_whitespace' => $options[2],
      'replace_non_alphanumeric' => $options[3],
      'dedupe_separators' => $options[4],
      'lowercase' => $options[5],
    ];
    $config_factory = $this->getConfigFactoryStub([
      'file.settings' => [
        'filename_sanitization' => $sanitization_options,
      ],
    ]);

    $event = new FileUploadSanitizeNameEvent($original, $language_id);
    $subscriber = new FileEventSubscriber($config_factory, new PhpTransliteration());
    $subscriber->sanitizeFilename($event);

    // Check the results of the configured sanitization.
    $this->assertEquals($expected, $event->getFilenameWithExtension());
  }

  /**
   * Provides data for testFileNameTransliteration().
   *
   * @return array
   *   Arrays with original name, expected name, and sanitization options.
   */
  public function provideFilenames() {
    return [
      'Test default options' => [
        'TÉXT-œ.txt',
        'TÉXT-œ.txt',
        [FALSE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test raw file without extension' => [
        'TÉXT-œ',
        'TÉXT-œ',
        [FALSE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test only transliteration: simple' => [
        'ÁTÉXT-œ.txt',
        'ATEXT-oe.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test only transliteration: raw file without extension' => [
        'ÁTÉXT-œ',
        'ATEXT-oe',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test only transliteration: complex and replace (-)' => [
        'S  Pácê--táb#	#--🙈.jpg',
        'S  Pace--tab#	#---.jpg',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test only transliteration: complex and replace (_)' => [
        'S  Pácê--táb#	#--🙈.jpg',
        'S  Pace--tab#	#--_.jpg',
        [TRUE, '_', FALSE, FALSE, FALSE, FALSE],
      ],
      'Test transliteration, replace (-) and replace whitespace (trim front)' => [
        '  S  Pácê--táb#	#--🙈.png',
        'S--Pace--tab#-#---.png',
        [TRUE, '-', TRUE, FALSE, FALSE, FALSE],
      ],
      'Test transliteration, replace (-) and replace whitespace (trim both sides)' => [
        '  S  Pácê--táb#	#--🙈   .jpg',
        'S--Pace--tab#-#---.jpg',
        [TRUE, '-', TRUE, FALSE, FALSE, FALSE],
      ],
      'Test transliteration, replace (_) and replace whitespace (trim both sides)' => [
        '  S  Pácê--táb#	#--🙈  .jpg',
        'S__Pace--tab#_#--_.jpg',
        [TRUE, '_', TRUE, FALSE, FALSE, FALSE],
      ],
      'Test transliteration, replace (_), replace whitespace and replace non-alphanumeric' => [
        '  S  Pácê--táb#	#--🙈.txt',
        'S__Pace--tab___--_.txt',
        [TRUE, '_', TRUE, TRUE, FALSE, FALSE],
      ],
      'Test transliteration, replace (-), replace whitespace and replace non-alphanumeric' => [
        '  S  Pácê--táb#	#--🙈.txt',
        'S--Pace--tab------.txt',
        [TRUE, '-', TRUE, TRUE, FALSE, FALSE],
      ],
      'Test transliteration, replace (-), replace whitespace, replace non-alphanumeric and deduplicating separators' => [
        'S  Pácê--táb#	#--🙈.txt',
        'S-Pace-tab-.txt',
        [TRUE, '-', TRUE, TRUE, TRUE, FALSE],
      ],
      'Test transliteration, replace (-), replace whitespace and dedupe separators' => [
        '  S  Pácê--táb#	#--🙈.txt',
        'S-Pace-tab#-#-.txt',
        [TRUE, '-', TRUE, FALSE, TRUE, FALSE],
      ],
      'Test transliteration, replace (_), replace whitespace, replace non-alphanumeric and dedupe separators' => [
        'S  Pácê--táb#	#--🙈.txt',
        'S_Pace-tab_-_.txt',
        [TRUE, '_', TRUE, TRUE, TRUE, FALSE],
      ],
      'Test transliteration, replace (-), replace whitespace, replace non-alphanumeric, dedupe separators and lowercase conversion' => [
        'S  Pácê--táb#	#--🙈.jpg',
        's-pace-tab-.jpg',
        [TRUE, '-', TRUE, TRUE, TRUE, TRUE],
      ],
      'Test transliteration, replace (_), replace whitespace, replace non-alphanumeric, dedupe separators and lowercase conversion' => [
        'S  Pácê--táb#	#--🙈.txt',
        's_pace-tab_-_.txt',
        [TRUE, '_', TRUE, TRUE, TRUE, TRUE],
      ],
      'Ignore non-alphanumeric replacement if transliteration is not set, but still replace whitespace, deduplicate separators, and lowercase' => [
        '  2S  Pácê--táb#	#--🙈.txt',
        '2s-pácê-táb#-#-🙈.txt',
        [FALSE, '-', TRUE, TRUE, TRUE, TRUE],
      ],
      'Only lowercase, simple' => [
        'TEXT.txt',
        'text.txt',
        [FALSE, '-', FALSE, FALSE, FALSE, TRUE],
      ],
      'Only lowercase, with unicode' => [
        'TÉXT.txt',
        'téxt.txt',
        [FALSE, '-', FALSE, FALSE, FALSE, TRUE],
      ],
      'No transformations' => [
        'Ä Ö Ü Å Ø äöüåøhello.txt',
        'Ä Ö Ü Å Ø äöüåøhello.txt',
        [FALSE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Transliterate via en (not de), no other transformations' => [
        'Ä Ö Ü Å Ø äöüåøhello.txt',
        'A O U A O aouaohello.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Transliterate via de (not en), no other transformations' => [
        'Ä Ö Ü Å Ø äöüåøhello.txt',
        'Ae Oe Ue A O aeoeueaohello.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE], 'de',
      ],
      'Transliterate via de not en, plus whitespace + lowercase' => [
        'Ä Ö Ü Å Ø äöüåøhello.txt',
        'ae-oe-ue-a-o-aeoeueaohello.txt',
        [TRUE, '-', TRUE, FALSE, FALSE, TRUE], 'de',
      ],
      'Remove duplicate separators with falsey extension' => [
        'foo.....0',
        'foo.0',
        [FALSE, '-', FALSE, FALSE, TRUE, FALSE],
      ],
      'Remove duplicate separators with extension and ending in dot' => [
        'foo.....txt',
        'foo.txt',
        [FALSE, '-', FALSE, FALSE, TRUE, FALSE],
      ],
      'Remove duplicate separators without extension and ending in dot' => [
        'foo.....',
        'foo.',
        [FALSE, '-', FALSE, FALSE, TRUE, FALSE],
      ],
      'All unknown unicode' => [
        '🙈🙈🙈.txt',
        '---.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      '✓ unicode' => [
        '✓.txt',
        '-.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],
      'Multiple ✓ unicode' => [
        '✓✓✓.txt',
        '---.txt',
        [TRUE, '-', FALSE, FALSE, FALSE, FALSE],
      ],

    ];
  }

}
