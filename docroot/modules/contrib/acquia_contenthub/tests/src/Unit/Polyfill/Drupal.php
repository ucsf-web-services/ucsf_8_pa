<?php

namespace {

  use Drupal\Tests\acquia_contenthub\Unit\Polyfill\Drupal\PolyfillController;

  if (!function_exists('acquia_polyfill_controller_set_return')) {

    /**
     * Set polyfill return value.
     *
     * @param string $function_name
     *   The name of the function that is being polyfilled.
     * @param mixed $return_value
     *   Return value.
     */
    function acquia_polyfill_controller_set_return($function_name, $return_value) {
      PolyfillController::$return[$function_name] = $return_value;
    }

  }

  if (!function_exists('t')) {

    /**
     * Mock Drupal's t function.
     *
     * @param string $string
     *   String to be translated.
     * @param array $args
     *   An array in the form ['from' => 'to', ...].
     *
     * @return string
     *   Return value.
     */
    function t($string, array $args = []) {
      return strtr($string, $args);
    }

  }

  if (!function_exists('image_style_options')) {

    /**
     * Mock Drupal's image_style_options function.
     *
     * @param bool $include_empty
     *   Include empty if TRUE, don't include empty otherwise.
     *
     * @return array
     *   Return value.
     */
    function image_style_options($include_empty = TRUE) {
      return PolyfillController::$return['image_style_options'];
    }

  }

  if (!function_exists('file_create_url')) {

    /**
     * Mock Drupal's file_create_url function.
     *
     * @param string $uri
     *   URL.
     *
     * @return string
     *   Return value.
     */
    function file_create_url($uri) {
      return 'file_create_url:' . $uri;
    }

  }

}

namespace Drupal\Tests\acquia_contenthub\Unit\Polyfill\Drupal {

  /**
   * Polyfill controller.
   *
   * This class manages polyfilled global functions.
   */
  class PolyfillController {
    /**
     * Function return values.
     *
     * @var arraymanagesfunctionsreturnvalues
     */
    public static $return = [];

  }

}
