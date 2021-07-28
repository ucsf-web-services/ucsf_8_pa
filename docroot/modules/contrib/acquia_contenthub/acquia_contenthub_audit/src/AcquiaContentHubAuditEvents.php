<?php

namespace Drupal\acquia_contenthub_audit;

/**
 * Events for Acquia Content Hub Audits.
 *
 * @package Drupal\acquia_contenthub\Event
 */
final class AcquiaContentHubAuditEvents {

  /**
   * The event fired to perform operations before deleting an entity.
   *
   * @Event
   *
   * @see \Drupal\acquia_contenthub_audit\Event\AuditPreEntityDeleteEvent
   * @see \Drupal\acquia_contenthub_audit\SubscriberAudit::deleteEntity
   *
   * @var string
   */
  const PRE_ENTITY_DELETE = 'acquia_contenthub_audit_pre_entity_delete';

}
