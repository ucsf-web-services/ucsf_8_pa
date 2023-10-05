<?php


/**
 * @file
 * Drush command file.
 */

/**
 * Implements hook_drush_command().
 */
function ucsf_applenews_drush_command() {
  return [
    'ucsf-applenews-generate' => [
      'description' => 'Generate all Apple News exports for a site.',
      'arguments' => [],
      'drupal dependencies' => [],
      'aliases' => [],
      'options' => [
        'id-list' => 'Comma-separated list of entity ids.',
      ],
    ],
  ];
}

/**
 * Callback for drush ucsf-applenews-generate-all.
 */
function drush_ucsf_applenews_generate() {
  $entity_type = 'node';
  $entity_bundle = 'article';
  $template_id = 'article';

  if ($entity_ids = drush_get_option('id-list')) {
    $entity_ids = explode(',', $entity_ids);
  }
  else {
    $query = \Drupal::entityQuery($entity_type)
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('type', $entity_bundle)
      ->sort('created', 'DESC');
    $entity_ids = $query->execute();
  }

  /** @var \Drupal\applenews\ApplenewsManager $applenews_manager */
  $applenews_manager = \Drupal::service('applenews.manager');
  foreach ($entity_ids as $entity_id) {
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)
      ->load($entity_id);
    $json = $applenews_manager
      ->getDocumentDataFromEntity($entity, $template_id);
    if (FALSE) {
      drush_print($json);
    }
    else {
      $json = json_decode($json);
      drush_print("$entity_id: " . ($json ? $json->title : 'FAIL'));
    }
  }
}
