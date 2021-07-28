<?php

namespace Drupal\blazy;

/**
 * Defines shared plugin default settings for field formatter and Views style.
 */
class BlazyDefault {

  /**
   * The supported $breakpoints.
   *
   * @var array
   */
  private static $breakpoints = ['xs', 'sm', 'md', 'lg', 'xl'];

  /**
   * Defines constant for the supported text tags.
   */
  const TAGS = ['a', 'em', 'strong', 'h2', 'p', 'span', 'ul', 'ol', 'li'];

  /**
   * The current class instance.
   *
   * @var self
   */
  private static $instance = NULL;

  /**
   * The alterable settings.
   *
   * @var array
   */
  private static $alterableSettings;

  /**
   * Returns the static instance of this class.
   */
  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new BlazyDefault();
    }

    return self::$instance;
  }

  /**
   * Returns Blazy specific breakpoints.
   */
  public static function getConstantBreakpoints() {
    return self::$breakpoints;
  }

  /**
   * Returns alterable plugin settings to pass the tests.
   */
  public function alterableSettings(array &$settings = []) {
    if (!isset(static::$alterableSettings)) {
      $context = ['class' => get_called_class()];
      \Drupal::moduleHandler()->alter('blazy_base_settings', $settings, $context);
      static::$alterableSettings = $settings;
    }

    return static::$alterableSettings;
  }

  /**
   * Returns basic plugin settings.
   */
  public static function baseSettings() {
    $settings = [
      'cache'             => 0,
      'current_view_mode' => '',
      'optionset'         => 'default',
      'skin'              => '',
      'style'             => '',
    ];

    blazy_alterable_settings($settings);
    return $settings;
  }

  /**
   * Returns image-related field formatter and Views settings.
   */
  public static function baseImageSettings() {
    return [
      'background'             => FALSE,
      'box_caption'            => '',
      'box_caption_custom'     => '',
      'box_style'              => '',
      'box_media_style'        => '',
      'breakpoints'            => [],
      'caption'                => [],
      'image_style'            => '',
      'media_switch'           => '',
      'ratio'                  => '',
      'responsive_image_style' => '',
      'sizes'                  => '',
    ];
  }

  /**
   * Returns image-related field formatter and Views settings.
   */
  public static function imageSettings() {
    return [
      'iframe_lazy'     => TRUE,
      'icon'            => '',
      'layout'          => '',
      'thumbnail_style' => '',
      'view_mode'       => '',
    ] + self::baseSettings() + self::baseImageSettings();
  }

  /**
   * Returns Views specific settings.
   */
  public static function viewsSettings() {
    return [
      'class'   => '',
      'id'      => '',
      'image'   => '',
      'link'    => '',
      'overlay' => '',
      'title'   => '',
      'vanilla' => FALSE,
    ];
  }

  /**
   * Returns fieldable entity formatter and Views settings.
   */
  public static function extendedSettings() {
    return self::viewsSettings() + self::imageSettings();
  }

  /**
   * Returns optional grid field formatter and Views settings.
   */
  public static function gridSettings() {
    return [
      'grid'        => 0,
      'grid_header' => '',
      'grid_medium' => 0,
      'grid_small'  => 0,
      'style'       => '',
    ];
  }

  /**
   * Returns sensible default options common for Views lacking of UI.
   */
  public static function lazySettings() {
    return [
      'blazy' => TRUE,
      'lazy'  => 'blazy',
      'ratio' => 'fluid',
    ];
  }

  /**
   * Returns sensible default options common for entities lacking of UI.
   */
  public static function entitySettings() {
    return [
      'iframe_lazy'  => TRUE,
      'media_switch' => 'media',
      'rendered'     => FALSE,
      'view_mode'    => 'default',
      '_detached'    => TRUE,
    ] + self::lazySettings();
  }

  /**
   * Returns shared global form settings which should be consumed at formatters.
   */
  public static function uiSettings() {
    return [
      'one_pixel'        => TRUE,
      'responsive_image' => FALSE,
      'theme_hook_image' => 'blazy',
    ];
  }

  /**
   * Returns sensible default html settings to shutup notices when lacking.
   */
  public static function itemSettings() {
    return [
      'blazy_data'     => [],
      'classes'        => [],
      'content_url'    => '',
      'delta'          => 0,
      'embed_url'      => '',
      'entity_type_id' => '',
      'extension'      => '',
      'icon'           => '',
      'image_url'      => '',
      'id'             => '',
      'item_id'        => 'blazy',
      'lazy_attribute' => 'src',
      'lazy_class'     => 'b-lazy',
      'lightbox'       => FALSE,
      'namespace'      => 'blazy',
      'player'         => FALSE,
      'scheme'         => '',
      'type'           => 'image',
      'uri'            => '',
      'use_image'      => FALSE,
      'use_media'      => FALSE,
      'height'         => NULL,
      'width'          => NULL,
    ] + self::imageSettings() + self::uiSettings();
  }

  /**
   * Returns blazy theme properties, its image and container attributes.
   *
   * The reserved attributes mentioned here might be instantiated as an
   * instanceof \Drupal\Core\Template\Attribute before entering Blazy.
   */
  public static function themeProperties() {
    return [
      'attributes',
      'captions',
      'image',
      'item',
      'item_attributes',
      'settings',
      'url',
    ];
  }

  /**
   * Returns additional/ optional blazy theme attributes.
   *
   * The attributes mentioned here are only instantiated at theme_blazy() and
   * might be an empty array, not instanceof \Drupal\Core\Template\Attribute.
   */
  public static function themeAttributes() {
    return ['caption', 'media', 'url', 'wrapper'];
  }

}
