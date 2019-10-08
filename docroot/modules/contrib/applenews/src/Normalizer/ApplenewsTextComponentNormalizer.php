<?php

namespace Drupal\applenews\Normalizer;

/**
 * Class ApplenewsTextComponentNormalizer.
 *
 * @package Drupal\applenews\Normalizer
 */
class ApplenewsTextComponentNormalizer extends ApplenewsComponentNormalizerBase {

  /**
   * HTML tags allowed by Apple News JSON.
   *
   * @see https://developer.apple.com/documentation/apple_news/apple_news_format/components/using_html_with_apple_news_format
   */
  const ALLOWED_HTML_ELEMENTS = '<p><strong><b><em><i><a><ul><ol><li><br><sub><sup><del><s><pre><code><samp><footer><aside><blockquote>';

  /**
   * Component type.
   *
   * @var string
   */
  protected $componentType = 'text';

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    $component_class = $this->getComponentClass($data['id']);
    $entity = $context['entity'];

    $field_name = $data['component_data']['text']['field_name'];
    $context['field_property'] = $data['component_data']['text']['field_property'];
    $text = $this->serializer->normalize($entity->get($field_name), $format, $context);
    if ($data['component_data']['format'] == 'html') {
      $text = strip_tags($text, self::ALLOWED_HTML_ELEMENTS);
    }
    $component = new $component_class($text);

    $component->setFormat($data['component_data']['format']);

    $component->setLayout($this->getComponentLayout($data['component_layout']));

    return $component;
  }

}
