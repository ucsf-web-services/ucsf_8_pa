<?php

namespace Drupal\ucsf_applenews\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Body;
use ChapterThree\AppleNewsAPI\Document\Components\Byline;
use ChapterThree\AppleNewsAPI\Document\Components\Gallery;
use ChapterThree\AppleNewsAPI\Document\Components\Photo;
use ChapterThree\AppleNewsAPI\Document\Components\Quote;
use ChapterThree\AppleNewsAPI\Document\Components\Video;
use ChapterThree\AppleNewsAPI\Document\GalleryItem;
use ChapterThree\AppleNewsAPI\Document\Margin;
use ChapterThree\AppleNewsAPI\Document\Styles\ComponentTextStyle;
use ChapterThree\AppleNewsAPI\Document\Styles\TextStyle;
use Drupal\applenews\Normalizer\ApplenewsTextComponentNormalizer;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\Url;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * Override ApplenewsTextComponentNormalizer for UCSF exports.
 */
class UcsfApplenewsTextComponentNormalizer extends ApplenewsTextComponentNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {

    if ($data['id'] == 'default_text:byline' &&
      isset($data['component_data']['text']['field_name']) &&
      $data['component_data']['text']['field_name'] == 'field_author'
    ) {
      return $this->normalizeByline($data, $format, $context);
    }

    if ($data['id'] == 'ucsf_entityref:ucsf_body' &&
      isset($data['component_data']['text']['field_name']) &&
      $data['component_data']['text']['field_name'] == 'field_content_panel'
    ) {
      return $this->normalizeContentPanel($data, $format, $context);
    }

    return parent::normalize($data, $format, $context);
  }

  /**
   * Body from field_content_panel.
   *
   * @throws \Exception
   *   Unexpected paragraph type.
   */
  protected function normalizeContentPanel($data, $format = NULL, array $context = []) {
    $components = [];

    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $context['entity'];

    $field_name = $data['component_data']['text']['field_name'];
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $field */
    $field = $entity->get($field_name);
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($field->referencedEntities() as $paragraph) {

      $component = NULL;

      switch ($paragraph->getType()) {

        case 'blockquote':
          $text = '';
          /** @var \Drupal\text\Plugin\Field\FieldType\TextLongItem $item */
          foreach ($paragraph->get('field_blockquote_body') as $item) {
            $text .= $this->htmlValue($item->get('value')->getValue());
          }
          $component = new Quote($text);
          $component->setLayout(
            $this->getComponentLayout($data['component_layout']));
          $components[] = $component;
          break;

        case 'featured_video':
          /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $item */
          foreach ($paragraph->get('field_video_link_media') as $item) {
            /** @var \Drupal\paragraphs\Entity\Paragraph $video_paragraph */
            $video_paragraph = $item->entity;
            /** @var \Drupal\video_embed_field\Plugin\Field\FieldType\VideoEmbedField $video_field */
            $video_field = $video_paragraph
              ->get('field_media_video_embed_field')->get(0);
            if ($url = $video_field->get('value')->getString()) {
              $component = new Video($url);
              /** @var \Drupal\text\Plugin\Field\FieldType\TextLongItem $caption */
              $caption = $paragraph->get('field_video_caption')->get(0);
              $caption = $this->textValue($caption->get('value')->getValue());
              $component->setCaption($caption);
              $component->setLayout(
                $this->getComponentLayout($data['component_layout']));
              $components[] = $component;
              break;
            }
          }
          break;

        case 'text_block':
          $text = '';
          foreach ($paragraph->get('field_text_body') as $item) {
            $text .= $this->htmlValue($item->get('value')->getValue());
          }
          $component = new Body($text);
          $component->setFormat('html');
          $component->setLayout(
            $this->getComponentLayout($data['component_layout']));
          $components[] = $component;
          break;

        case 'gallery':
          $gallery_items = [];
          /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $gallery_item_ref */
          foreach ($paragraph->get('field_gallery_items') as $gallery_item_ref) {
            /** @var \Drupal\paragraphs\Entity\Paragraph $gallery_item */
            $video_paragraph = $gallery_item_ref->entity;
            /** @var \Drupal\media\Entity\Media $media */
            $media = $video_paragraph->get('field_gallery_image')->entity;
            /** @var \Drupal\file\Entity\File $file */
            $file = $media->get('field_media_image')->entity;
            $component = new GalleryItem($file->url());
            $caption = $video_paragraph->get('field_gallery_caption')->get(0);
            $caption = $this->textValue($caption->get('value')->getValue());
            $component->setCaption($caption);
            $gallery_items[] = $component;
          }
          $component = new Gallery($gallery_items);
          $component->setLayout(
            $this->getComponentLayout($data['component_layout']));
          $components[] = $component;
          break;

        default:
          throw new \Exception('need to handle paragraph type');

      }
    }

    return $components;
  }

  /**
   * Body.
   *
   * Not in use, leaving as an example of parsing a markup field, and
   * normalizing a field into multiple components.
   */
  protected function normalizeBody($data, $format = NULL, array $context = []) {
    $components = [];

    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $context['entity'];

    $field_name = $data['component_data']['text']['field_name'];
    $context['field_property'] = $data['component_data']['text']['field_property'];
    /** @var \Symfony\Component\Serializer\Serializer $serializer */
    $serializer = $this->serializer;
    $text = $serializer->normalize($entity->get($field_name), $format, $context);

    // Add first img as photo component.
    $doc = new \DOMDocument();
    $libxml_previous_state = libxml_use_internal_errors(TRUE);
    if (!$doc->loadHTML($text)) {
      throw new NotNormalizableValueException('Could not parse body HTML.');
    }
    $xp = new \DOMXPath($doc);
    /** @var \DOMElement $img */
    foreach ($xp->query('//img') as $img) {
      if (!$img->hasAttribute('src')) {
        continue;
      }
      if (!$url = $img->getAttribute('src')) {
        continue;
      }
      $url = Url::fromUserInput($url, ['absolute' => TRUE])->toString();
      $components['img'] = new Photo($url);
      $layout = $this->getComponentLayout($data['component_layout']);
      $layout
        ->setIgnoreDocumentGutter('both')
        ->setIgnoreDocumentMargin('both')
        ->setMargin(new Margin(15, 15));
      $components['img']->setLayout($layout);
      break;
    }
    libxml_clear_errors();
    libxml_use_internal_errors($libxml_previous_state);

    $components['body'] = new Body($this->htmlValue($text));

    // Text style.
    $link_style = new TextStyle();
    $link_style
      ->setFontName('HelveticaNeue-Medium')
      ->setFontSize(18)
      ->setTextColor('#0071AD')
      ->setUnderline(FALSE);
    $style = new ComponentTextStyle();
    $style
      ->setFontName('HelveticaNeue')
      ->setFontSize(18)
      ->setHyphenation(FALSE)
      ->setLineHeight(26)
      ->setLinkStyle($link_style)
      ->setStrikethrough(FALSE)
      ->setTextAlignment('left')
      ->setTextColor('#000')
      ->setTextTransform('none')
      ->setUnderline(FALSE)
      ->setVerticalAlignment('baseline');
    $components['body']->setTextStyle($style);

    $components['body']->setFormat($data['component_data']['format']);
    $components['body']->setLayout($this->getComponentLayout($data['component_layout']));

    return $components;
  }

  /**
   * News author.
   */
  protected function normalizeByline($data, $format = NULL, array $context = []) {

    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $context['entity'];

    // Generate content.
    /** @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem $date */
    try {
      $date = $entity->get('field_date_and_time')->get(0);
    }
    catch (MissingDataException $e) {
      return FALSE;
    }
    $date = \Drupal::service('date.formatter')->format(
      strtotime($date->getString()),
      'news_date');
    $field_name = $data['component_data']['text']['field_name'];
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $authors */
    $authors = $entity->get($field_name);
    $names = [];
    /** @var \Drupal\node\Entity\Node $author */
    foreach ($authors->referencedEntities() as $author) {
      $names[] = array_filter([
        $author->get('field_first_name')->getString(),
        $author->get('field_middle_name')->getString(),
        $author->get('field_last_name')->getString(),
      ]);
    }
    $names = array_map(function ($name) {
      return implode(' ', $name);
    }, $names);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $authors */
    $authors = $entity->get('field_custom_author');
    /** @var \Drupal\taxonomy\Entity\Term $author */
    foreach ($authors->referencedEntities() as $author) {
      $names[] = $author->getName();
    }
    if (!$names) {
      return FALSE;
    }
    $component = new Byline("By " . implode(', ', $names) .
      ' on ' . $date . '.');

    // Text style.
    $style = new ComponentTextStyle();
    $style
      ->setFontName("HelveticaNeue-Italic")
      ->setFontSize(14)
      ->setHyphenation(FALSE)
      ->setStrikethrough(FALSE)
      ->setTextAlignment('left')
      ->setTextColor('#646464')
      ->setTextTransform('none')
      ->setUnderline(FALSE)
      ->setVerticalAlignment('baseline');
    $component->setTextStyle($style);

    $component->setFormat($data['component_data']['format']);
    $component->setLayout($this->getComponentLayout($data['component_layout']));

    return $component;
  }

  /**
   * Apple news HTML subset.
   */
  protected function htmlValue($str) {
    return trim(
      html_entity_decode(
        strip_tags($str, self::ALLOWED_HTML_ELEMENTS)
      ),
      " \t\n\r\0\x0B\xC2\xA0"
    );
  }

  /**
   * Strips tags and html entities, trims whitespace.
   */
  protected function textValue($str) {
    return trim(
      html_entity_decode(
        strip_tags($str)
      ),
      " \t\n\r\0\x0B\xC2\xA0"
    );
  }

}
