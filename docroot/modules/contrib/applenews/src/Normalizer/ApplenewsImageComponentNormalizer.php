<?php

namespace Drupal\applenews\Normalizer;

use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Normalizer for "image" type com.
 */
class ApplenewsImageComponentNormalizer extends ApplenewsComponentNormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $componentType = 'image';

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    try {
      $component_class = $this->getComponentClass($data['id']);
      $entity = $context['entity'];

      $field_name = $data['component_data']['URL']['field_name'];
      $context['field_property'] = $data['component_data']['URL']['field_property'];
      $field_value = $this->serializer->normalize($entity->get($field_name), $format, $context);
      $component = new $component_class($this->getUrl($field_value));

      $field_name = $data['component_data']['caption']['field_name'];
      $context['field_property'] = $data['component_data']['caption']['field_property'];
      $text = $this->serializer->normalize($entity->get($field_name), $format, $context);
      $component->setCaption($text);
      $component->setLayout($this->getComponentLayout($data['component_layout']));

      return $component;
    }
    catch (MissingDataException $e) {
      // Without a proper file URL, there is nothing to return.
    }
  }

  /**
   * Gets image URL from a normalized file field.
   *
   * @param mixed $file
   *   File array.
   *
   * @return string
   *   String URL of the given normalized file field, otherwise NULL.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   When we could not find a valid image URL from the given normalized file
   *   field.
   */
  protected function getUrl($file) {
    if (!is_array($file) || !isset($file['uri'][0]['value'])) {
      throw new MissingDataException('Given normalized file field is not recognizable as a file field.');
    }
    $url = $this->fileCreateUrl($file['uri'][0]['value']);
    if ($url === FALSE) {
      throw new MissingDataException('Could not convert the normalized file field value to a file URL.');
    }
    return $url;
  }

  /**
   * Wraps file_create_url for testing purposes.
   *
   * @param string $uri
   *   Pass through $uri param for file_create_url.
   *
   * @return string|bool
   *   Return value from file_create_url.
   */
  public function fileCreateUrl($uri) {
    return file_create_url($uri);
  }

}
