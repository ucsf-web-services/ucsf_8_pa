<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a form that alters entity form to add a Content Hub form.
 */
class EntityForm {
  
  use StringTranslationTrait;
  
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   Acquia Content Hub Entity Form.
   */
  public function getForm(EntityInterface $entity) {
    // Don't display anything if the entity doesn't exist.
    $entity_id = $entity->id();
    if (empty($entity_id)) {
      return NULL;
    }

    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity->getEntityTypeId(), $entity_id);
    // If the entity is not imported, do not display form.
    if (!$imported_entity) {
      return NULL;
    }

    $form = [
      '#type' => 'details',
      '#title' => $this->t('Acquia Content Hub settings'),
      '#access' => $this->currentUser->hasPermission('administer acquia content hub'),
      '#group' => 'advanced',
      '#tree' => TRUE,
      '#weight' => 30,
    ];
    $has_local_change = $imported_entity->hasLocalChange();
    $form['auto_update_label'] = [
      '#type' => 'markup',
      '#markup' => $has_local_change ? $this->t('This syndicated content has been modified locally, therefore it is no longer automatically synchronized to its original content.') : $this->t('This is a syndicated content. What happens if its original content is updated?'),
    ];
    $form['auto_update'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable this syndicated content to receive the same updates of its original content'),
      '#default_value' => $imported_entity->isAutoUpdate(),
    ];
    if ($has_local_change) {
      $form['auto_update_local_changes_label'] = [
        '#type' => 'markup',
        '#markup' => '<div>' . $this->t('Check to enable synchronization with any future updates of content from Content Hub.') . '</div><div><strong>' . $this->t("Any edits that were made to your site's instance of this content will be overwritten by the Content Hub version.") . '</strong></div>',
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

    $entity = $form_state->getFormObject()->getEntity();
    $imported_entity = $this->contentHubEntitiesTracking->loadImportedByDrupalEntity($entity->getEntityTypeId(), $entity->id());
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
