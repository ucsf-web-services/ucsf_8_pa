<?php

namespace Drupal\blazy;

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Serialization\Json;
use Drupal\image\Entity\ImageStyle;

/**
 * Implements BlazyInterface.
 */
class Blazy implements BlazyInterface {

  /**
   * Defines constant placeholder Data URI image.
   */
  const PLACEHOLDER = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  /**
   * The blazy HTML ID.
   *
   * @var int
   */
  private static $blazyId;

  /**
   * Prepares variables for blazy.html.twig templates.
   */
  public static function buildAttributes(&$variables) {
    $element = $variables['element'];
    foreach (BlazyDefault::themeProperties() as $key) {
      $variables[$key] = isset($element["#$key"]) ? $element["#$key"] : [];
    }

    // Provides optional attributes.
    foreach (BlazyDefault::themeAttributes() as $key) {
      $key = $key . '_attributes';
      $variables[$key] = empty($element["#$key"]) ? [] : new Attribute($element["#$key"]);
    }

    // Provides sensible default html settings to shutup notices when lacking.
    $settings = &$variables['settings'];
    $settings += BlazyDefault::itemSettings();
    $settings['ratio'] = empty($settings['ratio']) ? '' : str_replace(':', '', $settings['ratio']);
    $settings['use_media'] = $settings['embed_url'] && in_array($settings['type'], ['audio', 'video']);

    self::buildUrl($settings, $variables['item']);

    // Do not proceed if no URI is provided.
    if (empty($settings['uri'])) {
      return;
    }

    // Check whether we have responsive image (no svg), or Blazy one.
    $settings['extension'] = pathinfo($settings['uri'])['extension'];
    if (!empty($settings['responsive_image_style_id']) && $settings['extension'] != 'svg') {
      self::buildResponsiveImage($variables);
    }
    else {
      self::buildImage($variables);
    }

    // Prepares a media player, and allows a tiny video preview without iframe.
    if ($settings['use_media'] && empty($settings['_noiframe'])) {
      self::buildIframeAttributes($variables);
    }

    // Image is optional for Video, and Blazy CSS background images.
    if ($variables['image']) {
      self::imageAttributes($variables);
    }

    self::thumbnailAttributes($variables);
  }

  /**
   * Modifies variables for Responsive image.
   */
  public static function buildResponsiveImage(array &$variables) {
    $image = &$variables['image'];
    $settings = &$variables['settings'];

    $image['#type'] = 'responsive_image';
    $image['#responsive_image_style_id'] = $settings['responsive_image_style_id'];
    $image['#uri'] = $settings['uri'];

    // Disable aspect ratio which is not yet supported due to complexity.
    $settings['ratio'] = FALSE;
  }

  /**
   * Modifies variables for regular image.
   */
  public static function buildImage(array &$variables) {
    $image = &$variables['image'];
    $settings = &$variables['settings'];
    $attributes = &$variables['attributes'];
    $image_attributes = &$variables['item_attributes'];

    // Supports non-lazyloaded image.
    $image['#theme'] = 'image';

    // Supports either lazy loaded image, or not, which is overriden later.
    // This allows Blazy to be used for RSS by disabling $settings['lazy']
    // and $settings['view_mode'] = 'rss' via hook_blazy_settings_alter()
    // since image_url is not transformed relative.
    $image['#uri'] = empty($settings['image_url']) ? $settings['uri'] : $settings['image_url'];

    // Aspect ratio to fix layout reflow with lazyloaded images responsively.
    // This is outside 'lazy' to allow non-lazyloaded iframes use this too.
    if (!empty($settings['width'])) {
      if (!empty($settings['ratio']) && in_array($settings['ratio'], ['enforced', 'fluid'])) {
        $padding_bottom = empty($settings['padding_bottom']) ? round((($settings['height'] / $settings['width']) * 100), 2) : $settings['padding_bottom'];
        $attributes['style'] = 'padding-bottom: ' . $padding_bottom . '%';
        $settings['_breakpoint_ratio'] = $settings['ratio'];
      }
    }

    // Supports lazyloaded image.
    if (!empty($settings['lazy'])) {
      $image['#uri'] = static::PLACEHOLDER;

      // Attach data attributes to either IMG tag, or DIV container.
      if (empty($settings['background']) || empty($settings['blazy'])) {
        self::buildBreakpointAttributes($image_attributes, $settings);
      }

      // Supports both Slick and Blazy CSS background lazyloading.
      if (!empty($settings['background'])) {
        self::buildBreakpointAttributes($attributes, $settings);
        $attributes['class'][] = 'media--background';

        // Blazy doesn't need IMG to lazyload CSS background. Slick does.
        if (!empty($settings['blazy'])) {
          $image = [];
        }
      }

      // Multi-breakpoint aspect ratio only applies if lazyloaded.
      if (!empty($settings['blazy_data']['dimensions'])) {
        $attributes['data-dimensions'] = Json::encode($settings['blazy_data']['dimensions']);
      }
    }
  }

  /**
   * Provides container attributes for .blazy container: .field, .view, etc.
   */
  public static function containerAttributes(array &$attributes, array $settings = []) {
    // Provides the main container attributes.
    $classes = empty($attributes['class']) ? [] : $attributes['class'];
    $attributes['data-blazy'] = empty($settings['blazy_data']) ? '' : Json::encode($settings['blazy_data']);

    // Provides data-LIGHTBOX-gallery to not conflict with original modules.
    if (!empty($settings['media_switch'])) {
      $switch = str_replace('_', '-', $settings['media_switch']);
      $attributes['data-' . $switch . '-gallery'] = TRUE;
    }

    // Provides contextual classes relevant to the container: .field, or .view.
    if (isset($settings['namespace']) && $settings['namespace'] == 'blazy') {
      foreach (['field', 'view'] as $key) {
        if (!empty($settings[$key . '_name'])) {
          $classes[] = 'blazy--' . $key . ' blazy--' . str_replace('_', '-', $settings[$key . '_name']);
        }
      }
    }

    $attributes['class'] = array_merge(['blazy'], $classes);
  }

  /**
   * Modifies $variables to provide optional (Responsive) image attributes.
   */
  public static function imageAttributes(array &$variables) {
    $item = $variables['item'];
    $image = &$variables['image'];
    $settings = $variables['settings'];
    $attributes = &$variables['item_attributes'];

    // Respects hand-coded image attributes.
    if ($item) {
      if (!isset($attributes['alt'])) {
        $attributes['alt'] = isset($item->alt) ? $item->alt : NULL;
      }

      // Do not output an empty 'title' attribute.
      if (isset($item->title) && (mb_strlen($item->title) != 0)) {
        $attributes['title'] = $item->title;
      }
    }

    // Only output dimensions for non-svg. Respects hand-coded image attributes.
    // Do not pass it to $attributes to also respect both (Responsive) image.
    // Responsive images with height and width save a lot of calls to
    // image.factory service for every image and breakpoint in
    // _responsive_image_build_source_attributes(). Very necessary for
    // external file system like Amazon S3.
    if (!isset($attributes['width']) && $settings['extension'] != 'svg') {
      $image['#width'] = $settings['width'];
      $image['#height'] = $settings['height'];
    }

    $attributes['class'][] = 'media__image media__element';
    $image['#attributes'] = $attributes;
  }

  /**
   * Modifies $variables to provide optional thumbnail attributes.
   *
   * With CSS background, IMG may be empty, add thumbnail to the container.
   * Supports unique thumbnail different from main image, such as logo for
   * thumbnail and main image for company profile.
   */
  public static function thumbnailAttributes(array &$variables) {
    $settings = $variables['settings'];
    $attributes = &$variables['attributes'];

    if (!empty($settings['thumbnail_uri'])) {
      $attributes['data-thumb'] = file_url_transform_relative(file_create_url($settings['thumbnail_uri']));
    }
    elseif (!empty($settings['thumbnail_style'])) {
      $attributes['data-thumb'] = ImageStyle::load($settings['thumbnail_style'])->buildUrl($settings['uri']);
    }
  }

  /**
   * Modifies variables for iframes.
   */
  public static function buildIframeAttributes(&$variables) {
    // Prepares a media player, and allows a tiny video preview without iframe.
    // image : If iframe switch disabled, fallback to iframe, remove image.
    // player: If no colorbox/photobox, it is an image to iframe switcher.
    // data- : Gets consistent with colorbox to share JS manipulation.
    $settings           = &$variables['settings'];
    $variables['image'] = empty($settings['media_switch']) ? [] : $variables['image'];
    $settings['player'] = empty($settings['lightbox']) && $settings['media_switch'] != 'content';
    $iframe['data-src'] = $settings['embed_url'];
    $iframe['src']      = empty($settings['iframe_lazy']) ? $settings['embed_url'] : 'about:blank';

    // Only lazyload if media switcher is empty, but iframe lazy enabled.
    if (!empty($settings['iframe_lazy']) && empty($settings['media_switch'])) {
      $iframe['class'][] = 'b-lazy';
    }

    // Prevents broken iframe when aspect ratio is empty.
    if (empty($settings['ratio']) && !empty($settings['width'])) {
      $iframe['width']  = $settings['width'];
      $iframe['height'] = $settings['height'];
    }

    // Pass iframe attributes to template.
    $settings['autoplay_url'] = empty($settings['autoplay_url']) ? $settings['embed_url'] : $settings['autoplay_url'];
    $variables['iframe_attributes'] = new Attribute($iframe);

    // Iframe is removed on lazyloaded, puts data at non-removable storage.
    $variables['attributes']['data-media'] = Json::encode(['type' => $settings['type'], 'scheme' => $settings['scheme']]);
  }

  /**
   * Provides re-usable breakpoint data-attributes.
   *
   * $settings['breakpoints'] must contain: xs, sm, md, lg breakpoints with
   * the expected keys: width, image_style.
   *
   * @see self::buildAttributes()
   */
  public static function buildBreakpointAttributes(array &$attributes = [], array &$settings = []) {
    // Defines attributes, builtin, or supported lazyload such as Slick.
    $attributes['class'][] = $settings['lazy_class'];
    $attributes['data-' . $settings['lazy_attribute']] = $settings['image_url'];

    // Only provide multi-serving image URLs if breakpoints are provided.
    if (empty($settings['breakpoints'])) {
      return;
    }

    $srcset = $json = [];
    foreach ($settings['breakpoints'] as $key => $breakpoint) {
      if (empty($breakpoint['image_style']) || empty($breakpoint['width'])) {
        continue;
      }

      if ($style = ImageStyle::load($breakpoint['image_style'])) {
        $url = file_url_transform_relative($style->buildUrl($settings['uri']));

        // Supports multi-breakpoint aspect ratio with irregular sizes.
        // Yet, only provide individual dimensions if not already set.
        // @see Drupal\blazy\BlazyManager::setDimensionsOnce().
        if (!empty($settings['_breakpoint_ratio']) && empty($settings['blazy_data']['dimensions'])) {
          $dimensions = [
            'width'  => $settings['width'],
            'height' => $settings['height'],
          ];

          $style->transformDimensions($dimensions, $settings['uri']);
          if ($width = self::widthFromDescriptors($breakpoint['width'])) {
            $json[$width] = round((($dimensions['height'] / $dimensions['width']) * 100), 2);
          }
        }

        $settings['breakpoints'][$key]['url'] = $url;

        // @todo: Recheck library if multi-styled BG is still supported anyway.
        // Confirmed: still working with GridStack multi-image-style per item.
        if (!empty($settings['background'])) {
          $attributes['data-src-' . $key] = $url;
        }
        elseif (!empty($breakpoint['width'])) {
          $width = trim($breakpoint['width']);
          $width = is_numeric($width) ? $width . 'w' : $width;
          $srcset[] = $url . ' ' . $width;
        }
      }
    }

    if ($srcset) {
      $settings['srcset'] = implode(', ', $srcset);

      $attributes['srcset'] = '';
      $attributes['data-srcset'] = $settings['srcset'];
      $attributes['sizes'] = '100w';

      if (!empty($settings['sizes'])) {
        $attributes['sizes'] = trim($settings['sizes']);
        unset($attributes['height'], $attributes['width']);
      }
    }

    if ($json) {
      $settings['blazy_data']['dimensions'] = $json;
    }
  }

  /**
   * Builds URLs, cache tags, and dimensions for individual image.
   */
  public static function buildUrl(array &$settings = [], $item = NULL) {
    // Blazy already sets URI, yet set fallback for direct theme_blazy() call.
    if (empty($settings['uri']) && $item) {
      $settings['uri'] = ($entity = $item->entity) && empty($item->uri) ? $entity->getFileUri() : $item->uri;
    }

    if (empty($settings['uri'])) {
      return;
    }

    // Lazyloaded elements expect image URL, not URI.
    if (empty($settings['image_url'])) {
      $settings['image_url'] = file_url_transform_relative(file_create_url($settings['uri']));
    }

    // Sets dimensions.
    // VEF without image style, or image style with crop, may already set these.
    if (empty($settings['width'])) {
      $settings['width']  = $item && isset($item->width) ? $item->width : NULL;
      $settings['height'] = $item && isset($item->height) ? $item->height : NULL;
    }

    // Image style modifier can be multi-style images such as GridStack.
    if (!empty($settings['image_style']) && ($style = ImageStyle::load($settings['image_style']))) {
      // Image URLs, as opposed to URIs, are expected by lazyloaded images.
      $settings['image_url']  = file_url_transform_relative($style->buildUrl($settings['uri']));
      $settings['cache_tags'] = $style->getCacheTags();

      // Only re-calculate dimensions if not cropped, nor already set.
      if (empty($settings['_dimensions'])) {
        $style->transformDimensions($settings, $settings['uri']);
      }
    }
  }

  /**
   * Gets the numeric "width" part from a descriptor.
   */
  public static function widthFromDescriptors($descriptor = '') {
    // Dynamic multi-serving aspect ratio with backward compatibility.
    $descriptor = trim($descriptor);
    if (is_numeric($descriptor)) {
      return (int) $descriptor;
    }

    // Cleanup w descriptor to fetch numerical width for JS aspect ratio.
    $width = strpos($descriptor, "w") !== FALSE ? str_replace('w', '', $descriptor) : $descriptor;

    // If both w and x descriptors are provided.
    if (strpos($descriptor, " ") !== FALSE) {
      // If the position is expected: 640w 2x.
      list($width, $px) = array_pad(array_map('trim', explode(" ", $width, 2)), 2, NULL);

      // If the position is reversed: 2x 640w.
      if (is_numeric($px) && strpos($width, "x") !== FALSE) {
        $width = $px;
      }
    }

    return is_numeric($width) ? (int) $width : FALSE;
  }

  /**
   * Overrides variables for responsive-image.html.twig templates.
   */
  public static function preprocessResponsiveImage(&$variables) {
    $image = &$variables['img_element'];
    $attributes = &$variables['attributes'];

    // Prepare all <picture> [data-srcset] attributes on <source> elements.
    if (!$variables['output_image_tag']) {
      /** @var \Drupal\Core\Template\Attribute $source */
      if (isset($variables['sources']) && is_array($variables['sources'])) {
        foreach ($variables['sources'] as &$source) {
          $source->setAttribute('data-srcset', $source['srcset']->value());
          $source->removeAttribute('srcset');
        }
      }

      // Fetches the picture element fallback URI, and empty it later.
      // These address both 8.x-2 and 8.x-3 compatibility.
      $fallback_uri = isset($image['#srcset'], $image['#srcset'][0]['uri']) ? $image['#srcset'][0]['uri'] : $image['#uri'];

      // Prevents invalid IMG tag when one pixel placeholder is disabled.
      $image['#uri'] = static::PLACEHOLDER;
      $image['#srcset'] = '';

      // Cleans up the no-longer relevant attributes for controlling element.
      unset($attributes['data-srcset'], $image['#attributes']['data-srcset']);
    }
    else {
      $fallback_uri = $image['#uri'];

      $attributes['data-srcset'] = $attributes['srcset']->value();
      $image['#attributes']['data-srcset'] = $attributes['srcset']->value();
      $image['#attributes']['srcset'] = '';
    }

    // Blazy needs controlling element to have fallback [data-src], else error.
    $image['#attributes']['data-src'] = $fallback_uri;
    $image['#attributes']['class'][] = 'b-lazy b-responsive';

    // Only replace fallback image URI with 1px placeholder, if so configured.
    // This prevents double-downloading the fallback image.
    if (!empty($attributes['data-b-lazy'])) {
      $image['#uri'] = static::PLACEHOLDER;
    }

    unset($attributes['data-b-lazy'], $image['#attributes']['data-b-lazy']);
  }

  /**
   * Implements hook_config_schema_info_alter().
   */
  public static function configSchemaInfoAlter(array &$definitions, $formatter = 'blazy_base', $settings = []) {
    if (isset($definitions[$formatter])) {
      $mappings = &$definitions[$formatter]['mapping'];
      $settings = $settings ?: BlazyDefault::extendedSettings() + BlazyDefault::gridSettings();
      foreach ($settings as $key => $value) {
        // Seems double is ignored, and causes a missing schema, unlike float.
        $type = gettype($value);
        $type = $type == 'double' ? 'float' : $type;
        $mappings[$key]['type'] = $key == 'breakpoints' ? 'mapping' : (is_array($value) ? 'sequence' : $type);

        if (!is_array($value)) {
          $mappings[$key]['label'] = Unicode::ucfirst(str_replace('_', ' ', $key));
        }
      }

      if (isset($mappings['breakpoints'])) {
        foreach (BlazyDefault::getConstantBreakpoints() as $breakpoint) {
          $mappings['breakpoints']['mapping'][$breakpoint]['type'] = 'mapping';
          foreach (['breakpoint', 'width', 'image_style'] as $item) {
            $mappings['breakpoints']['mapping'][$breakpoint]['mapping'][$item]['type']  = 'string';
            $mappings['breakpoints']['mapping'][$breakpoint]['mapping'][$item]['label'] = Unicode::ucfirst(str_replace('_', ' ', $item));
          }
        }
      }

      // @todo: Drop non-UI stuffs.
      foreach (['dimension', 'display', 'item_id'] as $key) {
        $mappings[$key]['type'] = 'string';
      }
    }
  }

  /**
   * Returns the trusted HTML ID of a single instance.
   */
  public static function getHtmlId($string = 'blazy', $id = '') {
    if (!isset(static::$blazyId)) {
      static::$blazyId = 0;
    }

    // Do not use dynamic Html::getUniqueId, otherwise broken AJAX.
    return empty($id) ? Html::getId($string . '-' . ++static::$blazyId) : strip_tags($id);
  }

  /**
   * Return blazy global config.
   *
   * @deprecated in blazy:8.x-1.0 and is removed from blazy:8.x-2.0. Use
   *   \Drupal\blazy\BlazyManager::configLoad() instead.
   * @see https://www.drupal.org/node/3103018
   */
  public static function getConfig($setting_name = '', $settings = 'blazy.settings') {
    return \Drupal::service('blazy.manager')->configLoad($setting_name, $settings);
  }

  /**
   * Checks if an image style contains crop effect.
   *
   * @deprecated in blazy:8.x-1.0 and is removed from blazy:8.x-2.0. Use
   *   \Drupal\blazy\BlazyManager::isCrop() instead.
   * @see https://www.drupal.org/node/3103018
   */
  public static function isCrop($style = NULL) {
    return \Drupal::service('blazy.manager')->isCrop($style);
  }

}
