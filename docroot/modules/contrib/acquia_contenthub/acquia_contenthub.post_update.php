<?php

/**
 * @file
 * Post update functions for Acquia Content Hub.
 */

use Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig;

/**
 * @addtogroup updates-8.2.x-config-entities
 * @{
 */

/**
 * Create Content Hub Entity configuration entities.
 *
 * @see acquia_contenthub_update_8201()
 * @see https://www.drupal.org/node/2822285
 */
function acquia_contenthub_post_update_create_acquia_contenthub_entity_config_entities() {
  /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $contenthub_entity_config_old */
  $contenthub_entity_config_old = \Drupal::state()->get('acquia_contenthub_update_8201_entity_config', []);
  $contenthub_preview_image_config_old = \Drupal::state()->get('acquia_contenthub_update_8201_preview_image_config', []);

  $entity_type_manager = \Drupal::entityTypeManager();
  /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig[] $contenthub_entity_config_ids */
  $contenthub_entity_config_ids = $entity_type_manager->getStorage('acquia_contenthub_entity_config')->loadMultiple();

  foreach ($contenthub_entity_config_old as $entity_type => $bundles) {
    // Convert integer value to boolean.
    foreach ($bundles as $type => $bundle) {
      $bundles[$type]['enable_index'] = boolval($bundle['enable_index']);
      $bundles[$type]['enable_viewmodes'] = boolval($bundle['enable_viewmodes']);

      // Preview image only works for node entities.
      if ($entity_type == 'node') {
        $image_field = isset($contenthub_preview_image_config_old[$entity_type][$bundle]['field']) ? $contenthub_preview_image_config_old[$entity_type][$bundle]['field'] : FALSE;
        if ($image_field) {
          $bundles[$type]['preview_image_field'] = $image_field;
        }
        $image_style = isset($contenthub_preview_image_config_old[$entity_type][$bundle]['style']) ? $contenthub_preview_image_config_old[$entity_type][$bundle]['style'] : FALSE;
        if ($image_style) {
          $bundles[$type]['preview_image_style'] = $image_style;
        }
      }
    }

    // Saving configuration entities, only if they don't exist yet.
    if (!isset($contenthub_entity_config_ids[$entity_type])) {
      $contenthub_entity_config = ContentHubEntityTypeConfig::create([
        'id' => $entity_type,
        'bundles' => $bundles,
      ]);
      $contenthub_entity_config->save();
    }
  }
}

/**
 * @} End of "addtogroup updates-8.2.x-config-entities".
 */
