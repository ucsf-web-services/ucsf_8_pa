<?php

namespace Drupal\applenews\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Layouts\Layout;
use ChapterThree\AppleNewsAPI\Document;
use Drupal\applenews\Entity\ApplenewsTemplate;
use Drupal\applenews\Event\DocumentPostTransformEvent;
use Drupal\applenews\Repository\ApplenewsTemplateRepository;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use ChapterThree\AppleNewsAPI\Document\Components\Component;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Applenews content entity normalizer.
 *
 * Takes a content entity, normalizes it into a
 * \ChapterThree\AppleNewsAPI\Document.
 */
class ApplenewsContentEntityNormalizer extends ApplenewsNormalizerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Apple news template repository.
   *
   * @var \Drupal\applenews\Repository\ApplenewsTemplateRepository
   */
  protected $templateRepository;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs an ApplenewsTemplateSelection object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\applenews\Repository\ApplenewsTemplateRepository $template_repository
   *   Apple news template repository.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, ApplenewsTemplateRepository $template_repository, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->templateRepository = $template_repository;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // Only consider this normalizer if we are trying to normalize a content
    // entity into the 'applenews' format.
    return $format === $this->format && $data instanceof ContentEntityInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    assert($object instanceof ContentEntityInterface);
    $template = $this->templateRepository->getTemplateByTemplateId($context['template_id']);
    $document = new Document($object->uuid(), $object->getTitle(), $this->getLangcodeForEntity($object), $this->getLayoutFromTemplate($template));

    $context['entity'] = $object;
    foreach ($template->getComponents() as $component) {
      $normalized = $this->serializer->normalize($component, $format, $context);
      if (!$normalized) {
        continue;
      }
      elseif ($normalized instanceof Component) {
        $normalized = [$normalized];
      }

      foreach ($normalized as $normalized_component) {
        if ($normalized_component instanceof Component) {
          $document->addComponent($normalized_component);
        }
      }
    }

    // @todo Load only default and used custom styles.
    $text_styles = $this->entityTypeManager->getStorage('applenews_text_style')->loadMultiple();
    foreach ($text_styles as $text_style) {
      /** @var \Drupal\applenews\Entity\ApplenewsTextStyle $text_style */
      $document->addTextStyle($text_style->id(), $text_style->toObject());
    }

    $event = new DocumentPostTransformEvent($object, $document, $template);
    $this->eventDispatcher->dispatch(DocumentPostTransformEvent::DOCUMENT_POST_TRANSFORM_EVENT, $event);

    // @todo It's atypical for a normalizer service to end this way. Typically
    //   the normalizer would leave it completely in the normalized format, here
    //   the Apple News Document object, and then defer to the encoder to
    //   convert to a format suitable for transmission (eg. JSON). Here the
    //   jsonSerialize() take a half step towards encoding. Only the top level
    //   properties of the Document object are added as keys to an array. The
    //   children are still left in their ChapterThree\AppleNewsAPI\Document*
    //   objects. There also isn't an encoder service at this point, so client
    //   code must use the normalizer and json_encode the result from here.
    return $document->jsonSerialize();
  }

  /**
   * Helper to get the appropriate langcode for the given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A content entity.
   *
   * @return string
   *   The langcode for the given entity.
   */
  protected function getLangcodeForEntity(ContentEntityInterface $entity): string {
    $langcode = $entity->language()->getId();
    // If language is not specified , fallback to site default.
    if ($langcode === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }
    return $langcode;
  }

  /**
   * Get a Layout object from the configured properties in the template.
   *
   * @param \Drupal\applenews\Entity\ApplenewsTemplate $template
   *   Apple news template entity.
   *
   * @return \ChapterThree\AppleNewsAPI\Document\Layouts\Layout
   *   Apple News document layout object.
   */
  protected function getLayoutFromTemplate(ApplenewsTemplate $template): Layout {
    $layout = new Layout($template->columns, $template->width);
    $layout
      ->setMargin($template->margin)
      ->setGutter($template->gutter);
    return $layout;
  }

}
