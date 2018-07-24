<?php

namespace Drupal\acquia_contenthub\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Defines a ContentHubEntityTypeConfig configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "acquia_contenthub_entity_config",
 *   label = @Translation("Acquia Content Hub Entity Type configuration"),
 *   config_prefix = "entity",
 *   admin_permission = "Administer Acquia Content Hub",
 *   label_callback = "getLabelFromPlugin",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   config_export = {
 *     "id",
 *     "bundles",
 *   }
 * )
 */
class ContentHubEntityTypeConfig extends ConfigEntityBase implements ContentHubEntityTypeConfigInterface {

  /**
   * The Content Hub Entity Type Configuration.
   *
   * @var string
   */
  protected $id;

  /**
   * The Bundle Configuration.
   *
   * @var array
   *   An array keyed by bundle.
   */
  protected $bundles;

  /**
   * Gets the list of bundles and their configuration.
   *
   * @return array
   *   An array keyed by bundle.
   */
  public function getBundles() {
    return $this->bundles;
  }

  /**
   * Check if this bundle is enabled.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return bool
   *   TRUE if enabled, FALSE otherwise.
   */
  public function isEnableIndex($bundle) {
    return !empty($this->bundles[$bundle]['enable_index']);
  }

  /**
   * Check if view modes are enabled.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return bool
   *   TRUE if enabled, FALSE otherwise.
   */
  public function isEnabledViewModes($bundle) {
    return !empty($this->bundles[$bundle]['enable_viewmodes']);
  }

  /**
   * Obtains the list of rendering view modes.
   *
   * Note this does not check whether the view modes are enabled so a previous
   * check on that has to be done.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return array
   *   An array of rendering view modes.
   */
  public function getRenderingViewModes($bundle) {
    return isset($this->bundles[$bundle]['rendering']) ? $this->bundles[$bundle]['rendering'] : [];
  }

  /**
   * Sets the bundles.
   *
   * @param array $bundles
   *   An array of bundles configuration.
   */
  public function setBundles(array $bundles) {
    $this->bundles = $bundles;
  }

  /**
   * Obtains the Preview Image Field for this particular bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return string|null
   *   The preview image field if exists, NULL otherwise.
   */
  public function getPreviewImageField($bundle) {
    return isset($this->bundles[$bundle]['preview_image_field']) ? $this->bundles[$bundle]['preview_image_field'] : NULL;
  }

  /**
   * Obtains the Preview Image Style for this particular bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return string|null
   *   The preview image style if exists, NULL otherwise.
   */
  public function getPreviewImageStyle($bundle) {
    return isset($this->bundles[$bundle]['preview_image_style']) ? $this->bundles[$bundle]['preview_image_style'] : NULL;
  }

  /**
   * Sets the preview image field for a specific bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   * @param string $image_field
   *   The preview image field.
   */
  public function setPreviewImageField($bundle, $image_field) {
    if ($this->isEnableIndex($bundle)) {
      $this->bundles[$bundle]['preview_image_field'] = $image_field;
    }
  }

  /**
   * Sets the preview image style for a specific bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   * @param string $image_style
   *   The preview image style.
   */
  public function setPreviewImageStyle($bundle, $image_style) {
    if ($this->isEnableIndex($bundle)) {
      $this->bundles[$bundle]['preview_image_style'] = $image_style;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Add dependencies on module.
    $entity_type = $this->entityTypeManager()->getDefinition($this->id());
    $this->addDependency('module', $entity_type->getProvider());

    // Add config dependencies.
    $bundles = array_keys($this->getBundles());
    foreach ($bundles as $bundle) {
      if ($this->isEnableIndex($bundle)) {
        // Add dependency on this particular bundle.
        $this->calculateDependenciesForBundle($entity_type, $bundle);

        // Add dependencies on all enabled view modes.
        if ($this->isEnabledViewModes($bundle)) {
          $this->calculateDependenciesForViewModes($entity_type, $bundle);
        }

        // Add dependencies on preview image fields and styles.
        $entity_field_manager = $this->entityFieldManager();
        $this->calculateDependenciesForPreviewImage($entity_field_manager, $bundle);

      }
    }
    return $this;
  }

  /**
   * Calculates dependencies for bundle.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The Entity Type object.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function calculateDependenciesForBundle(EntityTypeInterface $entity_type, $bundle) {
    $config_bundle = $entity_type->getBundleConfigDependency($bundle);
    $this->addDependency($config_bundle['type'], $config_bundle['name']);
  }

  /**
   * Calculate dependencies for view modes.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The Entity Type object.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function calculateDependenciesForViewModes(EntityTypeInterface $entity_type, $bundle) {
    if ($this->isEnabledViewModes($bundle)) {
      $view_modes = $this->getRenderingViewModes($bundle);
      foreach ($view_modes as $view_mode) {
        // Enable dependency on these view modes.
        /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
        $viewmode = "{$entity_type->id()}.$bundle.$view_mode";
        if ($display = EntityViewDisplay::load($viewmode)) {
          $this->addDependencies($display->getDependencies());
        }
      }
    }
  }

  /**
   * Calculates dependencies for Preview Image.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The Entity Field Manager service.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function calculateDependenciesForPreviewImage(EntityFieldManagerInterface $entity_field_manager, $bundle) {
    $preview_image_field = $this->getPreviewImageField($bundle);
    $preview_image_style = $this->getPreviewImageStyle($bundle);

    // Calculate dependencies for preview image field.
    if (!empty($preview_image_field)) {
      /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
      $fields = $entity_field_manager->getFieldDefinitions($this->id(), $bundle);
      $field_config = isset($fields[$preview_image_field]) ? $fields[$preview_image_field]->getConfig($bundle) : FALSE;
      if ($field_config) {
        $this->addDependencies($field_config->getDependencies());
      }

      // Calculate dependencies for preview image style.
      if (!empty($preview_image_style)) {
        $image_style = ImageStyle::load($preview_image_style);
        if (isset($image_style)) {
          $this->addDependency($image_style->getConfigDependencyKey(), $image_style->getConfigDependencyName());
          $this->addDependencies($image_style->getDependencies());
        }
      }
    }
  }

  /**
   * Gets the entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   Returns the EntityTypeManager service.
   */
  protected function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  /**
   * Gets the entity field manager service.
   *
   * @return \Drupal\Core\Entity\EntityFieldManagerInterface
   *   Returns the EntityFieldManager service.
   */
  protected function entityFieldManager() {
    return \Drupal::getContainer()->get('entity_field.manager');
  }

}
