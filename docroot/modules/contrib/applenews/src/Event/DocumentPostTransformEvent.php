<?php

namespace Drupal\applenews\Event;

use ChapterThree\AppleNewsAPI\Document;
use Drupal\applenews\Entity\ApplenewsTemplate;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fires after an entity has been transformed into a Apple News Document.
 */
class DocumentPostTransformEvent extends Event {

  /**
   * The document post transform event type.
   */
  const DOCUMENT_POST_TRANSFORM_EVENT = 'applenews.document_post_transform';

  /**
   * Some content entity, currently only nodes are supported.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The transformed entity as an Apple News document.
   *
   * @var \ChapterThree\AppleNewsAPI\Document
   */
  protected $document;

  /**
   * The Apple News template entity.
   *
   * @var \Drupal\applenews\Entity\ApplenewsTemplate
   */
  protected $template;

  /**
   * DocumentPostTransformEvent constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Some content entity, currently only nodes are supported.
   * @param \ChapterThree\AppleNewsAPI\Document $document
   *   The transformed entity as an Apple News document.
   * @param \Drupal\applenews\Entity\ApplenewsTemplate $template
   *   The Apple News template entity.
   */
  public function __construct(ContentEntityInterface $entity, Document $document, ApplenewsTemplate $template) {
    $this->entity = $entity;
    $this->document = $document;
    $this->template = $template;
  }

  /**
   * Return the content entity that was transformed.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity that was transformed.
   */
  public function getEntity(): ContentEntityInterface {
    return $this->entity;
  }

  /**
   * Return the Apple News formatted document.
   *
   * @return \ChapterThree\AppleNewsAPI\Document
   *   The Apple News formatted document.
   */
  public function getDocument(): Document {
    return $this->document;
  }

  /**
   * Return the Apple News template used to transform the entity.
   *
   * @return \Drupal\applenews\Entity\ApplenewsTemplate
   *   The Apple News template used to transform the entity.
   */
  public function getTemplate(): ApplenewsTemplate {
    return $this->template;
  }

}
