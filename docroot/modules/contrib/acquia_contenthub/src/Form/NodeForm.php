<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;

/**
 * Defines a form that alters node form to add a Content Hub form.
 */
class NodeForm {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The Content Hub Entities Tracking Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   */
  private $contentHubEntitiesTracking;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entities_tracking
   *   The Content Hub Entities Tracking Service.
   */
  public function __construct(AccountInterface $current_user, ContentHubEntitiesTracking $entities_tracking) {
    $this->currentUser = $current_user;
    $this->contentHubEntitiesTracking = $entities_tracking;
  }

  /**
   * Get Form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node.
   *
   * @return array
   *   Acquia Content Hub Node Form.
   */
  public function getForm(EntityInterface $node) {
    // Don't display anything if the node doesn't exist.
    $node_id = $node->id();
    if (empty($node_id)) {
      return NULL;
    }

    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($node->getEntityTypeId(), $node_id);
    // If the entity is not imported, do not display form.
    if (!$imported_entity) {
      return NULL;
    }

    $form = [
      '#type' => 'details',
      '#title' => t('Acquia Content Hub settings'),
      '#access' => $this->currentUser->hasPermission('administer acquia content hub'),
      '#group' => 'advanced',
      '#tree' => TRUE,
      '#weight' => 30,
    ];
    $has_local_change = $imported_entity->hasLocalChange();
    $form['auto_update_label'] = [
      '#type' => 'markup',
      '#markup' => $has_local_change ? t('This content has been modified.') : t('What do you like to do if there are changes in the original article?'),
    ];
    $form['auto_update'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable automatic updates'),
      '#default_value' => $imported_entity->isAutoUpdate(),
    ];
    if ($has_local_change) {
      $form['auto_update_local_changes_label'] = [
        '#type' => 'markup',
        '#markup' => '<div>' . t('Check to enable syncing with any future updates of content from Content Hub.') . '</div><div><strong>' . t("Any edits that were made to your site's instance of this content will be overwritten by the Content Hub version.") . '</strong></div>',
      ];
    }

    return $form;
  }

  /**
   * Attach submit handler to form actions.
   *
   * @param array &$form_actions
   *   Form actions.
   * @param string $submit_handler_name
   *   Submit handler's name.
   */
  public function attachSubmitHandler(array &$form_actions, $submit_handler_name) {
    foreach (array_keys($form_actions) as $action) {
      if ($action === 'preview' || !isset($form_actions[$action]['#type']) || $form_actions[$action]['#type'] !== 'submit') {
        continue;
      }
      array_unshift($form_actions[$action]['#submit'], $submit_handler_name);
    }
  }

  /**
   * Save settings.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function saveSettings(FormStateInterface $form_state) {
    if ($form_state->isValueEmpty('acquia_contenthub')) {
      return;
    }

    $node = $form_state->getFormObject()->getEntity();
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($node->getEntityTypeId(), $node->id());
    if (!$imported_entity) {
      return;
    }
    $values = $form_state->getValue('acquia_contenthub');
    $new_auto_update_flag = $values['auto_update'];

    // If we are changing state to "sync enabled" (from anything else),
    // set state to resync entity. Otherwise, just set the new autoUpdate flag.
    $set_pending_sync = !$imported_entity->isAutoUpdate() && $new_auto_update_flag === 1;
    $set_pending_sync ? $imported_entity->setPendingSync() : $imported_entity->setAutoUpdate($new_auto_update_flag);
    $imported_entity->save();
  }

}
