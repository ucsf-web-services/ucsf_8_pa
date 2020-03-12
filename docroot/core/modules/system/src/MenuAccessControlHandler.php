<?php

namespace Drupal\system;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the menu entity type.
 *
 * @see \Drupal\system\Entity\Menu
 */
class MenuAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // There are no restrictions on viewing the label of a date format.
    if ($operation === 'view label') {
      return AccessResult::allowed();
    }
    elseif ($operation === 'view') {
      if ($account->hasPermission('administer menu')) {
        return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($entity);
      }
      // If menu link is internal, and user has access, grant view access to menu link.
      if (($url_object = $entity->getUrlObject()) && ($url_object->isRouted())) {
        $link_access = $this->accessManager->checkNamedRoute($url_object->getRouteName(), $url_object->getRouteParameters(), $account, TRUE);
        if ($link_access->isAllowed()) {
          return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($entity);
        }
      }
      // Grant view access to external links.
      elseif ($url_object->isExternal()) {
        return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($entity);
      }
    }
    // Locked menus could not be deleted.
    elseif ($operation === 'delete') {
      if ($entity->isLocked()) {
        return AccessResult::forbidden('The Menu config entity is locked.')->addCacheableDependency($entity);
      }
      else {
        return parent::checkAccess($entity, $operation, $account)->addCacheableDependency($entity);
      }
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
