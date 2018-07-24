<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_contenthub\Controller\ContentHubReindex;

/**
 * Defines the form to register the webhooks.
 */
class ReExportEntitiesForm extends FormBase {

  /**
   * Content Hub Reindex Service.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubReindex
   */
  protected $reindex;

  /**
   * ReExportEntitiesForm constructor.
   *
   * @param \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex
   *   The Content Hub Reindex service.
   */
  public function __construct(ContentHubReindex $reindex) {
    $this->reindex = $reindex;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\acquia_contenthub\Controller\ContentHubReindex $reindex */
    $reindex = $container->get('acquia_contenthub.acquia_contenthub_reindex');
    return new static(
      $reindex
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_contenthub.re_export';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $reexport_entities = $this->reindex->getCountReExportEntities();
    $form['reexport_after_reindex'] = [
      '#type' => 'details',
      '#title' => $this->t('Re-Export entities after Reindex'),
      '#open' => TRUE,
      '#description' => $this->t('Re-export entities after successful Re-indexation of Content Hub Subscription'),
    ];

    $form['reexport_after_reindex']['entities'] = [
      '#type' => 'item',
      '#title' => $this->t('Number of entities waiting to be re-exported'),
      '#description' => $this->t('%num @items.', [
        '%num' => $reexport_entities,
        '@items' => $reexport_entities === 1 ? $this->t('entity') : $this->t('entities'),
      ]),
    ];

    // Only present this form if we have a successful re-indexation and we have
    // some entities that are waiting to be re-exported.
    if ($this->reindex->isReindexFinished() && $reexport_entities > 0) {
      $number_of_items = [1, 3, 5, 10, 20, 30, 50];
      $number_of_items = array_combine($number_of_items, $number_of_items);

      $form['reexport_after_reindex']['batch_size'] = [
        '#type' => 'select',
        '#options' => $number_of_items,
        '#title' => $this->t('Batch size'),
        '#description' => $this->t('Number of entities and their dependencies that will be processed in the same batch process. This has an impact on the importing site, the higher the number the more time spent in a single batch process with the potential of consuming memory. Set to "10" by default.'),
        '#default_value' => 10,
      ];

      $form['reexport_after_reindex']['run'] = [
        '#type' => 'submit',
        '#name' => 're_export',
        '#value' => $this->t('Run re-export of reindexed entities'),
        '#op' => 'run',
      ];
    }
    else {
      $form['reexport_after_reindex']['message'] = [
        '#markup' => $this->t('There are no entities marked for re-export.'),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch_size = $form_state->getValue('batch_size');
    $this->reindex->reExportEntitiesAfterReindex($batch_size);
  }

}
