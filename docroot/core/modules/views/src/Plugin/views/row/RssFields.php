<?php

namespace Drupal\views\Plugin\views\row;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders an RSS item based on fields.
 *
 * @ViewsRow(
 *   id = "rss_fields",
 *   title = @Translation("Fields"),
 *   help = @Translation("Display fields as RSS items."),
 *   theme = "views_view_row_rss",
 *   display_types = {"feed"}
 * )
 */
class RssFields extends RowPluginBase {

  /**
   * The image style manager.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Does the row plugin support to add fields to its output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RssPluginBase  object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Image\ImageFactory image_factory
   *   The image factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository = NULL, ImageFactory $image_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_display_repository);

    $this->imageFactory = $image_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository'),
      $container->get('image.factory')
    );
  }


  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['title_field'] = ['default' => ''];
    $options['link_field'] = ['default' => ''];
    $options['description_field'] = ['default' => ''];
    $options['creator_field'] = ['default' => ''];
    $options['date_field'] = ['default' => ''];
    $options['enclosure_field'] = ['default' => ''];
    $options['guid_field_options']['contains']['guid_field'] = ['default' => ''];
    $options['guid_field_options']['contains']['guid_field_is_permalink'] = ['default' => TRUE];
    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $initial_labels = ['' => $this->t('- None -')];
    $view_fields_labels = $this->displayHandler->getFieldLabels();
    $view_fields_labels = array_merge($initial_labels, $view_fields_labels);

    $form['title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Title field'),
      '#description' => $this->t('The field that is going to be used as the RSS item title for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['title_field'],
      '#required' => TRUE,
    ];
    $form['link_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Link field'),
      '#description' => $this->t('The field that is going to be used as the RSS item link for each row. This must either be an internal unprocessed path like "node/123" or a processed, root-relative URL as produced by fields like "Link to content".'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['link_field'],
      '#required' => TRUE,
    ];
    $form['description_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Description field'),
      '#description' => $this->t('The field that is going to be used as the RSS item description for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['description_field'],
      '#required' => TRUE,
    ];
    $form['creator_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Creator field'),
      '#description' => $this->t('The field that is going to be used as the RSS item creator for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['creator_field'],
      '#required' => TRUE,
    ];
    $form['date_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Publication date field'),
      '#description' => $this->t('The field that is going to be used as the RSS item pubDate for each row. It needs to be in RFC 2822 format.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['date_field'],
      '#required' => TRUE,
    ];
    $form['enclosure_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Enclosure field'),
      '#description' => $this->t('Describes a media object that is attached to the item. This must be a file or media field.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['enclosure_field'],
    ];
    $form['guid_field_options'] = [
      '#type' => 'details',
      '#title' => $this->t('GUID settings'),
      '#open' => TRUE,
    ];
    $form['guid_field_options']['guid_field'] = [
      '#type' => 'select',
      '#title' => $this->t('GUID field'),
      '#description' => $this->t('The globally unique identifier of the RSS item.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['guid_field_options']['guid_field'],
      '#required' => TRUE,
    ];
    $form['guid_field_options']['guid_field_is_permalink'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('GUID is permalink'),
      '#description' => $this->t('The RSS item GUID is a permalink.'),
      '#default_value' => $this->options['guid_field_options']['guid_field_is_permalink'],
    ];
  }

  public function validate() {
    $errors = parent::validate();
    $required_options = ['title_field', 'link_field', 'description_field', 'creator_field', 'date_field'];
    foreach ($required_options as $required_option) {
      if (empty($this->options[$required_option])) {
        $errors[] = $this->t('Row style plugin requires specifying which views fields to use for RSS item.');
        break;
      }
    }
    // Once more for guid.
    if (empty($this->options['guid_field_options']['guid_field'])) {
      $errors[] = $this->t('Row style plugin requires specifying which views fields to use for RSS item.');
    }
    return $errors;
  }

  public function render($row) {
    static $row_index;
    if (!isset($row_index)) {
      $row_index = 0;
    }
    if (function_exists('rdf_get_namespaces')) {
      // Merge RDF namespaces in the XML namespaces in case they are used
      // further in the RSS content.
      $xml_rdf_namespaces = [];
      foreach (rdf_get_namespaces() as $prefix => $uri) {
        $xml_rdf_namespaces['xmlns:' . $prefix] = $uri;
      }
      $this->view->style_plugin->namespaces += $xml_rdf_namespaces;
    }

    // Create the RSS item object.
    $item = new \stdClass();
    $item->title = $this->getField($row_index, $this->options['title_field']);

    // If internal link, get absolute URL from URI.
    if (!UrlHelper::isExternal($this->getField($row_index, $this->options['link_field']))) {
      $item->link = $this->getAbsoluteUrl($this->getField($row_index, $this->options['link_field']));
    }
    else {
      $item->link = Url::fromUri($this->getField($row_index, $this->options['link_field']))->setAbsolute()->toString();
    }

    $field = $this->getField($row_index, $this->options['description_field']);
    $item->description = is_array($field) ? $field : ['#markup' => $field];

    $item->elements = [
      ['key' => 'pubDate', 'value' => $this->getField($row_index, $this->options['date_field'])],
      [
        'key' => 'dc:creator',
        'value' => $this->getField($row_index, $this->options['creator_field']),
        'namespace' => ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'],
      ],
    ];

    if ($this->options['enclosure_field']) {
      $field_name = $this->options['enclosure_field'];
      $field = $this->view->field[$field_name];
      $field_options = $field->options;
      $entity = $field->getEntity($this->view->result[$row_index]);
      $enclosure = $entity->$field_name;
      $file = NULL;

      if ($enclosure instanceof FileFieldItemList) {
        $value = $enclosure->getValue();
        $file = $this->entityTypeManager->getStorage('file')->load($value[0]['target_id']);
      }
      elseif ($enclosure instanceof EntityReferenceFieldItemList && count($enclosure->referencedEntities()) > 0) {
        $field = $this->entityTypeManager->getStorage('field_config')->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $field_name);
        if (isset($field)) {
          $field_storage = $this->entityTypeManager->getStorage('field_storage_config')->load($field->getTargetEntityTypeId() . '.' . $field->getName());
          if ($field->getType() == 'entity_reference' && $field_storage->getSetting("target_type") === "media") {
            $file = $enclosure->referencedEntities()[0]->get('thumbnail')->entity;
          }
        }
      }

      if (isset($file)) {
        $file_url = '';
        $file_size = '';
        $file_mimetype = '';
        $file_uri = $file->getFileUri();
        if (!empty($field_options['settings']['image_style'])) {
          $style = $this->entityTypeManager->getStorage('image_style')->load($field_options['settings']['image_style']);
          $derivative_uri = $style->buildUri($file_uri);
          $derivative_exists = TRUE;
          if (!file_exists($derivative_uri)) {
            $derivative_exists = $style->createDerivative($file_uri,
              $derivative_uri);
          }
          if ($derivative_exists) {
            $image = $this->imageFactory->get($derivative_uri);
            $file_url = file_create_url($derivative_uri);
            $file_size = $image->getFileSize();
            $file_mimetype = $image->getMimeType();
          }
        }
        else {
          // In RSS feeds, it is necessary to use absolute URLs. The 'url.site'
          // cache context is already associated with RSS feed responses, so it
          // does not need to be specified here.
          $file_url = file_create_url($file_uri);
          $file_size = $file->getSize();
          $file_mimetype = $file->getMimeType();
        }
        if (!empty($file_url)) {
          $item->elements[] = [
            'key' => 'enclosure',
            'attributes' => [
              'url' => $file_url,
              'length' => $file_size,
              'type' => $file_mimetype,
            ],
          ];
        }
      }
    }

    $guid_is_permalink_string = 'false';
    $item_guid = $this->getField($row_index, $this->options['guid_field_options']['guid_field']);
    if ($this->options['guid_field_options']['guid_field_is_permalink']) {
      $guid_is_permalink_string = 'true';

      // If the guid is an internal link, get the absolute URL from the URI.
      if (!UrlHelper::isExternal($item_guid)) {
        $item_guid = $this->getAbsoluteUrl($item_guid);
      }
      else {
        $item_guid = Url::fromUri($item_guid);
      }
    }
    $item->elements[] = [
      'key' => 'guid',
      'value' => $item_guid,
      'attributes' => ['isPermaLink' => $guid_is_permalink_string],
    ];

    $row_index++;

    foreach ($item->elements as $element) {
      if (isset($element['namespace'])) {
        $this->view->style_plugin->namespaces = array_merge($this->view->style_plugin->namespaces, $element['namespace']);
      }
    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];

    return $build;
  }

  /**
   * Retrieves a views field value from the style plugin.
   *
   * @param $index
   *   The index count of the row as expected by views_plugin_style::getField().
   * @param $field_id
   *   The ID assigned to the required field in the display.
   *
   * @return string|null|\Drupal\Component\Render\MarkupInterface
   *   An empty string if there is no style plugin, or the field ID is empty.
   *   NULL if the field value is empty. If neither of these conditions apply,
   *   a MarkupInterface object containing the rendered field value.
   */
  public function getField($index, $field_id) {
    if (empty($this->view->style_plugin) || !is_object($this->view->style_plugin) || empty($field_id)) {
      return '';
    }
    return $this->view->style_plugin->getField($index, $field_id);
  }

  /**
   * Convert a rendered URL string to an absolute URL.
   *
   * @param string $url_string
   *   The rendered field value ready for display in a normal view.
   *
   * @return string
   *   A string with an absolute URL.
   */
  protected function getAbsoluteUrl($url_string) {
    // If the given URL already starts with a leading slash, it's been processed
    // and we need to simply make it an absolute path by prepending the host.
    if (strpos($url_string, '/') === 0) {
      $host = \Drupal::request()->getSchemeAndHttpHost();
      // @todo Views should expect and store a leading /.
      // @see https://www.drupal.org/node/2423913
      return $host . $url_string;
    }
    // Otherwise, this is an unprocessed path (e.g. node/123) and we need to run
    // it through a Url object to allow outbound path processors to run (path
    // aliases, language prefixes, etc).
    else {
      return Url::fromUserInput('/' . $url_string)->setAbsolute()->toString();
    }
  }

}
