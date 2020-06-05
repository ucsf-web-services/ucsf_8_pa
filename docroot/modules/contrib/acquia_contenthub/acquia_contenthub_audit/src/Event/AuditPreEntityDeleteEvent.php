<?php

namespace Drupal\acquia_contenthub_audit\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired before deleting an entity.
 *
 * Class AuditPreEntityDeleteEvent.
 */
class AuditPreEntityDeleteEvent extends Event {

  /**
   * The entity about to be deleted.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * AuditPreEntityDeleteEvent constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is about to be deleted.
   */
  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * The entity to be deleted.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Entity.
   */
  public function getEntity() {
    return $this->entity;
  }

}
