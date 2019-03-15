<?php

namespace Drupal\ucsf_applenews\Derivative;

use Drupal\applenews\Derivative\ApplenewsDefaultComponentTextTypeDeriver;

/**
 * Creates custom ucsf_applenews Apple News plugins for entity references.
 */
class UcsfApplenewsEntityreferenceComponentTypeDeriver extends ApplenewsDefaultComponentTextTypeDeriver {

  /**
   * {@inheritdoc}
   */
  public function getComponentClasses() {
    return [
      'ucsf_body' => [
        'component_class' => 'ChapterThree\AppleNewsAPI\Document\Components\Body',
        'label' => 'UCSF Body',
        'description' => 'A chunk of text.',
      ],
    ];
  }

}
