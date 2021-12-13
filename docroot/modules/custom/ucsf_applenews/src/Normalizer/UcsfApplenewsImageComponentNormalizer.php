<?php

namespace Drupal\ucsf_applenews\Normalizer;

use ChapterThree\AppleNewsAPI\Document\Components\Photo;
use Drupal\applenews\Normalizer\ApplenewsImageComponentNormalizer;

/**
 * Override ApplenewsImageComponentNormalizer for UCSF exports.
 */
class UcsfApplenewsImageComponentNormalizer extends ApplenewsImageComponentNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {

    if ($data['id'] == 'default_image:photo' &&
      isset($data['component_data']['URL']['field_name']) &&
      $data['component_data']['URL']['field_name'] == 'field_banner_image'
    ) {
      return $this->normalizeBannerImage($data, $format, $context);
    }

    return parent::normalize($data, $format, $context);
  }

  /**
   * Banner image.
   */
  protected function normalizeBannerImage($data, $format = NULL, array $context = []) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $context['entity'];

    $field_name = $data['component_data']['URL']['field_name'];
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $field */
    $field = $entity->get($field_name);
    /** @var \Drupal\media\Entity\Media $media */
    foreach ($field->referencedEntities() as $media) {
      break;
    }
    if (empty($media)) {
      return NULL;
    }
    /** @var \Drupal\file\Entity\File $file */
    $file = $media->get('field_media_image')->entity;
    $component = new Photo($file->createFileUrl(FALSE));

    $field_name = $data['component_data']['caption']['field_name'];
    $context['field_property'] = $data['component_data']['caption']['field_property'];
    $text = $this->serializer->normalize($entity->get($field_name), $format, $context);
    $component->setCaption($text);

    $component->setLayout($this->getComponentLayout($data['component_layout']));

    return $component;
  }

}
