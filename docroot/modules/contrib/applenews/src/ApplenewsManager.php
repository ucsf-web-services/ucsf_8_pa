<?php

namespace Drupal\applenews;

use ChapterThree\AppleNewsAPI\Document\Components\Text;
use Drupal\applenews\Entity\ApplenewsArticle;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Applenews manager contains common functions to manage fields.
 */
class ApplenewsManager {
  use StringTranslationTrait;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The applenews settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The date formatter service.
   *
   * @var \Drupal\applenews\PublisherInterface
   */
  protected $publisher;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Messenger service for showing messages to the user.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * ApplenewsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   Serializer.
   * @param \Drupal\applenews\PublisherInterface $publisher
   *   Apple news publisher.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service for showing messages to the user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, TranslationInterface $string_translation, Serializer $serializer, PublisherInterface $publisher, LoggerInterface $logger, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->config = $config_factory->get('applenews.settings');
    $this->stringTranslation = $string_translation;
    $this->serializer = $serializer;
    $this->publisher = $publisher;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * Callback for hook_entity_insert and hook_entity_update.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being inserted or updated.
   */
  public function entitySave(EntityInterface $entity) {
    try {
      // On successful post, save response details on entity.
      $this->syncToAppleNews($entity);
    }
    catch (\Exception $e) {
      $this->logger->error(sprintf('Error while trying to save an article in Apple News: %s', $e->getMessage()));
    }
  }

  /**
   * Callback for hook_entity_presave.
   *
   * Enforce properties of the Apple News field(s) based on published status if
   * known.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity being saved.
   */
  public function entityPreSave(EntityInterface $entity) {
    $fields = $this->getFields($entity->getEntityTypeId(), $entity);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity $fields */
    foreach (array_keys($fields) as $field_name) {
      $field = $entity->get($field_name);
      // Force set preview when we can determine that the entity is unpublished.
      if (!$field->is_preview && $entity instanceof EntityPublishedInterface && !$entity->isPublished()) {
        $field->is_preview = TRUE;
        $this->messenger->addMessage('Apple News article was automatically set to Preview since this content is unpublished.');
      }
      // Force unset preview when we can determine that the entity changes from
      // unpublished to published.
      elseif ($field->is_preview && $entity instanceof EntityPublishedInterface && isset($entity->original) && !$entity->original->isPublished() && $entity->isPublished()) {
        $field->is_preview = FALSE;
        $this->messenger->addMessage('Apple News article was automatically set to Published since this content was changed to published.');
      }
    }
  }

  /**
   * Get all Apple News fields for the given entity type.
   *
   * Optionally you can pass a specific entity to only return the Apple News
   * fields that apply to that entity. This is useful for cases like migration,
   * where the entity might not have the field.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   Entity which the Apple News fields should be present on.
   *
   * @return array
   *   Associative array with the keys being field names and value is an array
   *   with two entries:
   *   - type: The field type.
   *   - bundles: An associative array of the bundles in which the field
   *     appears, where the keys and values are both the bundle's machine name.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   When the given entity type id does not exist.
   */
  public function getFields($entity_type_id, EntityInterface $entity = null) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    if (!$entity_type->entityClassImplements(FieldableEntityInterface::class)) {
      return [];
    }

    $field_map = $this->entityFieldManager->getFieldMapByFieldType('applenews_default');
    $entity_field_map = isset($field_map[$entity_type_id]) ? $field_map[$entity_type_id] : [];
    if ($entity) {
      foreach (array_keys($entity_field_map) as $field_name) {
        // For cases like migration, entity might not have the field.
        if (!$entity->hasField($field_name)) {
          unset($entity_field_map[$field_name]);
        }
      }
    }
    return $entity_field_map;
  }

  /**
   * Post article to selected channels with given template.
   *
   * Using single method here for create and update as it is possible from
   * entity (e.g. node) UI to create an entity without publishing to Apple News
   * and decide to publish on entity update.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity associated with AppleNews.
   *
   * @return bool
   *   Response of post. TRUE if successful.
   *
   * @deprecated Use \Drupal\applenews\ApplenewsManager::entitySave instead.
   */
  public function postArticle(EntityInterface $entity) {
    $fields = $this->getFields($entity->getEntityTypeId());
    if (!$fields) {
      return FALSE;
    }
    $this->syncToAppleNews($entity);
    return TRUE;
  }

  /**
   * Sync the entity to Apple News.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be sync'd.
   */
  protected function syncToAppleNews(EntityInterface $entity) {
    $fields = $this->getFields($entity->getEntityTypeId(), $entity);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity $fields */
    foreach (array_keys($fields) as $field_name) {
      $field = $entity->get($field_name);
      if ($field->status) {
        // Check if we are going from Live to Preview. Apple doesn't support
        // updating a Live article to Preview, so we have to first delete the
        // article.
        // @see https://developer.apple.com/documentation/apple_news/update_an_article
        if (isset($entity->original) && ($field_original = $entity->original->get($field_name)) && !$field_original->is_preview && $field->is_preview) {
          $this->deleteByField($entity, $field);
          unset($field->article);
        }

        // Post the article to Apple News.
        $this->saveToAppleNews($entity, $field);
      }
      elseif ($field->article) {
        // Delete the article from Apple News.
        $this->deleteByField($entity, $field);
      }
    }
  }

  /**
   * Save the given entity for the given Apple News field to Apple News.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to save to Apple News.
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   Apple News field with the details for saving to Apple News.
   */
  protected function saveToAppleNews(EntityInterface $entity, FieldItemListInterface $field) {
    $template = $field->template;
    $channels = unserialize($field->channels);
    $document = $this->getDocumentDataFromEntity($entity, $template);
    $data = [
      'json' => $document,
    ];
    foreach ($channels as $channel_id => $sections) {
      // Publish for the first time.
      if (!$field->article) {
        $data['metadata'] = $this->getMetaData($sections, NULL, $field->is_preview);
        $response = $this->doPost($channel_id, $data);
        $article = ApplenewsArticle::create([
          'entity_id' => $entity->id(),
          'entity_type' => $entity->getEntityType()->id(),
          'field_name' => $field->getName(),
        ]);
        $article->updateFromResponse($response)->save();
      }
      else {
        /** @var \Drupal\applenews\Entity\ApplenewsArticle $article */
        $article = $field->article;
        // hook_entity_update get called on ->save(). Avoid multiple calls.
        $data['metadata'] = $this->getMetaData($sections, $article->getRevision(), $field->is_preview);
        $response = $this->doUpdate($article->getArticleId(), $data);
        $article->updateFromResponse($response)->save();
      }
    }
  }

  /**
   * Fetches metadata.
   *
   * @param array $sections
   *   An array of section ids.
   * @param null|string $revision_id
   *   Revision ID for article update.
   * @param bool $is_preview
   *   Is the article published as a preview.
   *
   * @return string
   *   JSON metadata string.
   */
  protected function getMetadata(array $sections, $revision_id = NULL, $is_preview = FALSE) {
    foreach ($sections as $section_id => $flag) {
      $section_urls[] = $this->config->get('endpoint') . '/sections/' . $section_id;
    }
    $data = [
      'links' => [
        'sections' => $section_urls,
      ],
      'isPreview' => $is_preview,
    ];
    if ($revision_id) {
      $data['revision'] = $revision_id;
    }
    return json_encode(['data' => $data], JSON_UNESCAPED_SLASHES);
  }

  /**
   * Retrieve article.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity associated with AppleNews.
   * @param string $field_name
   *   String applenews field name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Apple News Article entity if exist, NULL otherwise.
   */
  public static function getArticle(EntityInterface $entity, $field_name) {
    try {
      $query = \Drupal::entityQuery('applenews_article');

      $ids = $query
        ->condition('entity_type', $entity->getEntityType()->id())
        ->condition('entity_id', $entity->id())
        ->condition('field_name', $field_name)
        ->execute();
      if (!empty($ids)) {
        $articles = \Drupal::entityTypeManager()->getStorage('applenews_article')->loadMultiple($ids);
        // We expect to have only one article.
        foreach ($articles as $article) {
          return $article;
        }
      }
    }
    catch (\Exception $e) {
      // Do not throw exception.
    }

    return NULL;
  }

  /**
   * Delete an article from given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity associated with AppleNews.
   *
   * @return object
   *   Response object.
   */
  public function delete(EntityInterface $entity) {
    $fields = $this->getFields($entity->getEntityTypeId(), $entity);

    foreach (array_keys($fields) as $field_name) {
      $article = self::getArticle($entity, $field_name);
      if ($article) {
        // Delete article from remote.
        $this->doDelete($article->getArticleId());
        // Delete corresponding applenews_article entity.
        $article->delete();
      }
    }
  }

  /**
   * Delete the article for the given entity and Apple News field pair.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to delete the Apple News article for.
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The Apple News field to delete the article for.
   */
  public function deleteByField(EntityInterface $entity, FieldItemListInterface $field) {
    /** @var \Drupal\applenews\Entity\ApplenewsArticle $article */
    $article = self::getArticle($entity, $field->getName());
    if ($article) {
      // Delete article from remote.
      $this->doDelete($article->getArticleId());
      // Delete corresponding applenews_article entity.
      $article->delete();
    }
  }

  /**
   * Delete an article.
   *
   * @param string $article_id
   *   String article UUID.
   *
   * @return object
   *   Response object.
   */
  protected function doDelete($article_id) {
    return $this->publisher->deleteArticle($article_id);
  }

  /**
   * Update an article.
   *
   * @param string $article_id
   *   String article UUID.
   * @param array $data
   *   Data array.
   *
   * @return object
   *   Response object.
   */
  protected function doUpdate($article_id, array $data) {
    return $this->publisher->updateArticle($article_id, $data);
  }

  /**
   * Posts article.
   *
   * @param string $channel_id
   *   String channel ID.
   * @param array $data
   *   JSON Data string.
   *
   * @return object
   *   Response object.
   */
  protected function doPost($channel_id, array $data) {
    return $this->publisher->postArticle($channel_id, $data);
  }

  /**
   * Generates document from entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity associated with AppleNews.
   * @param string $template
   *   String template ID.
   *
   * @return string
   *   JSON string document.
   */
  public function getDocumentDataFromEntity(EntityInterface $entity, $template) {
    global $base_url;
    $context['template_id'] = $template;
    /** @var \ChapterThree\AppleNewsAPI\Document $document */
    $document = $this->serializer->normalize($entity, 'applenews', $context);

    /** @var \ChapterThree\AppleNewsAPI\Document\Components\Text $component */
    if (!empty($document['components'])) {
      foreach ($document['components'] as $index => $component) {
        if (!$component instanceof Text) {
          continue;
        }
        $component->setText(Html::transformRootRelativeUrlsToAbsolute($component->getText(), $base_url));
      }
    }
    return Json::encode($document);
  }

}
