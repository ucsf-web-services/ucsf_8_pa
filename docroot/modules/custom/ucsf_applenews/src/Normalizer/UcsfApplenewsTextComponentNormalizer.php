<?php

namespace Drupal\ucsf_applenews\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Body;
use ChapterThree\AppleNewsAPI\Document\Components\Byline;
use ChapterThree\AppleNewsAPI\Document\Components\EmbedWebVideo;
use ChapterThree\AppleNewsAPI\Document\Components\Gallery;
use ChapterThree\AppleNewsAPI\Document\Components\Heading;
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

    if ($data['id'] == 'default_text:body' &&
      isset($data['component_data']['text']['field_name']) &&
      $data['component_data']['text']['field_name'] == 'body'
    ) {
      return $this->normalizeBody($data, $format, $context);
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
              $component = $this->getVideoComponent($url);
              /** @var \Drupal\text\Plugin\Field\FieldType\TextLongItem $caption */
              if ($caption = $paragraph->get('field_video_caption')->get(0)) {
                /** @var string $caption */
                if ($caption = $this->textValue($caption->get('value')->getValue())) {
                  $component->setCaption($caption);
                }
              }
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
            /** @var \Drupal\text\Plugin\Field\FieldType\TextLongItem $caption */
            if ($caption = $video_paragraph->get('field_gallery_caption')->get(0)) {
              /** @var string $caption */
              if ($caption = $this->textValue($caption->get('value')->getValue())) {
                $component->setCaption($caption);
              }
            }
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
   * If value contains certain tags (blockquote, headers...), break up the value
   * into a series of Body and Blockquote, Heading, etc. components.
   *
   * @throws \Exception
   *   Unexpected source data.
   */
  protected function normalizeBody($data, $format = NULL, array $context = []) {
    $components = [];

    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $context['entity'];

    $field_name = $data['component_data']['text']['field_name'];
    $context['field_property'] = $data['component_data']['text']['field_property'];

    /** @var \Drupal\Core\Field\FieldItemList $field */
    $field = $entity->get($field_name);
    foreach ($field as $item) {

      // Toss out tags we don't care about.
      $text = $this->htmlValue($item->get('value')->getValue(),
        '<blockquote><h1><h2><h3><h4><h5><h6><img><drupal-entity><iframe>');

      // Parse value and create components for blockquote, headers, etc.
      $inline_components = [];
      $doc = new \DOMDocument();
      $libxml_previous_state = libxml_use_internal_errors(TRUE);
      if (!$doc->loadHTML('<?xml encoding="utf-8" ?>' . $text)) {
        throw new NotNormalizableValueException('Could not parse body HTML.');
      }
      $xp = new \DOMXPath($doc);
      $xp_query = '//blockquote|//h1|//h2|//h3|//h4|//h5|//h6|//img|//drupal-entity|//iframe';
      /** @var \DOMElement $element */
      foreach ($xp->query($xp_query) as $element) {
        $component = NULL;

        switch ($element->tagName) {

          case 'blockquote':
            $component = new Quote($this->textValue($element->textContent));
            break;

          case 'h1':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading1');
            break;

          case 'h2':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading2');
            break;

          case 'h3':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading3');
            break;

          case 'h4':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading4');
            break;

          case 'h5':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading5');
            break;

          case 'h6':
            $component = new Heading($this->textValue($element->textContent));
            $component->setRole('heading6');
            break;

          case 'img':
            if ($element->hasAttribute('src')) {
              $url = $element->getAttribute('src');
              $url_parsed = parse_url($url);
              if (empty($url_parsed['host'])) {
                $url = Url::fromUserInput($url, ['absolute' => TRUE])->toString();
                $url_parsed = parse_url($url);
                if (isset($url_parsed['query'])) {
                  parse_str($url_parsed['query'], $qs);
                  if (isset($qs['itok'])) {
                    unset($qs['itok']);
                    if (empty($qs)) {
                      unset($url_parsed['query']);
                    }
                    else {
                      $url_parsed['query'] = http_build_query($qs);
                    }
                    $url = $this->unParseUrl($url_parsed);
                  }
                }
              }
              $component = new Photo($url);
              $layout = $this->getComponentLayout($data['component_layout']);
              $layout
                ->setIgnoreDocumentGutter('both')
                ->setIgnoreDocumentMargin('both')
                ->setMargin(new Margin(15, 15));
              $component->setLayout($layout);
            }
            break;

          case 'iframe':
            if ($element->hasAttribute('src')) {
              $url = $element->getAttribute('src');
              $component = $this->getVideoComponent($url);
            }
            break;

          case 'drupal-entity':
            if ($element->hasAttribute('data-entity-type') &&
              $element->getAttribute('data-entity-type') == 'media' &&
              $element->hasAttribute('data-entity-uuid')
            ) {
              $uuid = $element->getAttribute('data-entity-uuid');
              $media = \Drupal::entityTypeManager()->getStorage('media')
                ->loadByProperties(['uuid' => $uuid]);
              if ($media) {
                /** @var \Drupal\media\Entity\Media $media */
                $media = reset($media);
                if ($media->hasField('field_media_image')) {
                  /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $file */
                  $file = $media->get('field_media_image');
                  /** @var \Drupal\file\Entity\File $file */
                  $file = $file->entity;
                  if ($file) {
                    $mimetype = explode('/', $file->getMimeType());
                    if (@$mimetype[0] == 'image' &&
                      in_array(@$mimetype[1], ['jpeg', 'gif', 'png'])
                    ) {
                      $component = new Photo($file->url());
                      if ($element->hasAttribute('data-caption')) {
                        $caption = $this->textValue(
                          $element->getAttribute('data-caption'));
                        if ($caption) {
                          $component->setCaption($caption);
                        }
                      }
                    }
                    else {
                      throw new \Exception(
                        'Unexpected file mime type ' . $file->getMimeType());
                    }
                  }
                }
                elseif ($media->hasField('field_media_video_embed_field')) {
                  /** @var \Drupal\Core\Field\FieldItemList $video_field */
                  $video_field = $media->get('field_media_video_embed_field');
                  if ($url = $video_field->get(0)->getString()) {
                    $component = $this->getVideoComponent($url);
                  }
                }
              }
            }
            break;

        }

        // Replace element with a token.
        if ($component) {

          // Value of the component is the index of the component in
          // $inline_components.
          $token = $doc->createElement('applenews_component',
            count($inline_components));
          $inline_components[] = $component;

          // Replace with token, making sure the token is at the root level of
          // the value html.
          if ($element->parentNode->tagName == 'body') {
            $element->parentNode->replaceChild($token, $element);
          }
          // Nested, insert token after top level ancestor.
          else {
            $ancestor = $element->parentNode;
            $only_child = $ancestor->childNodes->length == 1;
            while ($ancestor->parentNode->tagName != 'body') {
              $ancestor = $ancestor->parentNode;
              $only_child = $only_child &&
                $ancestor->childNodes->length == 1;
            }
            // Replace ancestor since element is the only descendant.
            if ($only_child) {
              $ancestor->parentNode->replaceChild($token, $ancestor);
            }
            elseif ($ancestor->nextSibling) {
              $ancestor->parentNode->insertBefore(
                $token, $ancestor->nextSibling);
              $element->parentNode->removeChild($element);
            }
            else {
              $ancestor->parentNode->appendChild($token);
              $element->parentNode->removeChild($element);
            }
          }

        }
        // Remove even if no component generated.
        else {
          $element->parentNode->removeChild($element);
        }

      }

      $text = preg_replace('/<[!\?][^>]+>/', '', $doc->saveHTML());
      $text = str_replace(
        array('<html>', '</html>', '<body>', '</body>'),
        array('', '', '', ''),
        $text);

      libxml_clear_errors();
      libxml_use_internal_errors($libxml_previous_state);

      // Split value into multiple Body components and insert other components
      // in the correct place.
      $text = preg_split('/<\/?applenews_component>/', $text);
      foreach ($text as $i => $value) {
        if ($i % 2) {
          $components[] = $inline_components[$value];
        }
        else {
          $value = $this->htmlValue($value);
          if (empty($value)) {
            continue;
          }
          $component = new Body($value);
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
          $component->setTextStyle($style);
          $component->setFormat($data['component_data']['format']);
          $component->setLayout($this->getComponentLayout($data['component_layout']));
          $components[] = $component;
        }
      }
    }

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
   * Inverse of parse_url().
   */
  protected function unParseUrl($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
  }

  /**
   * Apple news HTML subset.
   */
  protected function htmlValue($str, $additional_elements = '') {
    $allowed_elements = self::ALLOWED_HTML_ELEMENTS . $additional_elements;
    return trim(
      html_entity_decode(
        strip_tags($str, $allowed_elements)
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

  /**
   * Generates proper video component for a url.
   */
  protected function getVideoComponent($url) {
    $url_parsed = parse_url($url);
    if (preg_match('/(youtube|vimeo)\.com$/', $url_parsed['host'])) {
      if (empty($url_parsed['scheme'])) {
        $url = 'https:' . $url;
      }
      return new EmbedWebVideo($url);
    }
    elseif (!empty($url_parsed['path'])) {
      $ext = pathinfo($url_parsed['path'], PATHINFO_EXTENSION);
      if (in_array($ext, ['mp3', 'mov', 'qt'])) {
        if (empty($url_parsed['host'])) {
          $url = Url::fromUserInput($url, ['absolute' => TRUE])
            ->toString();
        }
        return new Video($url);
      }
    }
    return NULL;
  }

}
