<?php

/**
 * @file
 * ContentHub Drush Commands.
 */

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Url;
use Drush\Log\LogLevel;
use Drupal\Core\Form\FormState;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;

/**
 * Implements hook_drush_command().
 */
function acquia_contenthub_drush_command() {
  $items['acquia-contenthub-local'] = [
    'description' => 'Prints the CDF from a local source (drupal site)',
    'arguments' => [
      'entity-type' => 'Entity type',
      'entity-id' => 'Entity identifier or entity\'s UUID',
    ],
    'outputformat' => [
      'default' => 'json',
      'pipe-format' => 'config',
      'variable-name' => 'variables',
      'table-metadata' => [
        'format' => 'var_export',
      ],
      'require-engine-capability' => ['format-list'],
    ],
    'required-arguments' => 2,
    'aliases' => ['ach-lo'],
  ];

  $items['acquia-contenthub-remote'] = [
    'description' => 'Prints the CDF from a remote source (Content Hub)',
    'arguments' => [
      'uuid' => 'Entity\'s UUID',
    ],
    'required-arguments' => 1,
    'outputformat' => [
      'default' => 'json',
      'pipe-format' => 'config',
      'variable-name' => 'variables',
      'table-metadata' => [
        'format' => 'var_export',
      ],
      'require-engine-capability' => ['format-list'],
    ],
    'aliases' => ['ach-re'],
  ];

  $items['acquia-contenthub-compare'] = [
    'description' => 'Loads the CDF from a local and remote source, compares them and prints the differences.',
    'arguments' => [
      'entity-type' => 'Entity type',
      'uuid' => 'Entity\'s UUID',
    ],
    'required-arguments' => 2,
    'aliases' => ['ach-comp'],
  ];

  $items['acquia-contenthub-list'] = [
    'description' => 'List entities from the Content Hub using the listEntities() method.',
    'options' => [
      'limit' => [
        'description' => 'The number of entities to be listed',
        'example_value' => '5',
      ],
      'start' => [
        'description' => 'The offset to start listing the entities (Useful for pagination).',
        'example_value' => '5',
      ],
      'origin' => [
        'description' => 'The Client\'s Origin UUID.',
        'example_value' => '00000000-0000-0000-0000-000000000000',
      ],
      'language' => [
        'description' => 'The Language that will be used to filter field values.',
        'example_value' => 'en',
      ],
      'attributes' => [
        'description' => 'The attributes to display for all listed entities',
        'example_value' => 'status,title',
      ],
      'type' => [
        'description' => 'The entity type',
        'example-value' => 'node',
      ],
      'filters' => [
        'description' => 'Filters entities according to a set of of conditions as a key=value pair separated by commas. You could use regex too.',
        'example_value' => 'title=New*,status=1',
      ],
    ],
    'outputformat' => [
      'default' => 'json',
      'pipe-format' => 'config',
      'variable-name' => 'variables',
      'table-metadata' => [
        'format' => 'var_export',
      ],
      'require-engine-capability' => ['format-list'],
    ],
    'aliases' => ['ach-list'],
  ];

  $items['acquia-contenthub-delete'] = [
    'description' => 'Deletes a single entity from the Content Hub',
    'arguments' => [
      'uuid' => 'Entity\'s UUID',
    ],
    'required-arguments' => 1,
    'aliases' => ['ach-del'],
  ];

  $items['acquia-contenthub-purge'] = [
    'description' => 'Purges all entities from Acquia Content Hub. WARNING! Be VERY careful when using this command. This destructive command requires elevated keys. Every subsequent execution of this command will override the backup created by the previous call.',
    'aliases' => ['ach-purge'],
    'arguments' => [
      'api' => 'API Key',
      'secret' => 'Secret Key',
    ],
  ];

  $items['acquia-contenthub-restore'] = [
    'description' => 'Restores the backup taken by a previous execution of the "purge" command. WARNING! Be VERY careful when using this command. This destructive command requires elevated keys. By restoring a backup you will delete all the existing entities in your subscription.',
    'aliases' => ['ach-restore'],
    'arguments' => [
      'api' => 'API Key',
      'secret' => 'Secret Key',
    ],
  ];

  $items['acquia-contenthub-reindex'] = [
    'description' => 'Reindexes all entities in Content Hub',
    'aliases' => ['ach-reindex'],
    'arguments' => [
      'api' => 'API Key',
      'secret' => 'Secret Key',
    ],
  ];

  $items['acquia-contenthub-logs'] = [
    'description' => 'View Historic entity logs from Content Hub',
    'aliases' => ['ach-logs'],
    'outputformat' => [
      'default' => 'table',
      'pipe-format' => 'list',
      'field-default' => [
        'timestamp',
        'type',
        'client',
        'entity_uuid',
        'status',
        'request_id',
        'id',
        'message',
      ],
      'field-labels' => [
        'timestamp' => dt('Timestamp'),
        'type' => dt('Type'),
        'client' => dt('Client'),
        'entity_uuid' => dt('Entity UUID'),
        'status' => dt('Status'),
        'request_id' => dt('Request ID'),
        'id' => dt('ID'),
        'message' => dt('Message'),
      ],
      'output-data-type' => 'format-table',
    ],
    'arguments' => [
      'api' => 'API Key',
      'secret' => 'Secret Key',
    ],
    'options' => [
      'query' => [
        'description' => 'The Elastic Search Query to search for logs.',
        'example_value' => '{"query": {"match_all": {}}}',
      ],
      'size' => [
        'description' => 'The number of log entries to be listed.',
        'example_value' => '5',
      ],
      'from' => [
        'description' => 'The offset to start listing the log entries (Useful for pagination).',
        'example_value' => '5',
      ],
    ],
  ];

  $items['acquia-contenthub-mapping'] = [
    'description' => 'Shows Elastic Search field mappings from Content Hub',
    'aliases' => ['ach-mapping'],
    'outputformat' => [
      'default' => 'json',
      'pipe-format' => 'config',
      'variable-name' => 'variables',
      'table-metadata' => [
        'format' => 'var_export',
      ],
      'require-engine-capability' => ['format-list'],
    ],
  ];

  $items['acquia-contenthub-regenerate-secret'] = [
    'description' => 'Regenerates the Shared Secret used for Webhook Verification',
    'aliases' => ['ach-regsec'],
  ];

  $items['acquia-contenthub-update-secret'] = [
    'description' => dt('Updates the Shared Secret used for Webhook Verification'),
    'aliases' => ['ach-upsec'],
  ];

  $items['acquia-contenthub-connect-site'] = [
    'description' => "Connects a site with contenthub.",
    'aliases' => ['ach-connect'],
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
  ];
  $items['acquia-contenthub-disconnect-site'] = [
    'description' => 'Disconnects a site with contenthub.',
    'aliases' => ['ach-disconnect'],
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
  ];
  $items['acquia-contenthub-webhooks'] = [
    'description' => dt("Perform a webhook management operation."),
    'aliases' => ['ach-wh'],
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
    'required-arguments' => 1,
    'arguments' => [
      'op' => dt('The operation to use. Options are: register, unregister, list.'),
    ],
    'options' => [
      'webhook_url' => dt('The webhook URL to register or unregister.'),
    ],
  ];
  $items['acquia-contenthub-reset-entities'] = [
    'description' => dt("Deletes all entities of a particular type from Content Hub, reindex subscription and exports all deleted entities again to allow for Elasticsearch to map correct field types."),
    'aliases' => ['ach-reset'],
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
    'required-arguments' => 1,
    'arguments' => [
      'entity_type' => dt('The entity type.'),
      'api' => 'API Key',
      'secret' => 'Secret Key',
    ],
    'options' => [
      'bundle' => dt('The entity bundle to reset (optional). All bundles of this entity type will be reset if not specified.'),
    ],
  ];

  $items['acquia-contenthub-audit-publisher'] = [
    'description' => dt('Checks published entities and compares them with what currently exist in Content Hub to assure consistency.'),
    'arguments' => [
      'entity-type' => dt('Entity type'),
    ],
    'options' => [
      'publish' => dt('Republish inconsistent entities to Content Hub.'),
      'status' => dt('The export status of the entities to audit, defaults to EXPORTED if not given. Possible values: EXPORTED, INITIATED, REINDEX, QUEUED.'),
    ],
    'required-arguments' => 0,
    'aliases' => ['ach-audit-publisher', 'ach-ap'],
  ];

  $items['acquia-contenthub-watchdog-queue-sizes'] = [
    'description' => dt("Log the size of the export and import queues to Drupal watchdog."),
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
  ];

  return $items;
}

/**
 * Loads and prints a local entity from Drupal in CDF Format.
 *
 * @param string $entity_type
 *   Entity type.
 * @param string $entity_id
 *   Entity's Uuid of entity's Identifier.
 *
 * @return mixed|false
 *   Returns and array containing the CDF of a local entity, FALSE otherwise.
 */
function drush_acquia_contenthub_local($entity_type, $entity_id) {
  $entity_type_manager = \Drupal::entityTypeManager();

  /** @var \Symfony\Component\Serializer\Serializer $serializer */
  $serializer = \Drupal::service('serializer');

  /** @var \Drupal\Core\Entity\EntityRepository $entity_repository */
  $entity_repository = \Drupal::service('entity.repository');

  if (empty($entity_type) || empty($entity_id)) {
    return drush_set_error(dt("Missing required parameters: entity_type and entity_id (or entity's uuid)"));
  }
  elseif (!$entity_type_manager->getDefinition($entity_type)) {
    return drush_set_error(dt("Entity type @entity_type does not exist", [
      '@entity_type' => $entity_type,
    ]));
  }
  else {
    if (Uuid::isValid($entity_id)) {
      $entity = $entity_repository->loadEntityByUuid($entity_type, $entity_id);
    }
    else {
      $entity = $entity_type_manager->getStorage($entity_type)->load($entity_id);
    }
  }

  if (!$entity) {
    drush_print(dt("Entity having entity_type = @entity_type and entity_id = @entity_id does not exist.", [
      '@entity_type' => $entity_type,
      '@entity_id' => $entity_id,
    ]));
  }
  // If nothing else, return our object structure.
  $output = $serializer->normalize($entity, 'acquia_contenthub_cdf');
  return $output;
}

/**
 * Loads and prints a remote entity in CDF Format.
 *
 * @param string $uuid
 *   Entity's UUID.
 *
 * @return mixed|false
 *   Returns an array containing the CDF of a remote entity
 */
function drush_acquia_contenthub_remote($uuid) {
  if (Uuid::isValid($uuid)) {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');
    if ($entity = $client_manager->createRequest('readEntity', [$uuid])) {
      return (array) $entity;
    }
    else {
      drush_print(dt("The Content Hub does not have an entity with UUID = @uuid.", [
        '@uuid' => $uuid,
      ]));
    }
  }
  else {
    return drush_set_error(dt("Argument provided is not a UUID."));
  }
  return FALSE;
}

/**
 * Compares a CDF entity from local and remote source.
 *
 * @param string $entity_type
 *   The Entity type.
 * @param string $uuid
 *   The Entity's UUID.
 *
 * @return mixed|false
 *   Returns an array containing the differences between local and
 *   remote entities.
 */
function drush_acquia_contenthub_compare($entity_type, $uuid) {
  $entity_type_manager = \Drupal::entityTypeManager();

  if (!$entity_type_manager->getDefinition($entity_type)) {
    return drush_set_error(dt("The entity type provided does not exist."));
  }

  if (!Uuid::isValid($uuid)) {
    return drush_set_error(dt("Argument provided is not a UUID."));
  }

  /** @var \Symfony\Component\Serializer\Serializer $serializer */
  $serializer = \Drupal::service('serializer');

  /** @var \Drupal\Core\Entity\EntityRepository $entity_repository */
  $entity_repository = \Drupal::service('entity.repository');

  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');
  $client = $client_manager->getConnection();

  // Get our local CDF version.
  $local_entity = $entity_repository->loadEntityByUuid($entity_type, $uuid);
  $local_cdf = $serializer->normalize($local_entity, 'acquia_contenthub_cdf');
  if (!$local_cdf) {
    $local_cdf = [];
  }

  // Get the Remote CDF version.
  $remote_cdf = $client->readEntity($uuid);
  if (!$remote_cdf) {
    $remote_cdf = [];
  }

  $local_compare = array_diff($local_cdf, (array) $remote_cdf);
  drush_print("Data from the local entity that doesn't appear in the remote entity, retrieved from Content Hub Backend:");
  drush_print_r(json_encode($local_compare, JSON_PRETTY_PRINT));
  drush_print("Data from the remote entity that doesn't appear in the local entity:");
  $remote_compare = array_diff((array) $remote_cdf, $local_cdf);
  drush_print_r(json_encode($remote_compare, JSON_PRETTY_PRINT));
}

/**
 * Lists entities from the Content Hub.
 *
 * @return mixed|false
 *   Returns a list of Content Hub Entities
 */
function drush_acquia_contenthub_list() {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');
  $client = $client_manager->getConnection();

  $options = [];

  // Obtaining the limit.
  $limit = drush_get_option("limit");
  if (isset($limit)) {
    $limit = (int) $limit;
    if ($limit < 1 || $limit > 1000) {
      return drush_set_error(dt("The limit has to be an integer from 1 to 1000."));
    }
    else {
      $options['limit'] = $limit;
    }
  }

  // Obtaining the offset.
  $start = drush_get_option("start");
  if (isset($start)) {
    if (!is_numeric($start)) {
      return drush_set_error(dt("The start offset has to be numeric starting from 0."));
    }
    else {
      $options['start'] = $start;
    }
  }

  // Filtering by origin.
  $origin = drush_get_option("origin");
  if (isset($origin)) {
    if (Uuid::isValid($origin)) {
      $options['origin'] = $origin;
    }
    else {
      return drush_set_error(dt("The origin has to be a valid UUID."));
    }
  }

  // Filtering by language.
  $language = drush_get_option("language");
  if (isset($language)) {
    if (strlen($language) == 2) {
      $options['language'] = $language;
    }
    else {
      return drush_set_error(dt("The language has to be provided as a 2-letter language code."));
    }
  }

  // Filtering by fields.
  $fields = drush_get_option("attributes");
  if (isset($fields)) {
    $options['fields'] = $fields;
  }

  // Filtering by type.
  $type = drush_get_option("type");
  if (isset($type)) {
    $options['type'] = $type;
  }

  // Building the filters.
  $filters = drush_get_option("filters");
  if (isset($filters)) {
    $filters = isset($filters) ? explode(",", $filters) : FALSE;
    foreach ($filters as $key => $filter) {
      list($name, $value) = explode("=", $filter);
      $filters[$name] = $value;
      unset($filters[$key]);
    }
    $options['filters'] = $filters;
  }
  if ($client_manager->isConnected()) {
    $list = $client_manager->createRequest('listEntities', [$options]);
    return $list;
  }
  else {
    return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
  }

}

/**
 * Deletes single entities from the Content Hub.
 *
 * @param string $uuid
 *   Uuid of entity to delete.
 *
 * @return mixed|false
 *   TRUE if entity is deleted, FALSE otherwise.
 */
function drush_acquia_contenthub_delete($uuid = NULL) {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  if (!Uuid::isValid($uuid)) {
    return drush_set_error(dt("Argument provided is not a UUID."));
  }
  else {
    if (drush_confirm(dt('Are you sure you want to delete the entity with uuid = @uuid from the Content Hub? There is no way back from this action!', [
      '@uuid' => $uuid,
    ]))) {
      $client = $client_manager->getConnection();
      if ($client->deleteEntity($uuid)) {
        drush_print(dt("Entity with UUID = @uuid has been successfully deleted from the Content Hub.", [
          '@uuid' => $uuid,
        ]));
      }
      else {
        return drush_set_error(dt("Entity with UUID = @uuid cannot be deleted.", [
          '@uuid' => $uuid,
        ]));
      }
    }
    else {
      return drush_user_abort();
    }
  }
}

/**
 * Purges entities from the Content Hub.
 *
 * WARNING! This requires elevated keys.
 * This also creates a backup that can be restored later. Be careful when using
 * this command because any subsequent purges overwrite the existing backup.
 * Backups can be restored by using the "restore" command.
 *
 * @param string|null $api
 *   API Key.
 * @param string|null $secret
 *   Secret Key.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_purge($api = NULL, $secret = NULL) {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  $warning_message = "Are you sure you want to PURGE your Content Hub Subscription?\n" .
    "*************************************************************************************\n" .
    "PROCEED WITH CAUTION. THIS ACTION WILL PURGE ALL EXISTING ENTITIES IN YOUR CONTENT HUB SUBSCRIPTION.\n" .
    "While a backup is created for use by the restore command, restoration may not be timely and is not guaranteed. Concurrent or frequent\n" .
    "use of this command may result in an inability to restore. You can always republish your content as a means of 'recovery'.
    For more information, check https://docs.acquia.com/content-hub.\n" .
    "*************************************************************************************\n" .
    "Are you sure you want to proceed?\n";
  if (drush_confirm($warning_message)) {
    // If API/Secret Keys have been given, reset the connection to use those
    // keys instead of the ones set in the configuration.
    if (!empty($api) && !empty($secret)) {
      $client_manager->resetConnection([
        'api' => $api,
        'secret' => $secret,
      ]);
    }

    // Execute the 'purge' command.
    if ($client_manager->isConnected()) {
      $response = $client_manager->createRequest('purge');
    }
    else {
      return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }
    if (isset($response['success']) && $response['success'] === TRUE) {
      // Deleting exported entities from the Tracking Table.
      /** @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entities_tracking */
      $entities_tracking = \Drupal::getContainer()->get('acquia_contenthub.acquia_contenthub_entities_tracking');
      $entities_tracking->deleteExportedEntities();
      drush_print("Your Subscription is being purged. All clients who have registered to received webhooks will be notified with a reindex webhook when the purge process has been completed.\n");
    }
    else {
      return drush_set_error(dt("Error trying to purge your subscription. You might require elevated keys to perform this operation."));
    }
  }
  else {
    return drush_user_abort();
  }
}

/**
 * Restores the backup created by a previous execution of the "purge" command.
 *
 * WARNING! This command requires elevated keys.
 * It also deletes all existing entities in your susbscription because they are
 * replaced by a backup copy.
 *
 * @param string|null $api
 *   API Key.
 * @param string|null $secret
 *   Secret Key.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_restore($api = NULL, $secret = NULL) {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  $warning_message = "Are you sure you want to RESTORE the latest backup taken after purging your Content Hub Subscription?\n" .
    "*************************************************************************************\n" .
    "PROCEED WITH CAUTION. THIS ACTION WILL ELIMINATE ALL EXISTING ENTITIES IN YOUR CONTENT HUB SUBSCRIPTION.\n" .
    "This restore command should only be used after an accidental purge event has taken place *and* completed. This will attempt to restore\n" .
    "from the last purge-generated backup. In the event this fails, you will need to republish your content to Content Hub.
    For more information, check https://docs.acquia.com/content-hub.\n" .
    "*************************************************************************************\n" .
    "Are you sure you want to proceed?\n";
  if (drush_confirm($warning_message)) {
    // If API/Secret Keys have been given, reset the connection to use those
    // keys instead of the ones set in the configuration.
    if (!empty($api) && !empty($secret)) {
      $client_manager->resetConnection([
        'api' => $api,
        'secret' => $secret,
      ]);
    }

    // Execute the 'restore' command.
    if ($client_manager->isConnected()) {
      $response = $client_manager->createRequest('restore');
    }
    else {
      return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }
    if (isset($response['success']) && $response['success'] === TRUE) {
      drush_print("Your Subscription is being restored. All clients who have registered to received webhooks will be notified with a reindex webhook when the restore process has been completed.\n");
    }
    else {
      return drush_set_error(dt("Error trying to restore your subscription from a backup copy. You might require elevated keys to perform this operation."));
    }
  }
  else {
    return drush_user_abort();
  }
}

/**
 * Rebuilds the Elastic Search Index in the Content Hub Subscription.
 *
 * WARNING! This requires elevated keys.
 * This command will rebuild the Elastic Search Index from the subscription.
 * It is important in cases where the field definition has changed, then all
 * existing entities with the previous definition would have to be deleted and
 * the index rebuilt. After that the new definition of the entities with that
 * field can be pushed to Content Hub.
 *
 * @param string|null $api
 *   API Key.
 * @param string|null $secret
 *   Secret Key.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_reindex($api = NULL, $secret = NULL) {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  $warning_message = "Are you sure you want to REINDEX your Content Hub Subscription?\n" .
    "*************************************************************************************\n" .
    "PROCEED WITH CAUTION. THIS ACTION WILL REBUILD THE ELASTIC SEARCH INDEX IN YOUR CONTENT HUB SUBSCRIPTION.\n" .
    "This command will rebuild your index from the data currently stored in Content Hub. Make sure to first 'unpublish' all the entities that contain undesired\n" .
    "field definitions, otherwise you will rebuild essentially the same index you have today. Entities containing fields with new definitions can be\n" .
    "published after the index has been rebuilt.
    For more information, check https://docs.acquia.com/content-hub.\n" .
    "*************************************************************************************\n" .
    "Are you sure you want to proceed?\n";
  if (drush_confirm($warning_message)) {
    // If API/Secret Keys have been given, reset the connection to use those
    // keys instead of the ones set in the configuration.
    if (!empty($api) && !empty($secret)) {
      $client_manager->resetConnection([
        'api' => $api,
        'secret' => $secret,
      ]);
    }

    // Execute the 'reindex' command.
    if ($client_manager->isConnected()) {
      /** @var \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex */
      $reindex = \Drupal::service('acquia_contenthub.acquia_contenthub_reindex');
      $response = $client_manager->createRequest('reindex');
      $reindex->setReindexStateSent();
    }
    else {
      return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }
    if (isset($response['success']) && $response['success'] === TRUE) {
      drush_print("Your Subscription is being re-indexed. All clients who have registered to received webhooks will be notified with a reindex webhook when the process has been completed.\n");
    }
    else {
      return drush_set_error(dt("Error trying to re-index your subscription. You might require elevated keys to perform this operation."));
    }
  }
  else {
    return drush_user_abort();
  }
}

/**
 * Obtains the Entity history logs for the Content Hub Subscription.
 *
 * These are log entries for entity-related requests executed by clients to
 * the content hub subscription and their status.
 *
 * @param string|null $api
 *   API Key.
 * @param string|null $secret
 *   Secret Key.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_logs($api = NULL, $secret = NULL) {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  // If API/Secret Keys have been given, reset the connection to use those
  // keys instead of the ones set in the configuration.
  if (!empty($api) && !empty($secret)) {
    $client_manager->resetConnection([
      'api' => $api,
      'secret' => $secret,
    ]);
  }

  $options = [];
  // Obtaining the limit.
  $size = drush_get_option("size");
  if (isset($size)) {
    $size = (int) $size;
    if ($size < 1 || $size > 1000) {
      return drush_set_error(dt("The size has to be an integer from 1 to 1000."));
    }
    else {
      $options['size'] = $size;
    }
  }

  // Obtaining the offset.
  $from = drush_get_option("from");
  if (isset($from)) {
    if (!is_numeric($from)) {
      return drush_set_error(dt("The start offset has to be numeric starting from 0."));
    }
    else {
      $options['from'] = $from;
    }
  }

  // Obtaining the query.
  $query = drush_get_option("query");
  $query = !empty($query) ? $query : '';

  // Execute the 'history' command.
  if ($client_manager->isConnected()) {
    $logs = $client_manager->createRequest('logs', [$query, $options]);
  }
  else {
    return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
  }
  if ($logs) {
    $rows = [];
    if (isset($logs['hits']['hits'])) {
      foreach ($logs['hits']['hits'] as $log) {
        $rows[] = [
          'timestamp' => $log['_source']['timestamp'],
          'type' => strtoupper($log['_source']['type']),
          'client' => $log['_source']['client'],
          'entity_uuid' => $log['_source']['entity'],
          'status' => strtoupper($log['_source']['status']),
          'request_id' => $log['_source']['request_id'],
          'id' => $log['_source']['id'],
          'message' => $log['_source']['message'],
        ];
      }
    }

    // Sort results DESC by 'timestamp' before presenting.
    usort($rows, function ($a, $b) {
      return strcmp($b["timestamp"], $a["timestamp"]);
    });
    return $rows;
  }
  else {
    return drush_set_error(dt("Error trying to print the entity logs."));
  }
}

/**
 * Shows the field mappings in Elastic search for the current subscription.
 *
 * These field mappings shows the data types used in Elastic Search to index
 * content related to entities submitted to Content Hub.
 */
function drush_acquia_contenthub_mapping() {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  if ($client_manager->isConnected()) {
    $output = $client_manager->createRequest('mapping');
  }
  else {
    return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
  }

  if ($output) {
    return $output;
  }
  else {
    return drush_set_error(dt("Error trying to print the elastic search field mappings."));
  }
}

/**
 * Regenerates a Shared Secret from the Content Hub.
 */
function drush_acquia_contenthub_regenerate_secret() {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');
  $warning_message = "Are you sure you want to REGENERATE your shared-secret in the Content Hub?\n" .
    "*************************************************************************************\n" .
    "PROCEED WITH CAUTION. THIS COULD POTENTIALLY LEAD TO HAVING SOME SITES OUT OF SYNC.\n" .
    "Make sure you have ALL your sites correctly configured to receive webhooks before attempting to do this.\n" .
    "For more information, check https://docs.acquia.com/content-hub/known-issues.\n" .
    "*************************************************************************************\n";
  if (drush_confirm($warning_message)) {
    if ($client_manager->isConnected()) {
      $output = $client_manager->createRequest('regenerateSharedSecret');
    }
    else {
      return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }

    if ($output) {
      drush_print("Your Shared Secret has been regenerated. All clients who have registered to received webhooks are being notified of this change.\n");
    }
    else {
      return drush_set_error(dt("Error trying to regenerate the shared-secret in your subscription. Try again later."));
    }
  }
  else {
    return drush_user_abort();
  }

}

/**
 * Updates the Shared Secret from the Content Hub.
 */
function drush_acquia_contenthub_update_secret() {
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');
  /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
  $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');

  if ($client_manager->isConnected()) {
    $subscription->getSettings();
    drush_print(dt('The shared secret has been updated.'));
  }
  else {
    drush_set_error(dt('The Content Hub client is not connected so the shared secret can not be updated.'));
  }
}

/**
 * Connects a site with Content Hub.
 */
function drush_acquia_contenthub_connect_site($api_key = '', $secret = '', $hostname = '', $client_name = '') {
  $config_factory = \Drupal::configFactory();
  $uuid_service = \Drupal::service('uuid');

  $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
  $config_api_key = $config->get('api_key');

  if (!empty($config_api_key)) {
    drush_log(dt('Site is already connected to Content Hub. Skipping.'), LogLevel::CANCEL);
    return;
  }

  $hostname = !empty($hostname) ? $hostname : drush_prompt(dt('What is the Content Hub API URL?'), 'https://us-east-1.content-hub.acquia.com');
  $api_key = !empty($api_key) ? $api_key : drush_prompt(dt('What is your Content Hub API Key?'));
  $secret = !empty($secret) ? $secret : drush_prompt(dt('What is your Content Hub API Secret?'));
  $client_uuid = $uuid_service->generate();
  $client_name = !empty($client_name) ? $client_name : drush_prompt(dt('What is the client name for this site?'), $client_uuid);

  $form_state = new FormState();
  $values['api_key'] = $api_key;
  $values['secret_key'] = $secret;
  $values['hostname'] = $hostname;
  $values['client_name'] = $client_name . '_' . $client_uuid;
  $values['op'] = t('Save configuration');
  $form_state->setValues($values);
  \Drupal::formBuilder()->submitForm('Drupal\acquia_contenthub\Form\ContentHubSettingsForm', $form_state);
}

/**
 * Disconnects a site from Content Hub.
 */
function drush_acquia_contenthub_disconnect_site() {
  /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
  $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');
  $subscription->disconnectClient();
  // disconnectClient always returns false. Check config to be sure of success.
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
  $webhook_uuid = $config->get('webhook_uuid');
  if (!$webhook_uuid) {
    drush_log(dt('Site has been disconnected from Content Hub.'), LogLevel::SUCCESS);
  }
}

/**
 * Content Hub webhook operations.
 */
function drush_acquia_contenthub_webhooks($op, $webhook_url = NULL) {
  $config_factory = \Drupal::configFactory();
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');
  /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
  $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');

  if (empty($webhook_url)) {
    $webhook_url = Url::fromUri('internal:/acquia-contenthub/webhook', ['absolute' => TRUE])->toString();
  }
  if ($client_manager->isConnected()) {
    $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
    switch ($op) {
      case 'register':
        $success = $subscription->registerWebhook($webhook_url);
        if (!$success) {
          drush_log(dt('Registering webhooks encountered an error.'), LogLevel::CANCEL);
        }
        else {
          $webhook_uuid = $config->get('webhook_uuid');
          drush_log(dt('Registered Content Hub Webhook: @uuid', ['@uuid' => $webhook_uuid]), LogLevel::SUCCESS);
        }
        break;

      case 'unregister':
        $webhook_url = $config->get('webhook_url');
        $success = $subscription->unregisterWebhook($webhook_url);
        if (!$success) {
          drush_log(dt('There was an error unregistering the URL: @url', ['@url' => $webhook_url]), LogLevel::CANCEL);
        }
        break;

      case 'list':
        $settings = $subscription->getSettings();
        if (!$settings) {
          break;
        }
        $webhooks = $settings->getWebhooks();
        foreach ($webhooks as $index => $webhook) {
          drush_print($index + 1 . '. ' . $webhook['url'] . ' - ' . $webhook['uuid']);
        }
        break;

      default:
        // Invalid operation.
        drush_set_error(dt('The op "@op" is invalid', ['@op' => $op]));
    }
  }
  else {
    drush_set_error(dt('The Content Hub client is not connected so the webhook operations could not be performed.'));
  }
}

/**
 * Rebuilds the Elastic Search Index in the Content Hub Subscription.
 *
 * WARNING! This requires elevated keys.
 * This command will rebuild the Elastic Search Index from the subscription.
 * It is important in cases where the field definition has changed, then all
 * existing entities with the previous definition would have to be deleted and
 * the index rebuilt. After that the new definition of the entities with that
 * field can be pushed to Content Hub.
 *
 * @param string|null $entity_type
 *   The entity type.
 * @param string|null $api
 *   API Key.
 * @param string|null $secret
 *   Secret Key.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_reset_entities($entity_type = NULL, $api = NULL, $secret = NULL) {
  if (empty($entity_type)) {
    return drush_set_error(dt('You need to provide at least the entity type of the entities you want to reset.'), LogLevel::CANCEL);
  }
  /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
  $client_manager = \Drupal::service('acquia_contenthub.client_manager');

  // Defining the bundle.
  $bundle = drush_get_option("bundle");

  $warning_message = "Are you sure you want to remotely DELETE entities of type = %s %s, REINDEX subscription and RE-EXPORT deleted entities in this Content Hub Subscription?\n" .
    "*************************************************************************************\n" .
    "PROCEED WITH CAUTION. THIS ACTION WILL REBUILD THE ELASTIC SEARCH INDEX IN YOUR CONTENT HUB SUBSCRIPTION.\n" .
    "This command will rebuild your index from the data currently stored in Content Hub. Make sure to first 'unpublish' all the entities that contain undesired\n" .
    "field definitions, otherwise you will rebuild essentially the same index you have today. Entities containing fields with new definitions can be\n" .
    "published after the index has been rebuilt.
    For more information, check https://docs.acquia.com/content-hub.\n" .
    "*************************************************************************************\n" .
    "Are you sure you want to proceed?\n";
  $warning_bundle = sprintf("and bundle = %s", $bundle);
  $warning_message = sprintf($warning_message, $entity_type, $warning_bundle);
  if (drush_confirm($warning_message)) {
    // If API/Secret Keys have been given, reset the connection to use those
    // keys instead of the ones set in the configuration.
    if (!empty($api) && !empty($secret)) {
      $client_manager->resetConnection([
        'api' => $api,
        'secret' => $secret,
      ]);
    }

    // Verify that this site has a registered webhook before proceeding.
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
    $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');
    if (!$subscription->isWebhookSet()) {
      return drush_set_error(dt("Error trying to re-set and reindex entities. You are required to have a registered webhook in this site before proceeding."));
    }

    // Execute the 'reindex' command.
    if ($client_manager->isConnected()) {
      /** @var \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex */
      $reindex = \Drupal::service('acquia_contenthub.acquia_contenthub_reindex');

      // Only continue with the command if we didn't send a reindex request yet.
      // If we already sent a reindex request, then we are just waiting for it
      // to be completed.
      if (!$reindex->isReindexSent()) {

        // If the reindex has been completed then execute the batch process to
        // re-export entities.
        if ($reindex->isReindexFinished()) {
          $reindex->reExportEntitiesAfterReindex();

          // Start the batch process.
          drush_backend_batch_process();
        }
        else {
          // Check whether there are entities in Content Hub that are not
          // owned by this site.
          $entities = $reindex->getExportedEntitiesNotOwnedByThisSite($entity_type);
          if (count($entities) > 0) {
            $header = [
              'UUID',
              'Origin',
              'Modified',
              'Entity Type',
            ];
            drush_print(dt("You cannot perform this operation because there are entities exported to Content Hub that do not belong to this site. Please go to the origins listed (sites) and delete those entities (by doing 'ach-del <UUID>') before proceeding to reindex your subscription. Below is a list of those entities that cannot be deleted from this site:\n"));
            array_unshift($entities, $header);
            drush_print_table($entities, TRUE);
            return drush_set_error(dt("Error trying to re-index your subscription. You have entities that do not belong to this site."));
          }

          // Defining the bundle.
          $bundle = drush_get_option("bundle");

          // Delete entities and reindex subscription.
          $success = $reindex->setExportedEntitiesToReindex($entity_type, $bundle);
          if ($success && $reindex->isReindexSent()) {
            drush_print("Your Subscription is being re-indexed. All clients who have registered to received webhooks will be notified with a reindex webhook when the process has been completed.\n");
          }
          elseif (!$success && $reindex->isReindexFailed()) {
            return drush_set_error(dt("Error trying to re-index your subscription. You might require elevated keys to perform this operation."));
          }
          elseif (!$success && $reindex->isReindexNone()) {
            $bundle_msg = empty($bundle) ? '' : dt("and bundle = @bundle", [
              '@bundle' => $bundle,
            ]);
            return drush_set_error(dt("You are trying to reset entities of type = '@entity_type' @bundle_msg but none of them have been exported.", [
              '@entity_type' => $entity_type,
              '@bundle_msg' => $bundle_msg,
            ]));
          }
        }
      }
      else {
        return drush_set_error(dt('You have already sent a Reindex Request to Content Hub. Please wait until the current reindex is processed and finalized before you can send another reindex request again.'));
      }
    }
    else {
      return drush_set_error(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }
  }
  else {
    return drush_user_abort();
  }
}

/**
 * Audits exported entities and republishes if inconsistencies are found.
 *
 * @param string|null $entity_type_id
 *   The entity type.
 *
 * @return mixed|false
 *   Drush Output.
 */
function drush_acquia_contenthub_audit_publisher($entity_type_id = NULL) {
  // Obtaining the query.
  $publish = drush_get_option("publish") ?: FALSE;
  $status = drush_get_option("status") ?: ContentHubEntitiesTracking::EXPORTED;

  if ($publish) {
    $warning_message = dt('Are you sure you want to republish entities to Content Hub?');
    if (drush_confirm($warning_message) == FALSE) {
      return drush_user_abort();
    }
  }

  /** @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entities_tracking */
  $entities_tracking = \Drupal::getContainer()->get('acquia_contenthub.acquia_contenthub_entities_tracking');
  switch ($status) {
    case ContentHubEntitiesTracking::EXPORTED:
      $entities = $entities_tracking->getPublishedEntities($entity_type_id);
      break;

    case ContentHubEntitiesTracking::INITIATED:
      $entities = $entities_tracking->getInitiatedEntities($entity_type_id);
      break;

    case ContentHubEntitiesTracking::REINDEX:
      $entities = $entities_tracking->getEntitiesToReindex($entity_type_id);
      break;

    case ContentHubEntitiesTracking::QUEUED:
      // If we want to queue "queued" entities, then we have to make sure the
      // export queue is empty or we might be re-queuing entities that already
      // are in the queue.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = \Drupal::getContainer()->get('queue')->get('acquia_contenthub_export_queue');
      if ($queue->numberOfItems() > 0) {
        return drush_set_error('You cannot audit queued entities when the queue is not empty because you run the risk of re-queuing the same entities. Please retry when the queue is empty.');
      }
      $entities = $entities_tracking->getQueuedEntities($entity_type_id);
      break;

    default:
      return drush_set_error('You can only use the following values for status: EXPORTED, INITIATED, REINDEX, QUEUED.');
  }

  drush_print(dt('Auditing entities with export status = @status...', [
    '@status' => $status,
  ]));

  // Creating the batch process.
  $operations = [];
  $chunks = array_chunk($entities, 50);
  foreach ($chunks as $chunk) {
    $operations[] = [
      'acquia_contenthub_audit_publisher',
      [$chunk, $publish],
    ];
  }

  // Setting up batch process.
  $batch = [
    'title' => dt("Checks published entities with Content Hub for correct status"),
    'operations' => $operations,
    'finished' => 'acquia_contenthub_audit_publisher_finished',
  ];

  // Batch processing.
  batch_set($batch);

  // Start the batch process.
  drush_backend_batch_process();

}

/**
 * Log size of our queues to Drupal watchdog.
 */
function drush_acquia_contenthub_watchdog_queue_sizes() {
  $queue = drush_queue_get_class();
  $sizes = '';
  foreach (['acquia_contenthub_export_queue', 'acquia_contenthub_import_queue'] as $name) {
    $sizes .= $name . '=' . $queue->getQueue($name)->numberOfItems() . ";";
  }
  $logger = \Drupal::getContainer()->get('logger.factory');
  $logger->get('acquia_contenthub')->debug('Content Hub queues size tracking: %sizes', [
    '%sizes' => $sizes,
  ]);
}
