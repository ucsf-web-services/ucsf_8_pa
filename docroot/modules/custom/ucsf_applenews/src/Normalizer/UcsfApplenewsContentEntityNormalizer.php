<?php

namespace Drupal\ucsf_applenews\Normalizer;

use Drupal\applenews\Normalizer\ApplenewsContentEntityNormalizer;
use ChapterThree\AppleNewsAPI\Document\Layouts\Layout;
use ChapterThree\AppleNewsAPI\Document;
use ChapterThree\AppleNewsAPI\Document\Metadata;
use Drupal\Core\Language\LanguageInterface;
use ChapterThree\AppleNewsAPI\Document\Components\Component;
use Drupal\node\Entity\Node;

/**
 * Override ApplenewsContentEntityNormalizer for UCSF exports.
 */
class UcsfApplenewsContentEntityNormalizer extends ApplenewsContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    // @todo check cache
    $template = $this->entityTypeManager->getStorage('applenews_template')
      ->load($context['template_id']);
    $layout = new Layout($template->columns, $template->width);
    $langcode = $data->language()->getId();
    // If language is not specified , fallback to site default.
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    }
    $layout
      ->setMargin($template->margin)
      ->setGutter($template->gutter);
    $document = new Document($data->uuid(), $data->getTitle(), $langcode, $layout);

    // Customized to add metadata.
    $metadata = new Metadata();
    if ($data instanceof Node) {
      $info = system_get_info('module', 'applenews');
      $version = $info['core'] ?? '';
      if (!empty($info['version'])) {
        $version .= '-' . $info['version'];
      }
      $metadata
        ->setCanonicalURL($data->toUrl()->setAbsolute()->toString())
        ->setDateCreated(date('c', $data->getCreatedTime()))
        ->setDateModified(date('c', $data->getChangedTime()))
        ->setGeneratorIdentifier('DrupalAppleNews')
        ->setGeneratorName('Apple News Drupal Module')
        ->setGeneratorIdentifier($version);
    }
    $document->setMetadata($metadata);
    // End customization.

    $context['entity'] = $data;
    foreach ($template->getComponents() as $component) {
      $normalized = $this->serializer->normalize($component, $format, $context);
      if (!$normalized) {
        continue;
      }
      elseif ($normalized instanceof Component) {
        $normalized = [$normalized];
      }

      foreach ($normalized as $normalized_component) {
        if ($normalized_component instanceof Component) {
          $document->addComponent($normalized_component);
        }
      }
    }

    // @todo: Load only default and used custom styles.
    $text_styles = $this->entityTypeManager->getStorage('applenews_text_style')
      ->loadMultiple();
    foreach ($text_styles as $id => $text_style) {
      /** @var \Drupal\applenews\Entity\ApplenewsTextStyle $text_style */
      $document->addTextStyle($text_style->id(), $text_style->toObject());
    }
    return $document->jsonSerialize();
  }

}
