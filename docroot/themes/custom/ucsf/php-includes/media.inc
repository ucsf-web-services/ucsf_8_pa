<?php

function ucsf_preprocess_entity_embed_container(&$variables) {

  /** @var  $entity Drupal\media\Entity\Media **/
  $entity = $variables['element']['#entity'];

  if ($entity instanceof Drupal\media\Entity\Media) {
    $media_type = $entity->bundle();
    $variables['media_type'] = $media_type;
  }
}
