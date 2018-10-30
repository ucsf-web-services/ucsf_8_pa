<?php

namespace Drupal\acquia_contenthub_subscriber\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Prepares the form for input Content Hub Filters.
 */
class ContentHubFilterForm extends EntityForm {

  /**
   * The entity query factory.
   *
   * @var |Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The current user.
   *
   * @var |Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Public Constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The entity current user.
   */
  public function __construct(QueryFactory $entity_query, AccountProxyInterface $current_user) {
    $this->entityQuery = $entity_query;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\Query\QueryFactory $entity_query */
    $entity_query = $container->get('entity.query');
    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $container->get('current_user');
    return new static($entity_query, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\acquia_contenthub_subscriber\Entity\ContentHubFilter $contenthub_filter */
    $contenthub_filter = $this->entity;

    // Change page title for the edit operation.
    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit Content Hub Filter: @name', ['@name' => $contenthub_filter->name]);
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $contenthub_filter->label(),
      '#description' => $this->t('Content Hub Filter Name.'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#default_value' => $contenthub_filter->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
        'source' => ['name'],
      ],
      '#disabled' => !$contenthub_filter->isNew(),
    ];

    // The Publish Setting.
    $form['publish_setting'] = [
      '#type' => 'select',
      '#title' => $this->t('Publish Setting'),
      '#options' => [
        'none' => 'None',
        'import' => 'Always Import',
        'publish' => 'Always Publish',
      ],
      '#default_value' => isset($contenthub_filter->publish_setting) ? $contenthub_filter->publish_setting : 'none',
      '#maxlength' => 255,
      '#description' => $this->t('Sets the Publish setting for this filter.'),
      '#required' => TRUE,
    ];

    // The Search Term.
    $form['search_term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search Term'),
      '#maxlength' => 255,
      '#default_value' => $contenthub_filter->search_term,
      '#description' => $this->t('The search term.'),
    ];

    // The From Date.
    $form['from_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date From'),
      '#default_value' => $contenthub_filter->from_date,
      '#date_date_format' => 'm-d-Y',
      '#description' => $this->t('Date starting from'),
    ];

    // The To Date.
    $form['to_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date To'),
      '#date_date_format' => 'm-d-Y',
      '#default_value' => $contenthub_filter->to_date,
      '#description' => $this->t('Date until'),
    ];

    // The Source.
    $form['source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sources'),
      '#maxlength' => 255,
      '#default_value' => $contenthub_filter->source,
      '#description' => $this->t('Source origin site UUIDs, delimited by comma ","'),
    ];

    // The Tags.
    $form['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tags'),
      '#maxlength' => 255,
      '#default_value' => $contenthub_filter->tags,
      '#description' => $this->t('Tag UUIDs, delimited by comma ","'),
    ];

    // The Entity types.
    $form['entity_types'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Entity Types'),
      '#rows' => 4,
      '#default_value' => implode("\n", $contenthub_filter->getEntityTypes()),
      '#description' => $this->t('Entity types (one entity type id per line)'),
    ];

    // The Bundles.
    $form['bundles'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Bundles'),
      '#rows' => 4,
      '#default_value' => implode("\n", $contenthub_filter->getBundles()),
      '#description' => $this->t('Bundles (one bundle id per line)'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\acquia_contenthub_subscriber\Entity\ContentHubFilter $contenthub_filter */
    $contenthub_filter = $this->entity;

    // This Filter is owned by the user who created it.
    if (empty($contenthub_filter->author)) {
      $contenthub_filter->author = $this->currentUser->id();
    }

    // Fix the "entity_types" and "bundles" properties to be arrays.
    $contenthub_filter->formatEntityTypesAndBundles();

    // Save the filter.
    $status = $contenthub_filter->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Content Hub Filter.', [
        '%label' => $contenthub_filter->label(),
      ]));
    }
    else {
      drupal_set_message($this->t('The %label Content Hub Filter was not saved.', [
        '%label' => $contenthub_filter->label(),
      ]));
    }

    $form_state->setRedirect('entity.contenthub_filter.collection');
  }

  /**
   * Checks whether this entity exists or not.
   *
   * @param int $id
   *   The id on the entity.
   *
   * @return bool
   *   If the entity exists or not.
   */
  public function exist($id) {
    $entity = $this->entityQuery->get('contenthub_filter')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
