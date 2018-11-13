<?php

namespace Drupal\acquia_contenthub\Normalizer;

class NormalizerWrapper extends NormalizerBase {

  protected $contentEntityCdfNormalizer;

  public function __construct(ContentEntityCdfNormalizer $contentEntityCdfNormalizer) {
    $this->contentEntityCdfNormalizer = $contentEntityCdfNormalizer;
  }

  public function __call($name, $arguments) {
    return $this->contentEntityCdfNormalizer->$name(... $arguments);
  }

  public function supportsNormalization($data, $format = NULL) {
    return $this->contentEntityCdfNormalizer->supportsNormalization($data, $format);
  }

  public function supportsDenormalization($data, $type, $format = NULL) {
    return $this->contentEntityCdfNormalizer->supportsDenormalization($data, $type, $format);
  }

  public function denormalize($data, $class, $format = null, array $context = array()) {
    return $this->contentEntityCdfNormalizer->denormalize($data, $class, $format, $context);
  }

  public function normalize($object, $format = NULL, array $context = []) {
    return $this->contentEntityCdfNormalizer->normalize($object, $format, $context);
  }

}