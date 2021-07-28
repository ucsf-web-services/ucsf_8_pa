<?php

namespace Drupal\acquia_contenthub\Normalizer;

/**
 * Wrapper for the normalizer.
 *
 * @package Drupal\acquia_contenthub\Normalizer
 */
class NormalizerWrapper extends NormalizerBase {

  /**
   * Normalizer for content entity cdfs.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer
   */
  protected $contentEntityCdfNormalizer;

  /**
   * NormalizerWrapper constructor.
   *
   * @param \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer $contentEntityCdfNormalizer
   *   CDF Normalizer.
   */
  public function __construct(ContentEntityCdfNormalizer $contentEntityCdfNormalizer) {
    $this->contentEntityCdfNormalizer = $contentEntityCdfNormalizer;
  }

  /**
   * Call for content entity cdf normalizer.
   *
   * @param mixed $name
   *   Name for the cdf normalizer.
   * @param mixed $arguments
   *   Arguments for the cdf normalizer.
   *
   * @return mixed
   *   Returns the normalized name for the entity.
   */
  public function __call($name, $arguments) {
    return $this->contentEntityCdfNormalizer->$name(... $arguments);
  }

  /**
   * Checks normalization support.
   *
   * @param mixed $data
   *   Data for the normalization.
   * @param mixed|null $format
   *   Format for the normalization.
   *
   * @return mixed
   *   Whether this supports normalization.
   */
  public function supportsNormalization($data, $format = NULL) {
    return $this->contentEntityCdfNormalizer->supportsNormalization($data, $format);
  }

  /**
   * Checks for denormalization support.
   *
   * @param mixed $data
   *   Data for denormalization.
   * @param mixed $type
   *   Type for denormalization.
   * @param mixed|null $format
   *   Format for denormalization.
   *
   * @return mixed
   *   Whether this supports denormalization.
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return $this->contentEntityCdfNormalizer->supportsDenormalization($data, $type, $format);
  }

  /**
   * Denormalizes entities.
   *
   * @param mixed $data
   *   Data for denormalization.
   * @param mixed $class
   *   Class to denormalize.
   * @param mixed|null $format
   *   Format for denormalization.
   * @param array $context
   *   Context for denormalization.
   *
   * @return array|\Drupal\Core\Entity\ContentEntityInterface|null
   *   Content Hub Entity denormalized.
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    return $this->contentEntityCdfNormalizer->denormalize($data, $class, $format, $context);
  }

  /**
   * Normalizes entities.
   *
   * @param mixed $object
   *   Object to normalize.
   * @param mixed|null $format
   *   Format to normalize.
   * @param array $context
   *   Context for normalization.
   *
   * @return \Acquia\ContentHubClient\Entity[][]|array|bool|float|int|string|null
   *   Content Hub Entity normalized.
   */
  public function normalize($object, $format = NULL, array $context = []) {
    return $this->contentEntityCdfNormalizer->normalize($object, $format, $context);
  }

}
