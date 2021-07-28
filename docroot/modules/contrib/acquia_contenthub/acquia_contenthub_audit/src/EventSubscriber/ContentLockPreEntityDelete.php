<?php

namespace Drupal\acquia_contenthub_audit\EventSubscriber;

use Drupal\acquia_contenthub_audit\AcquiaContentHubAuditEvents;
use Drupal\acquia_contenthub_audit\Event\AuditPreEntityDeleteEvent;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Content lock that occurs pre-entity delete.
 *
 * @package Drupal\acquia_contenthub_audit\EventSubscriber
 */
class ContentLockPreEntityDelete implements EventSubscriberInterface {

  /**
   * The Content Lock Service.
   *
   * @var \Drupal\content_lock\ContentLock\ContentLock
   */
  protected $contentLock;

  /**
   * ContentLockPreEntityDelete constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $handler) {
    if ($handler->moduleExists('content_lock')) {
      $this->contentLock = \Drupal::service('content_lock');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubAuditEvents::PRE_ENTITY_DELETE] =
      ['onPreEntityDelete', 50];
    return $events;
  }

  /**
   * Deletes content locks on a particular entity.
   *
   * @param \Drupal\acquia_contenthub_audit\Event\AuditPreEntityDeleteEvent $event
   *   The audit pre entity delete event object.
   */
  public function onPreEntityDelete(AuditPreEntityDeleteEvent $event) {
    // If content_lock module is not enabled then exit inmediately.
    if (!$this->contentLock) {
      return;
    }
    $entity = $event->getEntity();

    // If translation lock is enabled for this entity type, remove lock per
    // each language.
    if ($this->contentLock->isTranslationLockEnabled($entity->getEntityTypeId())) {
      $languages = $entity->getTranslationLanguages();
      foreach (array_keys($languages) as $langcode) {
        // Release entity lock.
        $this->contentLock->release($entity->id(), $langcode, NULL, NULL, $entity->getEntityTypeId());
      }
    }
    else {
      // Release entity lock.
      $this->contentLock->release($entity->id(), LanguageInterface::LANGCODE_NOT_SPECIFIED, NULL, NULL, $entity->getEntityTypeId());
    }
  }

}
