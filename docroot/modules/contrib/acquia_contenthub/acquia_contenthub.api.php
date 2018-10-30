<?php

/**
 * @file
 * Hooks provided by the Acquia Content Hub module.
 */

use Drupal\Core\Entity\ContentEntityInterface;
use Acquia\ContentHubClient\Entity as ContentHubEntity;
use Drupal\Core\Entity\EntityInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allows modules to modify the drupal entity before its normalization to CDF.
 *
 * Common Data Format (CDF): https://docs.acquia.com/content-hub/cdf.
 *
 * This is very useful to add additional ad-hoc fields into the drupal entity
 * before it is converted to CDF during the export process.
 * Note that the changes will be reflected in the entity published in Content
 * Hub, but the local Drupal entity will not be affected.
 *
 * @param string $entity_type_id
 *   The Drupal Entity type ID.
 * @param object $entity
 *   The Drupal entity.
 */
function hook_acquia_contenthub_drupal_to_cdf_alter($entity_type_id, $entity) {

  // The following example modifies the title of the node for all nodes
  // exported to Content Hub and adds the string ' - By My Cool Site'.
  // It does it by changing the drupal entity title before it is converted
  // to Common Data Format (CDF).
  if ($entity_type_id === 'node') {
    // Site String.
    $site_string = ' - By My Cool Site';

    // Obtain the title from the node entity.
    $title = $entity->get('title')->getValue();

    // Always check that the changes have already been applied, because the
    // normalizer could be called more than once during the export process.
    if (strpos($title[0]['value'], $site_string) === FALSE) {

      // Add the site string to the title.
      $title[0]['value'] .= $site_string;
      $entity->get('title')->setValue($title);
    }
  }
}

/**
 * Allows modules to modify the CDF before it is sent to the Content Hub.
 *
 * Common Data Format (CDF): https://docs.acquia.com/content-hub/cdf.
 *
 * This is very useful to modify the CDF (usually its attributes) before
 * it is sent to the Content Hub during the normalization process.
 * Note that the changes will be reflected in the entity published in Content
 * Hub, but the local Drupal entity will not be affected.
 *
 * @param \Acquia\ContentHubClient\Entity $contenthub_entity
 *   The Content Hub CDF.
 */
function hook_acquia_contenthub_cdf_from_drupal_alter(ContentHubEntity $contenthub_entity) {

  // The following example modifies the title of the node for all nodes
  // exported to Content Hub and adds the string ' - By My Cool Site'.
  // It does it by modifying the title after producing the CDF that is fetched
  // by Content Hub.
  if ($contenthub_entity->getType() == 'node') {
    // Site String.
    $site_string = ' - By My Cool Site';

    $title = $contenthub_entity->getAttribute('title')->getValues();
    $language = reset(array_keys($title));

    // Always check that the changes have already been applied, because the
    // normalizer could be called more than once during the export process.
    if (strpos($title[$language], $site_string) === FALSE) {
      $contenthub_entity->getAttribute('title')->setValue($title[$language] . $site_string, $language);

      // Remember, in the code above you are just adding text to the CDF that
      // comes from an existent drupal entity without saving changes to the
      // entity itself, then in order for these changes to be obtained from
      // Content Hub, you would need to invalidate the cache tag for this
      // particular node so the changes take effect.
    }
  }
}

/**
 * Allows modules to modify the CDF before converting to Drupal Entity.
 *
 * Common Data Format (CDF): https://docs.acquia.com/content-hub/cdf.
 *
 * This is useful to modify the CDF that has been fetched from the Content
 * Hub before it has been converted to Drupal Entity during the denormalization
 * process.
 * Note that we these changes affect the local entity imported from Content Hub
 * but do not affect the entity in Content Hub itself.
 *
 * @param \Acquia\ContentHubClient\Entity $contenthub_entity
 *   The Content Hub CDF.
 */
function hook_acquia_contenthub_cdf_from_hub_alter(ContentHubEntity $contenthub_entity) {
  // The following example modifies the title of the node for all nodes
  // imported from Content Hub and adds the string ' - From My Cool Site'.
  // It does it by changing the CDF before denormalizing to a drupal entity.
  if ($contenthub_entity->getType() == 'node') {
    // Site String.
    $site_string = ' - From My Cool Site';

    $title = $contenthub_entity->getAttribute('title');
    $language = array_keys($title['value']);
    $language = reset($language);

    // Always check that the changes have already been applied, because the
    // denormalizer could be called more than once during the import process.
    if (strpos($title['value'][$language], $site_string) === FALSE) {

      // Add site string to the title.
      $title['value'][$language] = $title['value'][$language] . $site_string;
      $contenthub_entity['attributes']['title'] = $title;
    }
  }
}

/**
 * Allow modules to modify the Drupal Entity after conversion from CDF.
 *
 * Common Data Format (CDF): https://docs.acquia.com/content-hub/cdf.
 *
 * This is useful to modify the Drupal Entity that came out as a result of its
 * conversion from CDF fetched from Content Hub during the denormalization
 * process.
 * Note that we these changes affect the local entity imported from Content Hub
 * but do not affect the entity in Content Hub itself.
 *
 * @param string $entity_type_id
 *   The Drupal Entity type ID.
 * @param object $entity
 *   The Drupal entity.
 */
function hook_acquia_contenthub_drupal_from_cdf_alter($entity_type_id, $entity) {
  // The following example modifies the title of the node for all nodes
  // imported from Content Hub and adds the string ' - From My Cool Site'.
  // It does it by changing the drupal entity after it has been denormalized
  // from the Content Hub CDF.
  if ($entity_type_id === 'node') {
    // Site String.
    $site_string = ' - From My Cool Site';

    // Obtain the title from the node entity.
    $title = $entity->get('title')->getValue();

    // Always check that the changes have already been applied, because the
    // denormalizer could be called more than once during the import process.
    if (strpos($title[0]['value'], $site_string) === FALSE) {

      // Add the site string to the title.
      $title[0]['value'] .= $site_string;
      $entity->get('title')->setValue($title);
    }
  }
}

/**
 * Alter the excluded field types and names that get converted into a CDF.
 *
 * Common Data Format (CDF): https://docs.acquia.com/content-hub/cdf.
 *
 * Modules may implement this hook to alter the fields that
 * get excluded from being converted into a Content Hub CDF object. For example
 * the status, sticky and promote flags are excluded because they define the
 * state of a piece of content and not the piece of content itself. Acquia
 * Content Hub's main responsibility is transferring content, not the state of
 * it.
 *
 * @param array $excluded_fields
 *   The Field types that are excluded from being normalized into a CDF
 *   document.
 *
 * @see \Drupal\acquia_contenthub\Normalizer\ContentEntityNormalizer
 */
function hook_acquia_contenthub_exclude_fields_alter(array &$excluded_fields, ContentEntityInterface $entity) {
  // Do not include the uuid field.
  $excluded_fields[] = 'uuid';
  // Gets a specific entity key and add it to the excluded fields array.
  $excluded_fields[] = $entity->getEntityType()->getKey('id');
}

/**
 * Alter the field type mapping that maps field types to Content Hub types.
 *
 * Modules may implement this hook to alter the field type mapping. This is used
 * to tell Acquia Content Hub which Drupal field type maps to which data type
 * in Acquia Content Hub so that it correctly stores it and it allows for better
 * filtering, searching and storage.
 *
 * Be careful with altering existing field types as it could severely damage the
 * content in your Acquia Content Hub account.
 *
 * @param array $mapping
 *   The mapping of field types to their Content Hub Types. Example:
 *   $mapping = [
 *     'entity_reference => 'array<reference>',
 *     'integer' => 'array<integer>',
 *     'timespan' => 'array<integer>',
 *     'timestamp' => 'array<integer>',
 *     ...
 *   ];
 *   Available Content Hub Types, all are also available as multiple.
 *   - integer
 *   - string
 *   - boolean
 *   - reference
 *   - number.
 *
 * @see \Drupal\acquia_contenthub\Normalizer\ContentEntityNormalizer
 */
function hook_acquia_contenthub_field_type_mapping_alter(array &$mapping) {
  $mapping['my_custom_field'] = 'array<string>';
}

/**
 * Alter the excluded field types and names that get converted into a CDF.
 *
 * Modules may implement this hook to alter...
 *
 * @param \Acquia\ContentHubClient\Entity $contenthub_entity
 *   The Acquia Content Hub entity.
 * @param array $context
 *   Array consists out of at least 3 keys:
 *
 *   array['account'] object
 *     Defines the account it is being requested as.
 *
 *   array['entity'] Drupal\Core\Entity\ContentEntityInterface
 *     The entity that is being normalized.
 *
 *   array['langcode'] string
 *    The language the object was requested to be normalized in. Usually the
 *    normalization process iterates over all languages. Careful when making
 *    a selection based on this parameter.
 *
 * @see \Drupal\acquia_contenthub\Normalizer\ContentEntityNormalizer
 */
function hook_acquia_contenthub_cdf_alter(ContentHubEntity &$contenthub_entity, array &$context) {
  $langcode = isset($context['langcode']) ? $context['langcode'] : \Drupal::languageManager()->getDefaultLanguage();
  $contenthub_entity->setAttributeValue('my_attribute', 'this_is_my_value', $langcode);
}

/**
 * Determines whether an entity is eligible to be published to Content Hub.
 *
 * Modules may implement this hook to determine whether a particular entity can
 * be exported to Content Hub on a custom logic.
 * Content Hub checks for entities that are published and selected in the entity
 * configuration page but additional logic can be added through this hook.
 *
 * For example only nodes promoted to the frontpage can be published to Content
 * Hub according to the following code.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity to check for eligibility.
 *
 * @return bool
 *   TRUE if it is eligible to be published, FALSE otherwise.
 */
function hook_acquia_contenthub_is_eligible_entity(EntityInterface $entity) {
  if ($entity->getEntityTypeId() === 'node') {
    return $entity->isPromoted();
  }
}
