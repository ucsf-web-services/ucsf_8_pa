<?php

namespace Drupal\acquia_contenthub;

use Drupal\Component\Utility\Html;

/**
 * Content Hub Entity Embed Handler Class.
 */
class ContentHubEntityEmbedHandler {

  /**
   * Drupal field object.
   *
   * @var \Drupal\Core\Field\FieldItemListInterface
   *   Drupal field object.
   */
  protected $field;

  /**
   * ContentHubEntityEmbedHandler constructor.
   *
   * @param object $field
   *   Drupal field object.
   */
  public function __construct($field = NULL) {
    $this->field = $field;
  }

  /**
   * Method to parse UUID from text.
   *
   * @param string $text
   *   Any html text.
   *
   * @return array
   *   Array with parsed Uuids.
   */
  public function getReferencedUuids($text) {
    $result = [];
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    if (strpos($text, 'data-entity-uuid') !== FALSE) {
      foreach ($xpath->query('//drupal-entity[@data-entity-type and @data-entity-uuid]') as $node) {
        /** @var \DOMElement $node */
        $uuid = NULL;
        if ($uuid = $node->getAttribute('data-entity-uuid')) {
          $result[] = $uuid;
        }
      }
    }

    return $result;
  }

  /**
   * Parse entities from html text.
   *
   * @return array
   *   Array with entities.
   */
  public function getReferencedEntities() {

    // Check if field not set properly.
    if ($this->field == NULL) {
      return [];
    }

    $text = $this->field->getString();
    $result = [];

    if (strpos($text, 'data-entity-type') !== FALSE && (strpos($text, 'data-entity-uuid') !== FALSE || strpos($text, 'data-entity-id') !== FALSE)) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//drupal-entity[@data-entity-type and (@data-entity-uuid or @data-entity-id)]') as $node) {
        /** @var \DOMElement $node */
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;

        try {
          // Load the entity either by UUID (preferred) or ID.
          $id = NULL;
          $entity = NULL;
          if ($id = $node->getAttribute('data-entity-uuid')) {
            $entity = \Drupal::entityTypeManager()->getStorage($entity_type)
              ->loadByProperties(['uuid' => $id]);
            $entity = current($entity);
          }
          else {
            $id = $node->getAttribute('data-entity-id');
            $entity = \Drupal::entityTypeManager()->getStorage($entity_type)
              ->load($id);
          }

          if ($entity) {
            $result[$entity->uuid()] = $entity;
          }
        }
        catch (\Exception $e) {
          watchdog_exception('content_hub', $e);
        }
      }
    }

    return $result;
  }

  /**
   * Can given field contain entity_embed markup.
   *
   * @return bool
   *   Will return TRUE if we can process this field.
   */
  public function isProcessable() {
    // Check if field not set properly.
    if ($this->field == NULL) {
      return FALSE;
    }

    $field_type = $this->field->getFieldDefinition()->getType();
    // Field types to process.
    $types = ['text_with_summary', 'string_long', 'text_long'];
    return in_array($field_type, $types);
  }

}
