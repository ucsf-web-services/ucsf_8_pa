<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\acquia_contenthub\EntityManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\image\Entity\ImageStyle;

/**
 * Defines a form that alters node type form to add a preview image form.
 */
class NodeTypePreviewImageForm {

  use StringTranslationTrait;

  const PREVIEW_IMAGE_DEFAULT_KEY = 'acquia_contenthub_preview_image';
  const PREVIEW_IMAGE_ADD_DEFAULT_KEY = 'acquia_contenthub_preview_image_add';

  /**
   * Entity Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Content Hub Configuration Entity.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface
   */
  private $contenthubEntityConfig;

  /**
   * Processed field hashes.
   *
   * A list of spl_object_hash codes of objects that this service has already
   * iterated through. This is for handling circular referencing entities.
   *
   * Example: ['000000005e937119000000007b808ade' => TRUE]
   *
   * @var array
   */
  private $processedFieldHashes = [];

  /**
   * Available image fields, keyed by field key "roadmap" and valued at labels.
   *
   * Example: [
   *  'field_image' => 'Image',
   *  'field_media->thumbnail' => Media->Thumbnail,
   * ]
   *
   * @var array
   */
  private $imageFields = [];

  /**
   * Constructor.
   *
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The Entity Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The Entity Field Manager.
   */
  public function __construct(EntityManager $entity_manager, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    // We are assuming this is ONLY working for 'node' entities. If preview
    // images are to be supported for entities other than nodes, we will need
    // to change this line.
    $this->contenthubEntityConfig = $entity_manager->getContentHubEntityTypeConfigurationEntity('node');
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Get Form.
   *
   * @param string $node_type
   *   Node Type.
   *
   * @return array
   *   Acquia Content Hub preview image Form.
   */
  public function getForm($node_type) {
    $form = [
      '#title' => $this->t('Acquia Content Hub'),
      '#type' => 'details',
      '#tree' => TRUE,
      '#group' => 'additional_settings',
    ];

    // Find image fields.
    $this->collectImageFields('node', $node_type);
    if (empty($this->imageFields)) {
      $form['no_image_field'] = [
        '#type' => 'markup',
        '#markup' => '<div>' . $this->t('This content type has no image field yet.') . '</div>',
      ];
      return $form;
    }

    // Find image styles.
    $image_styles = image_style_options(FALSE);

    // If the default option is not in the system, offer to create and use the
    // Acquia Content Hub default style.
    if (!isset($image_styles[self::PREVIEW_IMAGE_DEFAULT_KEY])) {
      $image_styles = [self::PREVIEW_IMAGE_ADD_DEFAULT_KEY => $this->t('Acquia Content Hub Preview Image (150×150)')] + $image_styles;
    }

    // Obtaining preview image field and style from the configuration entity.
    $preview_image_field = $this->contenthubEntityConfig->getPreviewImageField($node_type);
    $preview_image_style = $this->contenthubEntityConfig->getPreviewImageStyle($node_type);

    // Building the form.
    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t("Select content type's preview image."),
      '#options' => $this->imageFields,
      '#default_value' => (isset($preview_image_field) ? $preview_image_field : ''),
      '#empty_option' => $this->t('None'),
      '#empty_value' => '',
    ];
    $form['style'] = [
      '#type' => 'select',
      '#title' => $this->t("Select the preview image's style."),
      '#options' => $image_styles,
      '#default_value' => (isset($preview_image_style) ? $preview_image_style : ''),
      '#empty_option' => $this->t('None'),
      '#empty_value' => '',
      '#states' => [
        'visible' => [
          ':input[name="acquia_contenthub[field]"]' => ['!value' => ''],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Collect image fields.
   *
   * Traverse the FieldableEntity and its fields, collect a field "roadmap" that
   * can lead to an image file.
   *
   * @param string $target_type
   *   Fieldable entity's identifier.
   * @param string $type
   *   Type of the fieldable entity.
   * @param string $key_prefix
   *   The concatenated entity field keys that has been traversed through.
   * @param string $label_prefix
   *   The concatenated entity labels that has been traversed through.
   */
  private function collectImageFields($target_type, $type, $key_prefix = '', $label_prefix = '') {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($target_type, $type);
    foreach ($field_definitions as $field_key => $field_definition) {
      $field_type = $field_definition->getType();
      $field_target_type = $field_definition->getSetting('target_type');
      $field_label = $field_definition->getLabel();
      $full_label = $label_prefix . $field_label;
      $full_key = $key_prefix . $field_key;

      // 1) Image type.
      if ($field_type === 'image') {
        $this->imageFields[$full_key] = $full_label . ' (' . $full_key . ')';
        continue;
      }

      // Check if the field has already been processed. If so, skip.
      $field_hash = spl_object_hash($field_definition);
      if (isset($this->processedFieldHashes[$field_hash])) {
        continue;
      }

      // 2) Entity Reference type whose entity is Fieldable.
      if ($field_type === 'entity_reference' &&
        $this->entityTypeManager->getDefinition($field_target_type)->entityClassImplements('\Drupal\Core\Entity\FieldableEntityInterface')
      ) {
        // Track this field, since it is about to be processed.
        $this->processedFieldHashes[$field_hash] = TRUE;

        // Process this field.
        $this->collectImageFields($field_target_type, $field_type, $full_key . '->', $full_label . '->');
        continue;
      }
    }
  }

  /**
   * Save settings.
   *
   * @param string $node_type
   *   Node Type.
   * @param array $settings
   *   Settings.
   */
  public function saveSettings($node_type, ?array $settings) {
    if (NULL === $settings || !isset($settings['field'], $settings['style'])) {
      return;
    }

    if ($settings['style'] === self::PREVIEW_IMAGE_ADD_DEFAULT_KEY) {
      $this->createDefaultImageStyle();
      $settings['style'] = self::PREVIEW_IMAGE_DEFAULT_KEY;
    }

    // Saving configuration entity.
    $this->contenthubEntityConfig->setPreviewImageField($node_type, $settings['field']);
    $this->contenthubEntityConfig->setPreviewImageStyle($node_type, $settings['style']);
    $this->contenthubEntityConfig->save();
  }

  /**
   * Create default image style.
   */
  public function createDefaultImageStyle() {
    $image_style = ImageStyle::create([
      'name' => self::PREVIEW_IMAGE_DEFAULT_KEY,
      'label' => $this->t('Acquia Content Hub Preview Image (150×150)'),
    ]);
    $image_style->addImageEffect([
      'id' => 'image_scale_and_crop',
      'weight' => 1,
      'data' => [
        'width' => 150,
        'height' => 150,
      ],
    ]);
    $image_style->save();
  }

}
