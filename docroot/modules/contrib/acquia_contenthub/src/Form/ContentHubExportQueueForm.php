<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\acquia_contenthub\Controller\ContentHubExportQueueController;

/**
 * Implements a form to Process items from the Content Hub Export Queue.
 */
class ContentHubExportQueueForm extends ConfigFormBase {

  /**
   * The Export Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  protected $exportQueueController;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_contenthub.export_queue_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['acquia_contenthub.entity_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContentHubExportQueueController $export_queue, ConfigFactoryInterface $config_factory) {
    $this->exportQueueController = $export_queue;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController $export_queue */
    $export_queue = $container->get('acquia_contenthub.acquia_contenthub_export_queue');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $export_queue,
      $config_factory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->configFactory->get('acquia_contenthub.entity_config');
    $export_with_queue = $config->get('export_with_queue');
    $export_queue_entities_per_item = $config->get('export_queue_entities_per_item');
    $export_queue_batch_size = $config->get('export_queue_batch_size');
    $export_queue_waiting_time = $config->get('export_queue_waiting_time');

    $form['description'] = [
      '#markup' => $this->t('Instruct the content hub module to manage content export with a queue.'),
    ];

    $form['export_with_queue'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Export via queue'),
      '#description' => $this->t('Enable content export queuing for this site'),
      '#default_value' => isset($export_with_queue) ? $export_with_queue : 0,
    ];

    // Defining options.
    $number_of_items = [
      'all' => $this->t('all'),
      5 => 5,
      10 => 10,
      20 => 20,
      50 => 50,
      100 => 100,
      200 => 200,
      500 => 500,
      1000 => 1000,
    ];
    $options = range(0, 10);
    unset($options[0]);
    $waiting_time = [
      3 => 3,
      5 => 5,
      10 => 10,
      15 => 15,
      30 => 30,
    ];
    $queue_count = intval($this->exportQueueController->getQueueCount());

    // Build the Form.
    $form['export_queue_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Queue Behavior Configuration'),
      '#states' => [
        'invisible' => [
          ':input[name="export_with_queue"]' => ['checked' => FALSE],
        ],
      ],
      '#open' => TRUE,
    ];
    $form['export_queue_configuration']['batch_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Queue Batch Size'),
      '#description' => $this->t('Number of queue items that will be processed in the same batch queue process. This has an impact on the exporting site, the higher the amount the more load we are putting in a single batch process (performed in a single page request). Set to "1" by default.'),
      '#options' => $options,
      '#default_value' => !empty($export_queue_batch_size) ? $export_queue_batch_size : 1,
    ];
    $form['export_queue_configuration']['entities_per_item'] = [
      '#type' => 'select',
      '#title' => $this->t('Entities per queue item'),
      '#description' => $this->t('Maximum number of entities fired through an entity hook that will be included in each queue item. These entities and all their dependencies will be sent to Content Hub in the same HTTP Request. This has an impact on the importing sites because each HTTP Request that exports entities to Content Hub generates a webhook directed to all importing sites. Each webhook will contain the number of entities defined in this parameter plus all their dependencies. It does not affect items that are already in the queue. Set to "1" by default.'),
      '#options' => $options,
      '#default_value' => !empty($export_queue_entities_per_item) ? $export_queue_entities_per_item : 1,
    ];
    $form['export_queue_configuration']['waiting_time'] = [
      '#type' => 'select',
      '#title' => $this->t('Queue waiting time between items'),
      '#description' => $this->t('Waiting time in seconds between queue items being processed. This has an impact on importing sites because the receiving webhooks will have a at least this number of seconds spread between them. Set to "3" seconds by default.'),
      '#options' => $waiting_time,
      '#default_value' => !empty($export_queue_waiting_time) ? $export_queue_waiting_time : 5,
    ];

    if ($queue_count > 0) {
      $form['export_queue_configuration']['purge_queue'] = [
        '#type' => 'item',
        '#title' => $this->t('Purge existing queues'),
        '#description' => $this->t('Its possible an existing queue has becomed orphaned, use this function to wipe all existing queues'),
      ];
      $form['export_queue_configuration']['purge'] = [
        '#type' => 'submit',
        '#value' => t('Purge'),
        '#name' => 'purge_export_queue',
      ];
    }

    $form['run_export_queue'] = [
      '#type' => 'details',
      '#title' => $this->t('Run Export Queue'),
      '#states' => [
        'invisible' => [
          ':input[name="export_with_queue"]' => ['checked' => FALSE],
        ],
      ],
      '#open' => FALSE,
    ];
    $form['run_export_queue']['queue-list'] = [
      '#type' => 'item',
      '#title' => $this->t('Number of queue items in the Export Queue'),
      '#description' => $this->t('%num @items.', [
        '%num' => $queue_count,
        '@items' => $queue_count === 1 ? $this->t('item') : $this->t('items'),
      ]),
    ];
    $form['run_export_queue']['number_of_items'] = [
      '#type' => 'select',
      '#title' => $this->t('Queue items to process at this time'),
      '#description' => $this->t('Number of queue items to process. These items will be processed in batches.'),
      '#options' => $number_of_items,
      '#default_value' => 'all',
      '#required' => TRUE,
    ];
    $form['run_export_queue']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#name' => 'run_export_queue',
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#name' => 'export_queue_configuration',
      '#value' => t('Save Configuration'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $queue_count = intval($this->exportQueueController->getQueueCount());
    $trigger = $form_state->getTriggeringElement();
    switch ($trigger['#name']) {
      case 'run_export_queue':
        if (!empty($queue_count)) {
          $number_of_items = $form_state->getValues()['number_of_items'];
          $this->exportQueueController->processQueueItems($number_of_items);
        }
        else {
          drupal_set_message($this->t('You cannot run the export queue because it is empty.'), 'warning');
        }
        break;
      case 'purge_export_queue':
        $this->exportQueueController->purgeQueue();
        drupal_set_message($this->t("Purged all contenthub export queues."));
        break;
      default:
        $config = $this->configFactory->getEditable('acquia_contenthub.entity_config');
        $export_with_queue = $form_state->getValue('export_with_queue');
        $export_queue_entities_per_item = $form_state->getValue('entities_per_item');
        $export_queue_batch_size = $form_state->getValue('batch_size');
        $export_queue_waiting_time = $form_state->getValue('waiting_time');
        $config->set('export_with_queue', $export_with_queue);
        $config->set('export_queue_entities_per_item', $export_queue_entities_per_item);
        $config->set('export_queue_batch_size', $export_queue_batch_size);
        $config->set('export_queue_waiting_time', $export_queue_waiting_time);
        $config->save();
        break;
    }
  }
}
