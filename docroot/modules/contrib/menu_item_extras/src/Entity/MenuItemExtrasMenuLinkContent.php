<?php

namespace Drupal\menu_item_extras\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * {@inheritdoc}
 */
class MenuItemExtrasMenuLinkContent extends MenuLinkContent implements MenuItemExtrasMenuLinkContentInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    if (isset($values['menu_name'])) {
      $values += ['bundle' => $values['menu_name']];
    }
    parent::preCreate($storage, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    $tags = parent::getCacheTagsToInvalidate();
    if ($this->isNew() && $this->getParentId()) {
      // Need to invalidate the parent to clear the render cache generated
      // in MenuLinkTreeHandler::getMenuLinkItemContent().
      list(, $parent_uuid) = explode(':', $this->getParentId());
      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
      $storage = $this->entityTypeManager()->getStorage($this->getEntityTypeId());
      $loaded_entities = $storage->loadByProperties(['uuid' => $parent_uuid]);
      $parent = reset($loaded_entities);
      if ($parent) {
        return Cache::mergeTags($tags, ['menu_link_content:' . $parent->id()]);
      }
    }
    return $tags;
  }

}
