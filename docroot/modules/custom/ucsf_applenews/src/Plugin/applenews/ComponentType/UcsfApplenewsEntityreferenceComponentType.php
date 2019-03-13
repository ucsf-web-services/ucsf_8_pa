<?php

namespace Drupal\ucsf_applenews\Plugin\applenews\ComponentType;

use Drupal\applenews\Plugin\applenews\ComponentType\ApplenewsDefaultTextComponentType;

/**
 * Component types suitable for entity references.
 *
 * @ApplenewsComponentType(
 *  id = "ucsf_entityref",
 *  label = @Translation("Entity Reference Component Type"),
 *  description = @Translation("Component types suitable for entity references."),
 *  component_type = "text",
 *  deriver = "Drupal\ucsf_applenews\Derivative\UcsfApplenewsEntityreferenceComponentTypeDeriver"
 * )
 */
class UcsfApplenewsEntityreferenceComponentType extends ApplenewsDefaultTextComponentType {

  /**
   * {@inheritdoc}
   */
  protected function getFieldOptions($node_type) {
    $fields = $this->fieldManager->getFieldDefinitions('node', $node_type);
    $field_options = [];
    foreach ($fields as $field_name => $field) {
      $field_options[$field_name] = $field->getLabel();
    }

    return $field_options;
  }

}
