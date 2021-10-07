<?php

namespace Drupal\ucsf_edu_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
/**
 * Base class for paragraph process plugins.
 */
class ParagraphProcessBase extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $paragraphs = [];
    foreach ($value[0] as $delta => $item) {
      $paragraph = $this->getParagraph($row, $destination_property, $delta);
      // Loop over all properties.
      foreach ($this->configuration['source'] as $destination => $source) {
        // Fetch the right delta, skip if it doesn't exist.
        $source_items = $row->getSourceProperty($source);
        if (!isset($source_items[$delta])) {
          continue;
        }
        $source_item = $source_items[$delta];
        $destination_value = NULL;
        if (isset($source_item['nid'])) {
          $destination_value = $source_item['nid'];
        }
        elseif (isset($source_item['value'])) {
          $destination_value = $source_item['value'];
        }
        if ($destination_value) {
          $paragraph->$destination->appendItem($destination_value);
        }
      }
      $paragraph->save();
      $paragraphs[]['entity'] = $paragraph;
    }
    return $paragraphs;
  }
  /**
   * Creates a new or returns an existing paragraph for the target node.
   *
   * @param \Drupal\migrate\Row $row
   *   The migration row.
   * @param string $field_name
   *   The field name.
   * @param int $delta
   *   The field delta.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   The paragraph.
   */
  protected function getParagraph(Row $row, $field_name, $delta) {
    // Attempt to fetch an existing paragraph if the target node already
    // exists, then get the translation. Otherwise create a new.
    if ($row->getDestinationProperty('nid') && $node = Node::load($row->getDestinationProperty('nid'))) {
      if (isset($node->$field_name[$delta]) && $node->$field_name[$delta]->entity) {
        $paragraph = $node->$field_name[$delta]->entity;
        if ($row->getDestinationProperty('langcode') && $row->getDestinationProperty('langcode') != LanguageInterface::LANGCODE_NOT_SPECIFIED) {
          if (!$paragraph->hasTranslation($row->getDestinationProperty('langcode'))) {
            $paragraph->addTranslation($row->getDestinationProperty('langcode'));
          }
          $paragraph = $paragraph->getTranslation($row->getDestinationProperty('langcode'));
        }
        return $paragraph;
      }
    }
    // Fallback, create a new paragraph.
    $paragraph = Paragraph::create(['type' => $this->configuration['type']]);
    if ($row->getDestinationProperty('langcode')) {
      $paragraph->langcode = $row->getDestinationProperty('langcode');
    }
    return $paragraph;
  }
}