<?php

namespace Drupal\acquia_contenthub;

/**
 * Interface for ContentHubEntityTypeConfig class.
 */
interface ContentHubEntityTypeConfigInterface {

  /**
   * Obtains the list of bundles.
   */
  public function getBundles();

  /**
   * Checks whether this bundle is enabled.
   *
   * @param string $bundle
   *   The bundle to check for.
   *
   * @return bool
   *   TRUE if the the bundle is enabled, FALSE otherwise.
   */
  public function isEnableIndex($bundle);

  /**
   * Checks if the view modes rendering is enabled for this bundle.
   *
   * @param string $bundle
   *   The bundle to check for.
   *
   * @return bool
   *   TRUE if view modes rendering is enabled for this bundle, FALSE otherwise.
   */
  public function isEnabledViewModes($bundle);

  /**
   * Obtains the list of enabled view modes for a particular bundle.
   *
   * @param string $bundle
   *   The bundle to check for.
   *
   * @return array
   *   An array of view modes.
   */
  public function getRenderingViewModes($bundle);

  /**
   * Sets the bundle array for this configuration entity.
   *
   * @param array $bundles
   *   An array of bundles.
   */
  public function setBundles(array $bundles);

  /**
   * Obtains the Preview Image Field for this particular bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return string|null
   *   The preview image field if exists, NULL otherwise.
   */
  public function getPreviewImageField($bundle);

  /**
   * Obtains the Preview Image Style for this particular bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return string|null
   *   The preview image style if exists, NULL otherwise.
   */
  public function getPreviewImageStyle($bundle);

  /**
   * Sets the preview image field for a specific bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   * @param string $image_field
   *   The preview image field.
   */
  public function setPreviewImageField($bundle, $image_field);

  /**
   * Sets the preview image style for a specific bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   * @param string $image_style
   *   The preview image style.
   */
  public function setPreviewImageStyle($bundle, $image_style);

  /**
   * Save the settings.
   */
  public function save();

}
