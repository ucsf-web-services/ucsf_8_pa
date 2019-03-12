<?php

namespace Drupal\ucsf_applenews\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Body;
use ChapterThree\AppleNewsAPI\Document\Components\Byline;
use ChapterThree\AppleNewsAPI\Document\Components\Photo;
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

    return parent::normalize($data, $format, $context);
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

    $text = strip_tags($text, self::ALLOWED_HTML_ELEMENTS);
    $components['body'] = new Body($text);

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

}
