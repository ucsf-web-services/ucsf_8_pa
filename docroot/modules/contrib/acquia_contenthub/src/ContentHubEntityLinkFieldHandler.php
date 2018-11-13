<?php

namespace Drupal\acquia_contenthub;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Content Hub Entity Link Field.
 */
class ContentHubEntityLinkFieldHandler {

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
   * @param \Drupal\Core\Field\FieldItemListInterface|null $field
   *   Drupal field object or NULL.
   */
  public function __construct($field = NULL) {
    if ($field instanceof FieldItemListInterface && $field->getFieldDefinition()->getType() === 'link') {
      $this->field = $field;
    }
  }

  /**
   * Static method.
   *
   * @param object|null $field
   *   A field object.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntityLinkFieldHandler
   *   The ContentHub
   */
  public static function load($field = NULL) {
    return new static($field);
  }

  /**
   * Validates whether the loaded field is an acceptable argument.
   *
   * @return bool|\Drupal\acquia_contenthub\ContentHubEntityLinkFieldHandler
   *   This object if validates, FALSE otherwise.
   */
  public function validate() {
    if (!empty($this->field)) {
      return $this;
    }
    return FALSE;
  }

  /**
   * Converts Entity IDs into UUIDs.
   *
   * @param array $items
   *   An array of field value items.
   *
   * @return array
   *   an array of field values that Replaces IDs with UUIDs.
   */
  public function normalizeItems(array $items) {
    $link_entities = $this->getReferencedEntities($items);
    foreach ($items as $key => $item) {
      if (isset($link_entities[$key])) {
        $uri = $item['uri'];
        $link_parts = pathinfo($uri);
        $items[$key]['uri'] = $link_parts['dirname'] . '/' . $link_entities[$key]->uuid();
      }
    }
    return $items;
  }

  /**
   * Converts Entity UUIDs into IDs in a field value item.
   *
   * @param array $item
   *   An array of a field value item with UUIDs.
   *
   * @return array|null
   *   An array of a value item with IDs if dependency exists, NULL otherwise.
   */
  public function denormalizeItem(array $item) {
    $entity_info = $this->getDependentEntityInfo($item);
    if (!empty($entity_info)) {
      list($entity_type_id, $uuid) = $entity_info;
      if (strpos($item['uri'], 'entity:') !== FALSE) {
        $entities = \Drupal::entityTypeManager()->getStorage($entity_type_id)
          ->loadByProperties(['uuid' => $uuid]);
        $entity = $entities ? reset($entities) : NULL;
      }
      elseif (strpos($item['uri'], 'internal:') !== FALSE) {
        $path = pathinfo($item['uri']);
        $entity_type_id = NULL;
        if (substr($path['dirname'], -4) == 'node') {
          $entity_type_id = 'node';
        }
        elseif (substr($path['dirname'], -13) == 'taxonomy/term') {
          $entity_type_id = 'taxonomy_term';
        }
        if ($entity_type_id) {
          $entities = \Drupal::entityTypeManager()->getStorage($entity_type_id)
            ->loadByProperties(['uuid' => $uuid]);
          $entity = $entities ? reset($entities) : NULL;
        }
      }
      if ($entity) {
        $item['uri'] = str_replace($entity->uuid(), $entity->id(), $item['uri']);
      }
    }
    return $item;
  }

  /**
   * Obtains a list of referenced entities a Link field points to.
   *
   * When links are content links, it loads those entities.
   *
   * @param array $items
   *   An array of field value items.
   *
   * @return array
   *   An array of linked dependent entities.
   */
  public function getReferencedEntities(array $items) {
    $referenced_entities = [];
    foreach ($items as $key => $value) {
      $uri = isset($value['uri']) ? $value['uri'] : NULL;
      if (strpos($uri, 'entity:') !== FALSE) {
        $link_entity = explode('/', str_replace('entity:', '', $uri));
        if ($ref_entity = \Drupal::entityTypeManager()->getStorage($link_entity[0])->load($link_entity[1])) {
          $referenced_entities[$key] = $ref_entity;
        }
      }
      elseif (strpos($uri, 'internal:') !== FALSE) {
        $link_path = str_replace('internal:', '', $uri);
        $path = pathinfo($link_path);
        switch ($path['dirname']) {
          case '/node':
            $nid = $path['filename'];
            if (is_numeric($nid)) {
              if ($ref_entity = \Drupal::entityTypeManager()->getStorage('node')->load($nid)) {
                $referenced_entities[$key] = $ref_entity;
              }
            }
            break;

          case '/taxonomy/term':
            $tid = $path['filename'];
            if (is_numeric($tid)) {
              if ($ref_entity = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)) {
                $referenced_entities[$key] = $ref_entity;
              }
            }
            break;
        }
      }
    }
    return $referenced_entities;
  }

  /**
   * Get Dependent entity type id and UUID from the field value item.
   *
   * @param string $item
   *   The field value item.
   *
   * @return string|null
   *   If it matches pattern, returns dependent entity type and UUID; otherwise NULL.
   */
  public function getDependentEntityInfo($item) {
    if (!isset($item['uri'])) {
      return NULL;
    }

    $uri_array = explode(':', $item['uri']);
    $allowed_types = [
      'entity',
      'internal',
    ];

    if (!in_array($uri_array[0], $allowed_types)) {
      return NULL;
    }

    $entity_array = explode('/', $uri_array[1]);
    $uuid = array_pop($entity_array);

    if (!Uuid::isValid($uuid)) {
      return NULL;
    }

    $entity_type = implode('_', $entity_array);
    return [$entity_type, $uuid];
  }

}
