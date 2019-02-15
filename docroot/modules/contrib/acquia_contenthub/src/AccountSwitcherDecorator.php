<?php

namespace Drupal\acquia_contenthub;

use Drupal\Core\Session\AccountSwitcher;

/**
 * Extend current account switcher service functionality.
 *
 * @package Drupal\acquia_contenthub
 */
class AccountSwitcherDecorator extends AccountSwitcher {

  /**
   * Switch back only if account stack contains at least one account.
   */
  public function switchBack() {
    // If there are no more accounts in the account stack,
    // current user will be the original user.
    if (count($this->accountStack) !== 0) {
      parent::switchBack();
    }
  }

}
