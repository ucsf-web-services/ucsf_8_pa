<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentHubImportQueueForm.
 *
 * @package Drupal\acquia_contenthub\Form
 */
class ContentHubImportQueueForm extends ConfigFormBase {

  /**
   * The queue factory for running the import queue.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, QueueFactory $queue_factory) {
    parent::__construct($config_factory);
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_contenthub.import_queue_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acquia_contenthub.entity_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acquia_contenthub.entity_config');

    $form['description'] = [
      '#markup' => $this->t('Instruct the content hub module to manage content syndication with a queue.'),
    ];

    $form['import_with_queue'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Import via queue'),
      '#description' => $this->t('Enable content import queuing for this site'),
      '#default_value' => $config->get('import_with_queue'),
    ];

    $form['import_queue_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Queue Settings'),
      '#states' => [
        'invisible' => [
          ':input[name="import_with_queue"]' => ['checked' => FALSE],
        ],
      ],
      '#open' => TRUE,
    ];

    $number_of_items = [1, 5, 10, 20, 50, 100, 200, 500, 1000];
    $number_of_items = array_combine($number_of_items, $number_of_items);

    $form['import_queue_configuration']['batch_size'] = [
      '#type' => 'select',
      '#options' => ['all' => $this->t('- All -')] + $number_of_items,
      '#title' => $this->t('Queue batch size'),
      '#description' => $this->t('Number of queue items that will be processed in the same batch queue process. This has an impact on the importing site, the higher the number the more time spent in a single batch process. Set to "1" by default.'),
      '#default_value' => $config->get('import_queue_batch_size'),
    ];

    $wait_time = [3, 5, 10, 15, 30];
    $wait_time = array_combine($wait_time, $wait_time);

    $form['import_queue_configuration']['wait_time'] = [
      '#type' => 'select',
      '#options' => $wait_time,
      '#title' => $this->t('Queue waiting time between items'),
      '#description' => $this->t('Waiting time in seconds between queue items being processed. This can be used to prevent stampeding and can be used with cron settings to control frequency.'),
      '#default_value' => $config->get('import_queue_wait_time'),
    ];

    $form['run_import_queue'] = [
      '#type' => 'details',
      '#title' => $this->t('Run the import queue'),
      '#description' => $this->t('Manually process the remaining items in the queue.'),
      '#states' => [
        'invisible' => [
          ':input[name="import_with_queue"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['run_import_queue']['actions'] = [
      '#type' => 'actions',
    ];

    $queue_count = $this->queueFactory->get('acquia_contenthub_import_queue')->numberOfItems();

    $form['run_import_queue']['queue_list'] = [
      '#type' => 'item',
      '#title' => $this->t('Number of items in the import queue'),
      '#description' => $this->t('%num @items', [
        '%num' => $queue_count,
        '@items' => $queue_count == 1 ? 'item' : 'items',
      ]),
    ];

    $form['run_import_queue']['actions']['run'] = [
      '#type' => 'submit',
      '#name' => 'run_import_queue',
      '#value' => $this->t('Run import queue'),
      '#op' => 'run',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#name' => 'import_queue_configuraiton',
      '#value' => $this->t('Save configuration'),
      '#op' => 'save',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    switch ($trigger['#op']) {

      case 'save':
        $config = $this->config('acquia_contenthub.entity_config');
        $config->set('import_with_queue', $form_state->getValue('import_with_queue'));
        $config->set('import_queue_batch_size', $form_state->getValue('batch_size'));
        $config->set('import_queue_wait_time', $form_state->getValue('wait_time'));
        $config->save();
        break;

      case 'run':
        $form_state->setRedirect('acquia_contenthub.import_queue_batch');
        break;
    }
  }

}
