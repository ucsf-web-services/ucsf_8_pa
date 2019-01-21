<?php

namespace Drupal\acquia_contenthub\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;
use Drush\Commands\DrushCommands;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drush\Log\LogLevel;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class AcquiaContenthubCommands extends DrushCommands {

  /**
   * Size of the chunk being processed at once.
   */
  const BATCH_PROCESS_CHUNK_SIZE = 50;

  /**
   * Prints the CDF from a local source (drupal site)
   *
   * @param $entity_type
   *   Entity type
   * @param $entity_id
   *   Entity identifier or entity's UUID
   *
   * @command acquia:contenthub-local
   * @aliases ach-lo,acquia-contenthub-local
   */
  public function contenthubLocal($entity_type, $entity_id) {
    $entity_type_manager = \Drupal::entityTypeManager();

    /** @var \Symfony\Component\Serializer\Serializer $serializer */
    $serializer = \Drupal::service('serializer');

    /** @var \Drupal\Core\Entity\EntityRepository $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');

    if (empty($entity_type) || empty($entity_id)) {
      throw new \Exception(dt("Missing required parameters: entity_type and entity_id (or entity's uuid)"));
    }
    elseif (!$entity_type_manager->getDefinition($entity_type)) {
      throw new \Exception(dt("Entity type @entity_type does not exist", [
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
      $this->output()->writeln(dt("Entity having entity_type = @entity_type and entity_id = @entity_id does not exist.", [
        '@entity_type' => $entity_type,
        '@entity_id' => $entity_id,
      ]));
    }
    // If nothing else, return our object structure.
    $output = $this->handleNormalizedData($serializer->normalize($entity, 'acquia_contenthub_cdf'));
    $this->output()->writeln(print_r((array) $output['entities'][0], TRUE));
  }

  /**
   * Prints the CDF from a remote source (Content Hub)
   *
   * @param $uuid
   *   Entity's UUID
   *
   * @command acquia:contenthub-remote
   * @aliases ach-re,acquia-contenthub-remote
   */
  public function contenthubRemote($uuid) {
    if (Uuid::isValid($uuid)) {
      /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
      $client_manager = \Drupal::service('acquia_contenthub.client_manager');
      if ($entity = $client_manager->createRequest('readEntity', [$uuid])) {
        return $this->output()->writeln(print_r((array) $entity, TRUE));
      }
      else {
        $this->output()->writeln(dt("The Content Hub does not have an entity with UUID = @uuid.", [
          '@uuid' => $uuid,
        ]));
      }
    }
    else {
      throw new \Exception(dt("Argument provided is not a UUID."));
    }
    return FALSE;
  }

  /**
   * Loads the CDF from a local and remote source, compares them and prints the differences.
   *
   * @param $entity_type
   *   Entity type
   * @param $uuid
   *   Entity's UUID
   *
   * @command acquia:contenthub-compare
   * @aliases ach-comp,acquia-contenthub-compare
   */
  public function contenthubCompare($entity_type, $uuid) {
    $entity_type_manager = \Drupal::entityTypeManager();

    if (!$entity_type_manager->getDefinition($entity_type)) {
      throw new \Exception(dt("The entity type provided does not exist."));
    }

    if (!Uuid::isValid($uuid)) {
      throw new \Exception(dt("Argument provided is not a UUID."));
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
      // Basic structure we'll reference for the diff.
      $local_cdf = [
        'entities' => [
          [],
        ],
      ];
    }
    else {
      $local_cdf = $this->handleNormalizedData($local_cdf);
    }

    // Get the Remote CDF version.
    $remote_cdf = $client->readEntity($uuid);
    if (!$remote_cdf) {
      $remote_cdf = [];
    }

    $local_compare = array_diff($local_cdf['entities'][0], (array) $remote_cdf);
    $this->output()->writeln("Data from the local entity that doesn't appear in the remote entity, retrieved from Content Hub Backend:");
    $this->output()->writeln(json_encode($local_compare, JSON_PRETTY_PRINT));
    $this->output()->writeln("Data from the remote entity that doesn't appear in the local entity:");
    $remote_compare = array_diff((array) $remote_cdf, $local_cdf);
    $this->output()->writeln(json_encode($remote_compare, JSON_PRETTY_PRINT));
  }

  /**
   * List entities from the Content Hub using the listEntities() method.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @option limit
   *   The number of entities to be listed
   * @option start
   *   The offset to start listing the entities (Useful for pagination).
   * @option origin
   *   The Client's Origin UUID.
   * @option language
   *   The Language that will be used to filter field values.
   * @option attributes
   *   The attributes to display for all listed entities
   * @option type
   *   The entity type
   * @option filters
   *   Filters entities according to a set of of conditions as a key=value pair separated by commas. You could use regex too.
   *
   * @command acquia:contenthub-list
   * @aliases ach-list,acquia-contenthub-list
   */
  public function contenthubList(array $options = ['limit' => NULL, 'start' => NULL, 'origin' => NULL, 'language' => NULL, 'attributes' => NULL, 'type' => NULL, 'filters' => NULL]) {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');
    $client = $client_manager->getConnection();

    $list_options = [];

    // Obtaining the limit.
    $limit = $options['limit'];
    if (isset($limit)) {
      $limit = (int) $limit;
      if ($limit < 1 || $limit > 1000) {
        throw new \Exception(dt("The limit has to be an integer from 1 to 1000."));
      }
      else {
        $list_options['limit'] = $limit;
      }
    }

    // Obtaining the offset.
    $start = $options['start'];
    if (isset($start)) {
      if (!is_numeric($start)) {
        throw new \Exception(dt("The start offset has to be numeric starting from 0."));
      }
      else {
        $list_options['start'] = $start;
      }
    }

    // Filtering by origin.
    $origin = $options['origin'];
    if (isset($origin)) {
      if (Uuid::isValid($origin)) {
        $list_options['origin'] = $origin;
      }
      else {
        throw new \Exception(dt("The origin has to be a valid UUID."));
      }
    }

    // Filtering by language.
    $language = $options['language'];
    if (isset($language)) {
      if (strlen($language) == 2) {
        $list_options['language'] = $language;
      }
      else {
        throw new \Exception(dt("The language has to be provided as a 2-letter language code."));
      }
    }

    // Filtering by fields.
    $fields = $options['attributes'];
    if (isset($fields)) {
      $list_options['fields'] = $fields;
    }

    // Filtering by type.
    $type = $options['type'];
    if (isset($type)) {
      $list_options['type'] = $type;
    }

    // Building the filters.
    $filters = $options['filters'];
    if (isset($filters)) {
      $filters = isset($filters) ? explode(",", $filters) : FALSE;
      foreach ($filters as $key => $filter) {
        list($name, $value) = explode("=", $filter);
        $filters[$name] = $value;
        unset($filters[$key]);
      }
      $list_options['filters'] = $filters;
    }
    if ($client_manager->isConnected()) {
      $list = $client_manager->createRequest('listEntities', [$list_options]);
      $this->output()->writeln(print_r($list, TRUE));
      return $list;
    }
    else {
      throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }
  }

  /**
   * Deletes a single entity from the Content Hub.
   *
   * @param $uuid
   *   Entity's UUID
   *
   * @command acquia:contenthub-delete
   * @aliases ach-del,acquia-contenthub-delete
   */
  public function contenthubDelete($uuid) {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');

    if (!Uuid::isValid($uuid)) {
      throw new \Exception(dt("Argument provided is not a UUID."));
    }
    else {
      $warning_message = dt('Are you sure you want to delete the entity with uuid = @uuid from the Content Hub? There is no way back from this action!', [
        '@uuid' => $uuid,
      ]);
      if ($this->io()->confirm($warning_message)) {
        $client = $client_manager->getConnection();
        if ($client->deleteEntity($uuid)) {
          $this->output()->writeln(dt("Entity with UUID = @uuid has been successfully deleted from the Content Hub.", [
            '@uuid' => $uuid,
          ]));
        }
        else {
          throw new \Exception(dt("Entity with UUID = @uuid cannot be deleted.", [
            '@uuid' => $uuid,
          ]));
        }
      }
    }
  }

  /**
   * Purges all entities from Acquia Content Hub. WARNING! Be VERY careful when using this command. This destructive command requires elevated keys. Every subsequent execution of this command will override the backup created by the previous call.
   *
   * @param $api
   *   API Key
   * @param $secret
   *   Secret Key
   *
   * @command acquia:contenthub-purge
   * @aliases ach-purge,acquia-contenthub-purge
   */
  public function contenthubPurge($api = NULL, $secret = NULL) {
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
    if ($this->io()->confirm($warning_message)) {
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
        throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
      }
      if (isset($response['success']) && $response['success'] === TRUE) {
        // Deleting exported entities from the Tracking Table.
        /** @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking $entities_tracking */
        $entities_tracking = \Drupal::getContainer()->get('acquia_contenthub.acquia_contenthub_entities_tracking');
        $entities_tracking->deleteExportedEntities();
        $this->output()->writeln("Your Subscription is being purged. All clients who have registered to received webhooks will be notified with a reindex webhook when the purge process has been completed.\n");
      }
      else {
        throw new \Exception(dt("Error trying to purge your subscription. You might require elevated keys to perform this operation."));
      }
    }
  }

  /**
   * Restores the backup taken by a previous execution of the "purge" command. WARNING! Be VERY careful when using this command. This destructive command requires elevated keys. By restoring a backup you will delete all the existing entities in your subscription.
   *
   * @param $api
   *   API Key
   * @param $secret
   *   Secret Key
   *
   * @command acquia:contenthub-restore
   * @aliases ach-restore,acquia-contenthub-restore
   */
  public function contenthubRestore($api, $secret) {
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
    if ($this->io()->confirm($warning_message)) {
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
        throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
      }
      if (isset($response['success']) && $response['success'] === TRUE) {
        $this->output()->writeln("Your Subscription is being restored. All clients who have registered to received webhooks will be notified with a reindex webhook when the restore process has been completed.\n");
      }
      else {
        throw new \Exception(dt("Error trying to restore your subscription from a backup copy. You might require elevated keys to perform this operation."));
      }
    }
  }

  /**
   * Reindexes all entities in Content Hub.
   *
   * @param $api
   *   API Key
   * @param $secret
   *   Secret Key
   *
   * @command acquia:contenthub-reindex
   * @aliases ach-reindex,acquia-contenthub-reindex
   */
  public function contenthubReindex($api, $secret) {
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
    if ($this->io()->confirm($warning_message)) {
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
        throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
      }
      if (isset($response['success']) && $response['success'] === TRUE) {
        $this->output()->writeln("Your Subscription is being re-indexed. All clients who have registered to received webhooks will be notified with a reindex webhook when the process has been completed.\n");
      }
      else {
        throw new \Exception(dt("Error trying to re-index your subscription. You might require elevated keys to perform this operation."));
      }
    }
  }

  /**
   * View Historic entity logs from Content Hub.
   *
   * @param $api
   *   API Key
   * @param $secret
   *   Secret Key
   * @param array $options
   *
   * @throws \Exception
   *
   * @internal param array $request_options An associative array of options
   *   whose values come from cli, aliases, config, etc.
   *
   * @option query
   *   The Elastic Search Query to search for logs.
   * @option size
   *   The number of log entries to be listed.
   * @option from
   *   The offset to start listing the log entries (Useful for pagination).
   *
   * @command acquia:contenthub-logs
   * @field-labels
   *   timestamp: Timestamp
   *   type: Type
   *   client: Client ID
   *   entity_uuid: Entity UUID
   *   status: Status
   *   request_id: Request ID
   *   id: ID
   *   message: Message
   * @aliases ach-logs,acquia-contenthub-logs
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function contenthubLogs($api, $secret, array $options = ['query' => NULL, 'size' => NULL, 'from' => NULL]) {
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

    $request_options = [];
    // Obtaining the limit.
    $size = $options["size"];
    if (isset($size)) {
      $size = (int) $size;
      if ($size < 1 || $size > 1000) {
        throw new \Exception(dt("The size has to be an integer from 1 to 1000."));
      }
      else {
        $request_options['size'] = $size;
      }
    }

    // Obtaining the offset.
    $from = $options["from"];
    if (isset($from)) {
      if (!is_numeric($from)) {
        throw new \Exception(dt("The start offset has to be numeric starting from 0."));
      }
      else {
        $request_options['from'] = $from;
      }
    }

    // Obtaining the query.
    $query = $options["query"];
    $query = !empty($query) ? $query : '';

    // Execute the 'history' command.
    if ($client_manager->isConnected()) {
      $logs = $client_manager->createRequest('logs', [$query, $request_options]);
    }
    else {
      throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
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
      return new RowsOfFields($rows);
    }
    else {
      throw new \Exception(dt("Error trying to print the entity logs."));
    }
  }

  /**
   * Shows Elastic Search field mappings from Content Hub.
   *
   * @command acquia:contenthub-mapping
   * @aliases ach-mapping,acquia-contenthub-mapping
   */
  public function contenthubMapping() {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');

    if ($client_manager->isConnected()) {
      $output = $client_manager->createRequest('mapping');
    }
    else {
      throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
    }

    if ($output) {
      $this->output()->writeln(print_r($output, TRUE));
    }
    else {
      throw new \Exception(dt("Error trying to print the elastic search field mappings."));
    }
  }

  /**
   * Regenerates the Shared Secret used for Webhook Verification.
   *
   * @command acquia:contenthub-regenerate-secret
   * @aliases ach-regsec,acquia-contenthub-regenerate-secret
   */
  public function contenthubRegenerateSecret() {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');
    $warning_message = "Are you sure you want to REGENERATE your shared-secret in the Content Hub?\n" .
      "*************************************************************************************\n" .
      "PROCEED WITH CAUTION. THIS COULD POTENTIALLY LEAD TO HAVING SOME SITES OUT OF SYNC.\n" .
      "Make sure you have ALL your sites correctly configured to receive webhooks before attempting to do this.\n" .
      "For more information, check https://docs.acquia.com/content-hub/known-issues.\n" .
      "*************************************************************************************\n";
    if ($this->io()->confirm($warning_message)) {
      if ($client_manager->isConnected()) {
        $output = $client_manager->createRequest('regenerateSharedSecret');
      }
      else {
        throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
      }

      if ($output) {
        $this->output()->writeln("Your Shared Secret has been regenerated. All clients who have registered to received webhooks are being notified of this change.\n");
      }
      else {
        throw new \Exception(dt("Error trying to regenerate the shared-secret in your subscription. Try again later."));
      }
    }
  }

  /**
   * Updates the Shared Secret used for Webhook Verification.
   *
   * @command acquia:contenthub-update-secret
   * @aliases ach-upsec,acquia-contenthub-update-secret
   */
  public function contenthubUpdateSecret() {
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
    $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');

    if ($client_manager->isConnected()) {
      $subscription->getSettings();
      $this->output()->writeln(dt('The shared secret has been updated.'));
    }
    else {
      throw new \Exception(dt('The Content Hub client is not connected so the shared secret can not be updated.'));
    }
  }

  /**
   * Connects a site with contenthub.
   *
   * @param array $options
   *
   * @command acquia:contenthub-connect-site
   * @aliases ach-connect,acquia-contenthub-connect-site
   *
   * @option hostname
   *   Content Hub API URL.
   * @option api_key
   *   Content Hub API Key.
   * @option secret
   *   Content Hub API Secret.
   * @option client_name
   *   The client name for this site.
   */
  public function contenthubConnectSite(array $options = ['hostname' => null, 'api_key' => null, 'secret' => null, 'client_name' => null]) {
    $config_factory = \Drupal::configFactory();
    $uuid_service = \Drupal::service('uuid');

    $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
    $config_api_key = $config->get('api_key');

    if (!empty($config_api_key)) {
      $this->logger()->log(LogLevel::CANCEL, dt('Site is already connected to Content Hub. Skipping.'));
      return;
    }

    $hostname = !empty($options['hostname']) ? $options['hostname'] : $this->io()->ask(dt('What is the Content Hub API URL?'), 'https://us-east-1.content-hub.acquia.com');
    $api_key = !empty($options['api_key']) ? $options['api_key'] : $this->io()->ask(dt('What is your Content Hub API Key?'));
    $secret = !empty($options['secret']) ? $options['secret'] : $this->io()->ask(dt('What is your Content Hub API Secret?'));
    $client_uuid = $uuid_service->generate();
    $client_name = !empty($options['client_name']) ? $options['client_name'] : $this->io()->ask(dt('What is the client name for this site?'), $client_uuid);

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
   * Disconnects a site with contenthub.
   *
   * @command acquia:contenthub-disconnect-site
   * @aliases ach-disconnect,acquia-contenthub-disconnect-site
   */
  public function contenthubDisconnectSite() {
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
    $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');
    $subscription->disconnectClient();
    // disconnectClient always returns false. Check config to be sure of success.
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
    $webhook_uuid = $config->get('webhook_uuid');
    if (!$webhook_uuid) {
      $this->logger()->log(LogLevel::SUCCESS, dt('Site has been disconnected from Content Hub.'));
    }
  }

  /**
   * Perform a webhook management operation.
   *
   * @param $op
   *   The operation to use. Options are: register, unregister, list.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @throws \Exception
   *
   * @option webhook_url
   *   The webhook URL to register or unregister.
   *
   * @command acquia:contenthub-webhooks
   * @aliases ach-wh,acquia-contenthub-webhooks
   */
  public function contenthubWebhooks($op, array $options = ['webhook_url' => NULL]) {
    $config_factory = \Drupal::configFactory();
    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $subscription */
    $subscription = \Drupal::service('acquia_contenthub.acquia_contenthub_subscription');

    $webhook_url = $options['webhook_url'];
    if (empty($webhook_url)) {
      $webhook_url = Url::fromUri('internal:/acquia-contenthub/webhook', ['absolute' => TRUE])->toString();
    }
    if ($client_manager->isConnected()) {
      $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
      switch ($op) {
        case 'register':
          $success = $subscription->registerWebhook($webhook_url);
          if (!$success) {
            $this->logger()->log(LogLevel::CANCEL, dt('Registering webhooks encountered an error.'));
          }
          else {
            $webhook_uuid = $config->get('webhook_uuid');
            $this->logger()->log(LogLevel::SUCCESS, dt('Registered Content Hub Webhook: @uuid', ['@uuid' => $webhook_uuid]));
          }
          break;

        case 'unregister':
          $webhook_url = isset($options['webhook_url']) ? $options['webhook_url'] : $config->get('webhook_url');
          $success = $subscription->unregisterWebhook($webhook_url);
          if (!$success) {
            $this->logger()->log(LogLevel::CANCEL, dt('There was an error unregistering the URL: @url', ['@url' => $webhook_url]));
          }
          break;

        case 'list':
          $settings = $subscription->getSettings();
          if (!$settings) {
            break;
          }
          $webhooks = $settings->getWebhooks();
          foreach ($webhooks as $index => $webhook) {
            $this->output()->writeln($index + 1 . '. ' . $webhook['url'] . ' - ' . $webhook['uuid']);
          }
          break;

        default:
          // Invalid operation.
          throw new \Exception(dt('The op "@op" is invalid', ['@op' => $op]));
      }
    }
    else {
      throw new \Exception(dt('The Content Hub client is not connected so the webhook operations could not be performed.'));
    }
  }

  /**
   * Deletes all entities of a particular type from Content Hub, reindex subscription and exports all deleted entities again to allow for Elasticsearch to map correct field types.
   *
   * @param $entity_type
   *   The entity type.
   * @param $api
   *   API Key
   * @param $secret
   *   Secret Key
   *
   * @throws \Exception
   *
   * @option bundle
   *   The Entity Bundle.
   *
   * @command acquia:contenthub-reset-entities
   * @aliases ach-reset,acquia-contenthub-reset-entities
   */
  public function contenthubResetEntities($entity_type, $api, $secret, array $options = ['bundle' => NULL]) {
    if (empty($entity_type)) {
      throw new \Exception(dt('You need to provide at least the entity type of the entities you want to reset.'), LogLevel::CANCEL);
    }

    // Defining the bundle.
    $bundle = $options['bundle'];

    /** @var \Drupal\acquia_contenthub\Client\ClientManager $client_manager */
    $client_manager = \Drupal::service('acquia_contenthub.client_manager');

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
    if ($this->io()->confirm($warning_message)) {
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
        throw new \Exception(dt("Error trying to re-set and reindex entities. You are required to have a registered webhook in this site before proceeding."));
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
              $this->output()->writeln(dt("You cannot perform this operation because there are entities exported to Content Hub that do not belong to this site. Please go to the origins listed (sites) and delete those entities (by doing 'ach-del <UUID>') before proceeding to reindex your subscription. Below is a list of those entities that cannot be deleted from this site:\n"));
              array_unshift($entities, $header);
              $this->output()->writeln(print_r($entities, TRUE));
              throw new \Exception(dt("Error trying to re-index your subscription. You have entities that do not belong to this site."));
            }

            // Delete entities and reindex subscription.
            $success = $reindex->setExportedEntitiesToReindex($entity_type, $bundle);
            if ($success && $reindex->isReindexSent()) {
              $this->output()->writeln("Your Subscription is being re-indexed. All clients who have registered to received webhooks will be notified with a reindex webhook when the process has been completed.\n");
            }
            elseif (!$success && $reindex->isReindexFailed()) {
              throw new \Exception(dt("Error trying to re-index your subscription. You might require elevated keys to perform this operation."));
            }
            elseif (!$success && $reindex->isReindexNone()) {
              $bundle_msg = empty($bundle) ? '' : dt("and bundle = @bundle", [
                '@bundle' => $bundle,
              ]);
              throw new \Exception(dt("You are trying to reset entities of type = '@entity_type' @bundle_msg but none of them have been exported.", [
                '@entity_type' => $entity_type,
                '@bundle_msg' => $bundle_msg,
              ]));
            }
          }
        }
        else {
          throw new \Exception(dt('You have already sent a Reindex Request to Content Hub. Please wait until the current reindex is processed and finalized before you can send another reindex request again.'));
        }
      }
      else {
        throw new \Exception(dt('Error trying to connect to the Content Hub. Make sure this site is registered to Content hub.'));
      }
    }
  }

  /**
   * Refactors the data object returned by the normalizer into a regular array.
   *
   * @param array $normalized_data
   *   The normalized array of objects.
   *
   * @return array
   *   An array that mirrors the sort of data in the remote storage.
   */
  protected function handleNormalizedData($normalized_data) {
    foreach ($normalized_data['entities'] as &$entity) {
      $entity = (array) $entity;
      foreach ($entity['attributes'] as $key => $value) {
        $entity['attributes'][$key] = (array) $value;
      }
      foreach ($entity['assets'] as $key => $value) {
        $entity['assets'][$key] = (array) $value;
      }
    }
    return $normalized_data;
  }

  /**
   * Checks published entities and republishes them to Content Hub.
   *
   * @param string $entity_type_id
   *   The Entity Type.
   * @param array $options
   *   The options array.
   *
   * @throws \Exception
   *
   * @internal param array $request_options An associative array of options
   *   whose values come from cli, aliases, config, etc.
   *
   * @option publish
   *   1 if set to republish entities, 0 (or false) if we just want to print.
   * @option status
   *   The status of the entities to audit, defaults to EXPORTED if not given.
   *
   * @command acquia:contenthub-audit-publisher
   * @aliases ach-ap,ach-audit-publisher
   */
  public function contenthubAuditPublisher($entity_type_id = NULL, array $options = ['publish' => NULL, 'status' => ContentHubEntitiesTracking::EXPORTED]) {
    // Obtaining the query.
    $publish = $options["publish"];
    $status = $options["status"];
    if ($publish) {
      $warning_message = dt('Are you sure you want to republish entities to Content Hub?');
      if ($this->io()->confirm($warning_message) == FALSE) {
        throw new \Exception(dt('Command aborted by user.'));
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
          throw new \Exception(dt('You cannot audit queued entities when the queue is not empty because you run the risk of re-queuing the same entities. Please retry when the queue is empty.'));
        }
        $entities = $entities_tracking->getQueuedEntities($entity_type_id);
        break;

      default:
        throw new \Exception(dt('You can only use the following values for status: EXPORTED, INITIATED, REINDEX, QUEUED.'));
    }

    $this->output->writeln(dt('Auditing entities with export status = @status...', [
      '@status' => $status,
    ]));

    // Creating the batch process.
    $operations = [];
    $chunks = array_chunk($entities, static::BATCH_PROCESS_CHUNK_SIZE);
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

}
