<?php

namespace Drupal\acquia_contenthub\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;

/**
 * Encodes Content Hub CDF data in JSON.
 *
 * Simply respond to the acquia_contenthub_cdf format requests using the JSON
 * encoder.
 */
class ContentHubCdfEncoder extends SymfonyJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected $format = 'acquia_contenthub_cdf';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {
    return $format == $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return $format == $this->format;
  }

}
