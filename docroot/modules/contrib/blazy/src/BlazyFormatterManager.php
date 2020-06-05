<?php

namespace Drupal\blazy;

/**
 * Provides common field formatter-related methods: Blazy, Slick.
 */
class BlazyFormatterManager extends BlazyManager {

  /**
   * The first image item found.
   *
   * @var object
   */
  protected $firstItem = NULL;

  /**
   * Returns the field formatter settings inherited by child elements.
   *
   * @param array $build
   *   The array containing: settings, or potential optionset for extensions.
   * @param object $items
   *   The items to prepare settings for.
   */
  public function buildSettings(array &$build, $items) {
    $settings       = &$build['settings'];
    $settings      += $this->getCommonSettings();
    $count          = $items->count();
    $field          = $items->getFieldDefinition();
    $entity         = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $entity_id      = $entity->id();
    $bundle         = $entity->bundle();
    $field_name     = $field->getName();
    $field_type     = $field->getType();
    $field_clean    = str_replace("field_", '', $field_name);
    $target_type    = $field->getFieldStorageDefinition()->getSetting('target_type');
    $view_mode      = empty($settings['current_view_mode']) ? '_custom' : $settings['current_view_mode'];
    $namespace      = $settings['namespace'] = empty($settings['namespace']) ? 'blazy' : $settings['namespace'];
    $id             = isset($settings['id']) ? $settings['id'] : '';
    $id             = Blazy::getHtmlId("{$namespace}-{$entity_type_id}-{$entity_id}-{$field_clean}-{$view_mode}", $id);
    $switch         = empty($settings['media_switch']) ? '' : $settings['media_switch'];
    $internal_path  = $absolute_path = NULL;

    // Deals with UndefinedLinkTemplateException such as paragraphs type.
    // @see #2596385, or fetch the host entity.
    if (!$entity->isNew() && method_exists($entity, 'hasLinkTemplate')) {
      if ($entity->hasLinkTemplate('canonical')) {
        $url = $entity->toUrl();
        $internal_path = $url->getInternalPath();
        $absolute_path = $url->setAbsolute()->toString();
      }
    }

    $settings['bundle']         = $bundle;
    $settings['cache_metadata'] = ['keys' => [$id, $count]];
    $settings['cache_tags'][]   = $entity_type_id . ':' . $entity_id;
    $settings['content_url']    = $settings['absolute_path'] = $absolute_path;
    $settings['count']          = $count;
    $settings['entity_id']      = $entity_id;
    $settings['entity_type_id'] = $entity_type_id;
    $settings['field_type']     = $field_type;
    $settings['field_name']     = $field_name;
    $settings['id']             = $id;
    $settings['internal_path']  = $internal_path;
    $settings['lightbox']       = ($switch && in_array($switch, $this->getLightboxes())) ? $switch : FALSE;
    $settings['target_type']    = $target_type;

    // Bail out if vanilla is requested.
    if (!empty($settings['vanilla'])) {
      $settings = array_filter($settings);
      return;
    }

    $settings['breakpoints'] = isset($settings['breakpoints']) && empty($settings['responsive_image_style']) ? $settings['breakpoints'] : [];
    if (!empty($settings['breakpoints'])) {
      $this->cleanUpBreakpoints($settings);
    }

    $settings['caption']    = empty($settings['caption']) ? [] : array_filter($settings['caption']);
    $settings['background'] = empty($settings['responsive_image_style']) && !empty($settings['background']);
    $settings['resimage']   = function_exists('responsive_image_get_image_dimensions') && !empty($settings['responsive_image']) && !empty($settings['responsive_image_style']);
    $settings['resimage']   = $settings['resimage'] ? $this->entityLoad($settings['responsive_image_style'], 'responsive_image_style') : FALSE;
    $settings['blazy']      = !empty($settings['blazy']) || $settings['background'] || !empty($settings['resimage']) || !empty($settings['breakpoints']);
    $settings['lazy']       = $settings['blazy'] ? 'blazy' : (isset($settings['lazy']) ? $settings['lazy'] : '');

    // Aspect ratio isn't working with Responsive image, yet.
    // However allows custom work to get going with an enforced.
    $ratio = FALSE;
    if (!empty($settings['ratio'])) {
      $ratio = empty($settings['responsive_image_style']);
      if ($settings['ratio'] == 'enforced' || $settings['background']) {
        $ratio = TRUE;
      }
    }

    $settings['ratio'] = $ratio ? $settings['ratio'] : FALSE;

    // Sets dimensions once, if cropped, to reduce costs with ton of images.
    // This is less expensive than re-defining dimensions per image.
    if (!empty($settings['image_style']) && empty($settings['resimage'])) {
      if ($field_type == 'image' && $item = $items[0]) {
        $settings['item'] = $item;
        $settings['uri']  = ($file = $item->entity) && empty($item->uri) ? $file->getFileUri() : $item->uri;
      }

      if (!empty($settings['uri'])) {
        $this->setDimensionsOnce($settings);
      }
    }

    $this->getModuleHandler()->alter($namespace . '_settings', $build, $items);
  }

  /**
   * Backported few cross-module methods to minimize mismatched branch issues.
   */
  public function preBuildElements(array &$build, $items, array $entities = []) {
    $this->buildSettings($build, $items);
  }

  /**
   * Backported few cross-module methods to minimize mismatched branch issues.
   */
  public function postBuildElements(array &$build, $items, array $entities = []) {
    // Rebuild the first item to build colorbox/zoom-like gallery.
    $build['settings']['first_item'] = $this->firstItem;
  }

}
