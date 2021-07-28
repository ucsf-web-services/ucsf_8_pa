<?php

namespace Drupal\applenews\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Base class for all Apple News normalizers.
 */
abstract class ApplenewsNormalizerBase implements NormalizerInterface, SerializerAwareInterface {

  use SerializerAwareTrait;

  /**
   * Name of the format that this normalizer deals with.
   *
   * @var string
   */
  protected $format = 'applenews';

}
