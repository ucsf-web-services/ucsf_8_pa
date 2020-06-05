<?php

namespace Drupal\menu_reference_render\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Plugin implementation of the 'menu_reference_render' formatter.
 *
 * @FieldFormatter(
 *   id = "menu_reference_render",
 *   label = @Translation("Rendered menu"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MenuReferenceFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $entity) {
      $menu_name = $entity->get('id');
      $menu_tree = \Drupal::menuTree();
      $menu_active_trail = \Drupal::service('menu.active_trail');

      // Build the typical default set of menu tree parameters.
      if ($this->getSetting('expand_all_items')) {
        $parameters = new MenuTreeParameters();
        $active_trail = $menu_active_trail->getActiveTrailIds($menu_name);
        $parameters->setActiveTrail($active_trail);
      }
      else {
        $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
      }

      // Load the tree based on this set of parameters.
      $tree = $menu_tree->load($menu_name, $parameters);

      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $menu_tree->transform($tree, $manipulators);

      $elements[] = $menu_tree->build($tree);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['expand_all_items'] = FALSE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['expand_all_items'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expand all menu items'),
      '#default_value' => $this->getSetting('expand_all_items'),
      '#description' => $this->t('Override the option found on each menu link used for expanding children and instead display the whole menu tree as expanded.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($this->getSetting('expand_all_items')) {
      $summary[] = $this->t('All menu items expanded');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Limit formatter to only menu entity types.
    return ($field_definition->getFieldStorageDefinition()->getSetting('target_type') == 'menu');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    // Set 'view label' operation for menu entity.
    // @see \Drupal\system\MenuAccessControlHandler::checkAccess().
    return $entity->access('view label', NULL, TRUE);
  }

}
