<?php

namespace Drupal\applenews\Plugin\Field\FieldWidget;

use Drupal\applenews\ApplenewsManager;
use Drupal\applenews\Repository\ApplenewsChannelRepository;
use Drupal\applenews\Repository\ApplenewsTemplateRepository;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default applenews widget.
 *
 * @FieldWidget(
 *   id = "applenews_default",
 *   label = @Translation("Apple News"),
 *   field_types = {
 *     "applenews_default"
 *   }
 * )
 */
class Applenews extends WidgetBase {

  /**
   * Apple news channel repository.
   *
   * @var \Drupal\applenews\Repository\ApplenewsChannelRepository
   */
  protected $channelRepository;

  /**
   * Apple news template repository.
   *
   * @var \Drupal\applenews\Repository\ApplenewsTemplateRepository
   */
  protected $templateRepository;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\applenews\Repository\ApplenewsChannelRepository $channel_repository
   *   Apple news channel repository.
   * @param \Drupal\applenews\Repository\ApplenewsTemplateRepository $template_repository
   *   Apple news channel repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ApplenewsChannelRepository $channel_repository, ApplenewsTemplateRepository $template_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->channelRepository = $channel_repository;
    $this->templateRepository = $template_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('applenews.channel_repository'),
      $container->get('applenews.template_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $channels = $this->channelRepository->getChannels();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();
    $templates = $this->templateRepository->getTemplatesForEntity($entity);

    $element['#attached']['library'][] = 'applenews/drupal.applenews.admin';
    if (empty($channels)) {
      $element = $this->formElementNoChannels($items, $delta, $element, $form, $form_state);
    }
    elseif (empty($templates)) {
      $element = $this->formElementNoTemplates($items, $delta, $element, $form, $form_state);
    }
    else {
      $element = $this->formElementFullyConfigured($items, $delta, $element, $form, $form_state);
    }

    $element = $this->addDownloadLinks($items, $delta, $element, $form, $form_state);
    $element = $this->moveToAdvancedSettings($items, $delta, $element, $form, $form_state);
    return $element;
  }

  /**
   * The Apple News widget when there are no templates configured.
   *
   * Renders a message prompting the user to configure Apple News templates.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   The form array element for the Apple News widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Apple News widget element.
   */
  protected function formElementNoTemplates(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();
    $element['message'] = [
      '#type' => 'item',
      '#title' => $this->t('No templates available'),
      '#markup' => $this->t('Add a template to %type type. Check Apple News template <a href=":url">configuration</a> page.', [
        '%type' => $entity->bundle(),
        ':url' => Url::fromRoute('entity.applenews_template.collection')->toString(),
      ]),
    ];
    return $element;
  }

  /**
   * The Apple News widget when there are no channels configured.
   *
   * Renders a message prompting the user to configure Apple News channels. Also
   * renders a list of links to download the Apple News format article in the
   * various configured templates, if any.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   The form array element for the Apple News widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Apple News widget element.
   */
  protected function formElementNoChannels(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $element['message'] = [
      '#type' => 'item',
      '#title' => $this->t('No channels available'),
      '#markup' => $this->t('There are no channels available. At least one channel must be setup to publish content to Apple News. To set up a channel, review the <a href=":url">Apple News settings</a>.', [':url' => Url::fromRoute('entity.applenews_template.collection')->toString()]),
    ];
    return $element;
  }

  /**
   * The Apple News widget when everything is fully configured.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   The form array element for the Apple News widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Apple News widget element.
   */
  protected function formElementFullyConfigured(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $field_name = $items->getName();
    $default_channels = unserialize($items[$delta]->channels);
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();
    $templates = $this->getOptionsFromTemplates($this->templateRepository->getTemplatesForEntity($entity));
    // @todo Dependency injection.
    /** @var \Drupal\applenews\Entity\ApplenewsArticle $article */
    $article = ApplenewsManager::getArticle($entity, $field_name);

    $element += [
      '#element_validate' => [[get_class($this), 'validateFormElement']],
    ];
    $element['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Publish to Apple News'),
      '#default_value' => $items->status,
      '#attributes' => [
        'class' => ['applenews-publish-flag'],
      ],
    ];
    if ($article) {
      $element['created'] = [
        '#type' => 'item',
        '#title' => $this->t('Apple News post date'),
        '#markup' => $article->getCreatedFormatted(),
      ];
      $element['share_url'] = [
        '#type' => 'item',
        '#title' => $this->t('Share URL'),
        '#markup' => $this->t('<a href=":url">:url</a>', [':url' => $article->getShareUrl()]),
      ];
      $delete_url = Url::fromRoute('applenews.remote.article_delete', [
        'entity_type' => $entity->getEntityTypeId(),
        'entity' => $entity->id(),
      ]);
      $element['delete'] = [
        '#type' => 'item',
        '#title' => $this->t('Delete'),
        '#markup' => $this->t('<a href=":url">Delete</a> this article from Apple News.', [':url' => $delete_url->toString()]),
      ];
      if (extension_loaded('zip') && $templates && $entity->id()) {
        $element['preview'] = [
          '#type' => 'item',
          '#title' => $this->t('Preview'),
          '#markup' => $this->t('Download the Apple News generated document (use the News Preview app to preview the article): <ul>', []),
        ];
        foreach ($templates as $template_id => $template) {
          $url_preview = Url::fromRoute('applenews.preview_download', [
            'entity_type' => $entity->getEntityTypeId(),
            'entity' => $entity->id(),
            'revision_id' => $entity->getLoadedRevisionId(),
            'template_id' => $template_id,
          ]);

          // @todo: Fix route, to support other than node.
          $element['preview']['#markup'] .= ' <li>' . $this->t('<a href=":url">:label</a> template</li>', [
            ':url' => $url_preview->toString(),
            ':label' => $template,
          ]);
        }
        $element['preview']['#markup'] .= '</ul>';
      }
    }
    $element['template'] = [
      '#type' => 'select',
      '#title' => t('Template'),
      '#default_value' => $items->template,
      '#options' => $templates,
      '#description' => $this->t('Select template to use for Applenews'),
      '#states' => [
        'visible' => [
          ':input[name="' . $items->getName() . '[' . $delta . '][status]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['channels'] = [
      '#type' => 'container',
      '#title' => $this->t('Default channels and sections'),
      '#group' => 'fieldset',
      '#states' => [
        'visible' => [
          ':input[name="' . $items->getName() . '[' . $delta . '][status]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    foreach ($this->channelRepository->getChannels() as $channel) {
      $channel_key = $channel->getChannelId();
      $element['channels'][$channel_key] = [
        '#type' => 'checkbox',
        '#title' => $channel->getName(),
        '#default_value' => isset($default_channels[$channel_key]),
        '#attributes' => [
          'data-channel-id' => $channel_key,
        ],
        '#states' => [
          'visible' => [
            ':input[name="' . $items->getName() . '[' . $delta . '][status]"]' => ['checked' => TRUE],
          ],
          'checked' => [
            ':input[data-section-of="' . $channel_key . '"]' => ['checked' => TRUE],
          ],
        ],
      ];
      foreach ($channel->getSections() as $section_id => $section_label) {
        $section_key = $channel_key . '-section-' . $section_id;
        $element['sections'][$section_key] = [
          '#type' => 'checkbox',
          '#title' => $section_label,
          '#default_value' => isset($default_channels[$channel_key][$section_id]),
          '#attributes' => [
            'data-section-of' => $channel_key,
            'class' => ['applenews-sections'],
          ],
          '#states' => [
            'visible' => [
              ':input[name="' . $items->getName() . '[' . $delta . '][status]"]' => ['checked' => TRUE],
            ],
          ],
        ];
      }
    }
    $element['is_preview'] = [
      '#title' => $this->t('Preview'),
      '#type' => 'checkbox',
      '#default_value' => $items->is_preview,
      '#description' => $this->t('Flags this article as a preview, only visible to members of your channel. <strong>Note:</strong> If your channel has not been approved, you must publish as a preview.'),
      '#states' => [
        'visible' => [
          ':input[name="' . $items->getName() . '[' . $delta . '][status]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $element;
  }

  /**
   * If the form has an advanced tab-set, move the Apple News widget there.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   The form array element for the Apple News widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Apple News widget element.
   */
  protected function moveToAdvancedSettings(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();

    // If the advanced settings tabs-set is available (normally rendered in the
    // second column on wide-resolutions), place the field as a details element
    // in this tab-set.
    if (isset($form['advanced'])) {
      // Override widget title to be helpful for end users.
      $element['#title'] = $this->t('Apple News settings');

      $element += [
        '#type' => 'details',
        '#group' => 'advanced',
        '#attributes' => [
          'class' => ['applenews-' . Html::getClass($entity->getEntityTypeId()) . '-settings-form'],
        ],
        '#open' => isset($article) && $items->status,
      ];
    }

    return $element;
  }

  /**
   * Add download links to the Apple News widget.
   *
   * Download the entity in the generated Apple News format.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   The form array element for the Apple News widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Apple News widget element.
   */
  protected function addDownloadLinks(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $channels = $this->channelRepository->getChannels();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();
    $templates = $this->templateRepository->getTemplatesForEntity($entity);

    // Not applicable if the zip extension is not enabled, if no templates have
    // been configured or if the entity is new.
    if (!extension_loaded('zip') || empty($templates) || $entity->isNew()) {
      return $element;
    }

    if (empty($channels)) {
      $element['download'] = [
        '#type' => 'item',
        '#title' => $this->t('Download'),
        '#markup' => $this->t('Download the Apple News generated document (use the News Preview app to preview the article):'),
        'templates' => [
          '#theme' => 'item_list',
          '#items' => [],
        ],
      ];
      foreach ($templates as $template) {
        $url_preview = Url::fromRoute('applenews.preview_download', [
          'entity_type' => $entity->getEntityTypeId(),
          'entity' => $entity->id(),
          'revision_id' => $entity->getLoadedRevisionId(),
          'template_id' => $template->id(),
        ]);
        $element['download']['templates']['#items'][] = [
          '#type' => 'link',
          '#url' => $url_preview,
          '#title' => $template->label(),
        ];
      }
    }
    else {
      $first_template = reset($templates);
      $url_preview = Url::fromRoute('applenews.preview_download', [
        'entity_type' => $entity->getEntityTypeId(),
        'entity' => $entity->id(),
        'revision_id' => $entity->getLoadedRevisionId(),
        'template_id' => $items->template ?? $first_template->id(),
      ]);
      $element['download'] = [
        '#type' => 'item',
        '#title' => $this->t('Download'),
        '#markup' => $this->t('<a href=":url">Download</a> the Apple News generated document (use the News Preview app to preview the article).', [
          ':url' => $url_preview->toString(),
        ]),
      ];
    }

    return $element;
  }

  /**
   * Form element validation handler for URL alias form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateFormElement(array $element, FormStateInterface $form_state) {
    $status = $element['status']['#value'];
    if ($status) {
      // If status exist, at least one channel should be selected.
      $has_channel = FALSE;
      $has_section = FALSE;
      foreach (Element::children($element['channels']) as $key) {
        if ($element['channels'][$key]['#value']) {
          $has_channel = TRUE;
        }
      }
      // If a channel selected, at least one section should selected.
      foreach (Element::children($element['sections']) as $key) {
        if ($element['sections'][$key]['#value']) {
          $has_section = TRUE;
        }
      }

      // Show consolidated message, if no channel AND sections selected.
      if (!$has_channel && !$has_section) {
        $form_state->setError($element['channels'], t('Apple News: At least one channel and a section should be selected to publish.'));
      }
      elseif (!$has_channel) {
        $form_state->setError($element['channels'], t('Apple News: At least one channel should be selected to publish.'));
      }
      elseif (!$has_section) {
        $form_state->setError($element['sections'], t('Apple News: At least one section should be selected to publish.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $enabled = $this->getSetting('status');
    if ($enabled) {
      $summary[] = t('Template: !template', ['!template' => $this->getSetting('template')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Update channels and sections structure for storage and API call.
    foreach ($values as &$value) {
      $value += [
        'status' => FALSE,
        'template' => '',
        'channels' => '',
        'is_preview' => TRUE,
      ];
      $result = [];
      $channels = array_keys(array_filter($value['channels']));
      $sections = array_keys(array_filter($value['sections']));
      foreach ($channels as $channel_id) {
        foreach ($sections as $section_id) {
          if (strpos($section_id, $channel_id) === 0) {
            $section_id_result = substr($section_id, strlen($channel_id . '-section-'));
            $result[$channel_id][$section_id_result] = 1;
          }
        }
      }
      $value['channels'] = serialize($result);
      unset($value['sections']);
    }
    return $values;
  }

  /**
   * Turn an array of Apple News templates into form options.
   *
   * @param \Drupal\applenews\Entity\ApplenewsTemplate[] $templates
   *   Array of Apple News template entities.
   *
   * @return array
   *   Array of template options, keyed by id.
   */
  protected function getOptionsFromTemplates(array $templates): array {
    $options = [];
    foreach ($templates as $template) {
      $options[$template->id()] = $template->label();
    }
    return $options;
  }

}
