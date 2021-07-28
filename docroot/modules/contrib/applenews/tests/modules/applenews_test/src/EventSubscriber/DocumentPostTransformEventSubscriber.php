<?php

namespace Drupal\applenews_test\EventSubscriber;

use ChapterThree\AppleNewsAPI\Document\Metadata;
use Drupal\applenews\Event\DocumentPostTransformEvent;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to the document post transform event
 */
class DocumentPostTransformEventSubscriber implements EventSubscriberInterface {

  /**
   * After an entity has been transformed into an Apple News document, alter it.
   *
   * @param \Drupal\applenews\Event\DocumentPostTransformEvent $event
   *   Document post transform event.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function documentPostTransform(DocumentPostTransformEvent $event) {
    $document = $event->getDocument();
    $entity = $event->getEntity();

    $metadata = new Metadata();
    $metadata->addAuthor('Joe Mayo');
    $metadata->setCanonicalURL($entity->toUrl()
      ->setAbsolute(TRUE)
      ->toString()
    );
    if ($entity instanceof NodeInterface || $entity instanceof MediaInterface) {
      $created = new \DateTime();
      $created->setTimestamp($entity->getCreatedTime());
      $created = $created->format('c');
      $metadata->setDateCreated($created)
        ->setDatePublished($created);
    }
    if ($entity instanceof EntityChangedInterface) {
      $changed = new \DateTime();
      $changed->setTimestamp($entity->getChangedTime());
      $metadata->setDateModified($changed->format('c'));
    }
    $document->setMetadata($metadata);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DocumentPostTransformEvent::DOCUMENT_POST_TRANSFORM_EVENT][] = ['documentPostTransform'];
    return $events;
  }

}
