<?php

namespace Drupal\acquia_contenthub\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface for View Modes Extractor.
 */
interface ContentEntityViewModesExtractorInterface {

  /**
   * Normalizes an object into a set of arrays/scalars.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $object
   *   Object to normalize. Due to the constraints of the class, we know that
   *   the object will be of the ContentEntityInterface type.
   *
   * @return array|null
   *   Returns the extracted view modes or null if the given object is not
   *   supported or if it was not configured in the Content Hub settings.
   */
  public function getRenderedViewModes(ContentEntityInterface $object);

}
