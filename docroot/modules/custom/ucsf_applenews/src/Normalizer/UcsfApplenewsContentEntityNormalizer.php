<?php

namespace Drupal\ucsf_applenews\Normalizer;

use Drupal\applenews\Normalizer\ApplenewsContentEntityNormalizer;
use ChapterThree\AppleNewsAPI\Document\Layouts\Layout;
use ChapterThree\AppleNewsAPI\Document;
use ChapterThree\AppleNewsAPI\Document\Metadata;
use ChapterThree\AppleNewsAPI\Document\Markdown;
use Drupal\Core\Language\LanguageInterface;
use ChapterThree\AppleNewsAPI\Document\Components\Component;
use ChapterThree\AppleNewsAPI\Document\Components\Title;
use ChapterThree\AppleNewsAPI\Document\Components\Header;
use ChapterThree\AppleNewsAPI\Document\Components\Photo;
use ChapterThree\AppleNewsAPI\Document\Components\EmbedWebVideo;
use Drupal\node\Entity\Node;

/**
 * Override ApplenewsContentEntityNormalizer for UCSF exports.
 */
class UcsfApplenewsContentEntityNormalizer extends ApplenewsContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    /** @var \Drupal\node\Entity\Node $data */

    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\StringItem $title_field */
    foreach ($data->get('field_apple_news_title') as $title_field) {
      if ($title = $title_field->getString()) {
        break;
      }
    }
    if (empty($title)) {
      $title = $data->getTitle();
    }

    /** @var \Drupal\applenews\Entity\ApplenewsTemplate $template */
    $template = $this->entityTypeManager->getStorage('applenews_template')
      ->load($context['template_id']);
    $layout = new Layout($template->columns, $template->width);
    $langcode = $data->language()->getId();
    // If language is not specified , fallback to site default.
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    }
    $layout
      ->setMargin($template->margin)
      ->setGutter($template->gutter);
    $document = new Document($data->uuid(), $title, $langcode, $layout);

    //figure out how to set the thumbnail here.
    $card_image = $data->get('field_card_image');
    foreach ($card_image->referencedEntities() as $media) {
      break;
    }

    if (!empty($media)) {
      $file = $media->get('field_media_image')->entity;
      $thumbnail = $file->createFileUrl(FALSE);
    } else {
      $thumbnail = 'https://www.ucsf.edu/sites/default/files/2019-04/ucsf_tapestry_portal_full_1230.jpg';
    }

    $metadata = new Metadata();
    if ($data instanceof Node) {
      $info = \Drupal::service('extension.list.module')->getExtensionInfo('applenews');
      $version = $info['core'] ?? '';
      if (!empty($info['version'])) {
        $version .= '-' . $info['version'];
      }
      $metadata
        ->setCanonicalURL($data->toUrl()->setAbsolute()->toString())
        ->setDateCreated(date('c', $data->getCreatedTime()))
        ->setDateModified(date('c', $data->getChangedTime()))
        ->setGeneratorIdentifier('DrupalAppleNews')
        ->setGeneratorName('Apple News Drupal Module')
        ->setGeneratorIdentifier($version)
        ->setThumbnailURL($thumbnail);
    }

    $document->setMetadata($metadata);

    if ($header = $this->articleHeader($data, $format, $context)) {
      $document->addComponent($header);
    }

    $component = new Title($title);
    $component->setTextStyle(_ucsf_applenews_title_component_text_style());
    $component->setLayout(_ucsf_applenews_title_component_layout());
    $document->addComponent($component);

    $context['entity'] = $data;
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

    // @todo: Load only default and used custom styles.
    $text_styles = $this->entityTypeManager->getStorage('applenews_text_style')
      ->loadMultiple();
    foreach ($text_styles as $id => $text_style) {
      /** @var \Drupal\applenews\Entity\ApplenewsTextStyle $text_style */
      $document->addTextStyle($text_style->id(), $text_style->toObject());
    }
    return $document->jsonSerialize();
  }

  /**
   * Article header.
   */
  protected function articleHeader($entity, $format, $context) {
    // Only add header when banner layout is: Medium, Feature, Feature Overlay Dark, and Feature Overlay Light.
    if (!in_array($entity->get('field_banner_layout')->value, ['medium', 'feature', 'featureoverlaydark', 'featureoverlaylight'])) {
      return NULL;
    }

    $header = new Header('header-' . $entity->id());

    if ($banner_image = $this->getReferencedEntity('field_banner_image', $entity)) {
      if ($file = $this->getReferencedEntity('field_media_image', $banner_image)) {
        $banner = new Photo($file->createFileUrl(FALSE), 'photo-' . $entity->id());
        $banner->setCaption((new Markdown())->convert($banner_image->get('field_caption')->value));
      }
    }
    else if ($banner_video = $this->getReferencedEntity('field_video_banner', $entity)) {
      $banner = new EmbedWebVideo($banner_video->get('field_media_video_embed_field')->value, 'video-embed-' . $entity->id());
    }

    if ($banner) {
      $banner->setLayout(_ucsf_applenews_banner_component_layout());
      $header->addComponent($banner);//$this->serializer->normalize($banner, $format, $context));
      $header->setLayout(_ucsf_applenews_main_header_component_layout());
      return $header;
    }
  }

  /**
   * Get product reference.
   */
  public function getReferencedEntity($field_name, $entity) {
    if (!$entity->get($field_name)->isEmpty()) {
      return $entity->get($field_name)->first()->get('entity')->getValue();
    }
  }

}
