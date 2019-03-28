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
use Drupal\applenews\Normalizer\ApplenewsTextComponentNormalizer;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\Url;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * Override ApplenewsTextComponentNormalizer for UCSF exports.
 */
class UcsfApplenewsTextComponentNormalizer extends ApplenewsTextComponentNormalizer {

  /**
   * Markup elements that can't be empty.
   *
   * @var array
   */
  protected $elementsNotEmpty = [
    'p',
    'strong',
    'b',
    'em',
    'i',
    'a',
    'ul',
    'ol',
    'li',
    'sub',
    'sup',
    'del',
    's',
    'pre',
    'code',
    'samp',
    'footer',
    'aside',
    'blockquote',
  ];

  /**
   * List of div classes to remove from body content.
   *
   * @var array
   */
  protected $divClassBlacklist = [
    'wysiwyg_quote',
    'wysiwyg_twocols',
    'wysiwyg_threecols',
    'wysiwyg_dottedlist',
    'cke-videolightbox-wrapper',
    'about-cta',
    'wysiwyg_cta',
    'btn',
    'border--blue',
    'ckgreybox',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   *   Unexpected content issue.
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
          $component->setTextStyle(
            _ucsf_applenews_title_component_quote_style());
          $component->setLayout(
            _ucsf_applenews_quote_component_layout());
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
                _ucsf_applenews_video_component_layout());
              $components[] = $component;
              break;
            }
          }
          break;

        case 'text_block':
          foreach ($paragraph->get('field_text_body') as $item) {
            $components = array_merge($components,
              $this->normalizeMarkup($data, $item->get('value')->getValue()));
          }
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
            _ucsf_applenews_gallery_component_layout());
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

      $components = array_merge($components,
        $this->normalizeMarkup($data, $item->get('value')->getValue()));

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

    $component->setTextStyle(_ucsf_applenews_author_component_text_style());
    $component->setFormat($data['component_data']['format']);
    $component->setLayout($this->getComponentLayout($data['component_layout']));

    return $component;
  }

  /**
   * Parse markup into a series of components.
   */
  protected function normalizeMarkup($data, $html) {
    $components = [];

    // Toss out tags we don't care about.
    $html = $this->htmlValue($html,
      '<blockquote><h1><h2><h3><h4><h5><h6><img><drupal-entity><iframe><div>');

    // Parse value and create components for blockquote, headers, etc.
    $inline_components = [];
    $doc = new \DOMDocument();
    $libxml_previous_state = libxml_use_internal_errors(TRUE);
    if (!$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
      throw new NotNormalizableValueException('Could not parse body HTML.');
    }
    $xp = new \DOMXPath($doc);

    // Remove certain div's.
    $xp_query = '//div';
    /** @var \DOMElement $element */
    foreach ($xp->query($xp_query) as $element) {
      if (!$element->parentNode) {
        continue;
      }
      if ($element->hasAttribute('class') &&
        ($classes = preg_split('/\s\s+/', $element->getAttribute('class'))) &&
        count(array_intersect($classes, $this->divClassBlacklist))
      ) {
        $element->parentNode->removeChild($element);
      }
    }

    // Create components.
    $xp_query = '//blockquote|//h1|//h2|//h3|//h4|//h5|//h6|//img|//drupal-entity|//iframe';
    /** @var \DOMElement $element */
    foreach ($xp->query($xp_query) as $element) {
      $component = NULL;

      switch ($element->tagName) {

        case 'blockquote':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Quote($value);
            $component->setTextStyle(
              _ucsf_applenews_title_component_quote_style());
            $component->setLayout(
              _ucsf_applenews_quote_component_layout());
          }
          break;

        case 'h1':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading1');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(1));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'h2':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading2');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(2));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'h3':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading3');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(3));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'h4':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading4');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(4));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'h5':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading5');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(5));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'h6':
          if ($value = $this->textValue($element->textContent)) {
            $component = new Heading($value);
            $component->setRole('heading6');
            $component->setTextStyle(
              _ucsf_applenews_heading_component_text_style(6));
            $component->setLayout(
              _ucsf_applenews_header_component_layout());
          }
          break;

        case 'img':
          if ($element->hasAttribute('src')) {
            $url = $element->getAttribute('src');
            $url_parsed = parse_url($url);
            if (empty($url_parsed['host'])) {
              try {
                $url = Url::fromUserInput($url, ['absolute' => TRUE])->toString();
              }
              catch (\InvalidArgumentException $e) {
                \Drupal::logger('ucsf_applenews')->error('throwing out HTML containing invalid src attribute ' . $doc->saveHTML($element));
                continue;
              }
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
            $component->setLayout(_ucsf_applenews_photo_component_layout());
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
                    $component->setLayout(
                      _ucsf_applenews_photo_component_layout());
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
                  $component->setLayout(
                    _ucsf_applenews_video_component_layout());
                }
              }
            }
          }
          break;

      }

      $inline_component = new \stdClass();
      $inline_component->element = $element;
      $inline_component->component = $component;
      $inline_component->children = [];
      $inline_components[spl_object_hash($element)] = $inline_component;

    }

    // Convert $inline_components into a tree.
    $tree = [];
    foreach ($inline_components as $key => $inline_component) {
      $ancestor = $inline_component->element->parentNode;
      while ($ancestor) {
        $ancestor_key = spl_object_hash($ancestor);
        if (isset($inline_components[$ancestor_key])) {
          $ancestor_component = $inline_components[$ancestor_key];
          $ancestor_component->children[$key] = $inline_component;
          continue 2;
        }
        $ancestor = $ancestor->parentNode;
      }
      $tree[$key] = $inline_component;
    }

    // Replace original element with a token element.
    $tokenize = function ($inline_component) use (&$tokenize, $doc) {
      foreach ($inline_component->children as $child) {
        $tokenize($child);
      }
      /** @var \ChapterThree\AppleNewsAPI\Document\Components\Component $component */
      $component = $inline_component->component;
      /** @var \DOMElement $element */
      $element = $inline_component->element;
      if ($component) {

        $key = spl_object_hash($element);
        $token = $doc->createElement('applenews_component');
        $token->setAttribute('id', $key);

        // Replace with token, making sure the token is at the root level of
        // the value html.
        if ($element->parentNode->tagName == 'body') {
          $element->parentNode->replaceChild($token, $element);
        }
        // Nested, insert token after top level ancestor.
        else {
          if (!$ancestor = $element->parentNode) {
            throw new \Exception(
              'Could not determine parent node for element ' .
              $doc->saveHTML($element));
          }
          while ($ancestor->parentNode &&
            $ancestor->parentNode->tagName != 'body'
          ) {
            $ancestor = $ancestor->parentNode;
          }
          if (!$ancestor->parentNode) {
            throw new \Exception(
              'Could not determine parent node for element ' .
              $doc->saveHTML($element));
          }
          // Insert after ancestor.
          if ($ancestor->nextSibling) {
            $ancestor->parentNode->insertBefore(
              $token, $ancestor->nextSibling);
            $element->parentNode->removeChild($element);
          }
          // Append to root element.
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
    };
    array_map($tokenize, $tree);

    // Remove empty elements.
    $xp_query = '/html/body/*';
    /** @var \DOMElement $element */
    foreach ($xp->query($xp_query) as $element) {
      if ($this->empty($element)) {
        $element->parentNode->removeChild($element);
      }
    }

    // Generate body components and assemble all components into return value.
    $xp_query = '/html/body/*';
    $current_body = '';
    $append_body = function () use (&$current_body, &$components, &$data) {
      if (!empty($current_body)) {
        $component = new Body($current_body);
        $component->setTextStyle(_ucsf_applenews_base_component_text_style());
        $component->setFormat($data['component_data']['format']);
        $component->setLayout($this->getComponentLayout($data['component_layout']));
        $components[] = $component;
      }
      $current_body = '';
    };
    /** @var \DOMElement $element */
    foreach ($xp->query($xp_query) as $element) {
      if ($element->tagName == 'applenews_component') {
        $append_body();
        $id = $element->getAttribute('id');
        $components[] = $inline_components[$id]->component;
      }
      else {
        $current_body .= $this->htmlValue($doc->saveHTML($element));
      }
    }
    $append_body();

    libxml_clear_errors();
    libxml_use_internal_errors($libxml_previous_state);

    return $components;
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
    return $this->trim(
      html_entity_decode(
        strip_tags($str, $allowed_elements)
      )
    );
  }

  /**
   * Strips tags and html entities, trims whitespace.
   */
  protected function textValue($str) {
    return $this->trim(
      html_entity_decode(
        strip_tags($str)
      )
    );
  }

  /**
   * Trims whitespace.
   */
  protected function trim($str) {
    return trim($str, " \t\n\r\0\x0B\xC2\xA0");
  }

  /**
   * Detects empty DOM elements.
   */
  protected function empty(\DOMNode $element) {
    // This element can be empty.
    if ($element instanceof \DOMElement &&
      !in_array($element->tagName, $this->elementsNotEmpty)
    ) {
      return FALSE;
    }
    if ($element->childNodes) {
      /** @var \DOMElement $child */
      foreach ($element->childNodes as $child) {
        // Contains text content.
        if ($child instanceof \DOMText && $this->trim($child->textContent) != ''
        ) {
          return FALSE;
        }
        // Recurse.
        elseif (!$this->empty($child)) {
          return FALSE;
        }
      }
    }
    return TRUE;
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
