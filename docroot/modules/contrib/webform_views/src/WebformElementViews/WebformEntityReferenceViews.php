<?php

namespace Drupal\webform_views\WebformElementViews;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform\Plugin\WebformElementEntityReferenceInterface;
use Drupal\webform\Plugin\WebformElementInterface;
use Drupal\webform\Plugin\WebformElementManagerInterface;

/**
 * Webform views handler for entity reference webform elements.
 */
class WebformEntityReferenceViews extends WebformDefaultViews {

  /**
   * Entity type manager service.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * WebformEntityReferenceViews constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service
   * @param \Drupal\webform\Plugin\WebformElementManagerInterface $webform_element_manager
   *   Webform element manager service
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, WebformElementManagerInterface $webform_element_manager) {
    parent::__construct($entity_type_manager, $webform_element_manager);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementViewsData(WebformElementInterface $element_plugin, array $element) {
    $views_data = parent::getElementViewsData($element_plugin, $element);

    $target_entity_type = $this->getTargetEntityType($element_plugin, $element);

    if ($target_entity_type instanceof ContentEntityTypeInterface) {
      $views_data['relationship'] = [
        'base' => $target_entity_type->getDataTable() ? $target_entity_type->getDataTable() : $target_entity_type->getBaseTable(),
        'field' => 'value',
        'base field' => $target_entity_type->getKey('id'),
        'label' => $target_entity_type->getLabel(),
        'title' => $this->t('Referenced @entity_label', [
          '@entity_label' => $target_entity_type->getLabel(),
        ]),
        'id' => 'standard',
      ];
    }

    return $views_data;
  }

  /**
   * Retrieve target entity type from provided element.
   *
   * @param \Drupal\webform\Plugin\WebformElementEntityReferenceInterface $element_plugin
   *   Element plugin that corresponds to $element
   * @param array $element
   *   Webform element whose target entity type is requested
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   Target entity type of the provided element
   */
  protected function getTargetEntityType(WebformElementEntityReferenceInterface $element_plugin, array $element) {
    return $this->entityTypeManager->getDefinition($element_plugin->getTargetType($element));
  }

}
