<?php

namespace Drupal\ucsf_edu_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin creates a new paragraph entity based on the source.
 *
 * @MigrateProcessPlugin(
 *   id = "mds_paragraph"
 * )
 */
class ParagraphMigrateProcessor extends ParagraphProcessBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $paragraph = $this->getParagraph($row, $destination_property, 0);

    $paragraph->set('field_text', ['value' => $value, 'format' => $format]);
    $paragraph->save();
    return $paragraph;
  }
}