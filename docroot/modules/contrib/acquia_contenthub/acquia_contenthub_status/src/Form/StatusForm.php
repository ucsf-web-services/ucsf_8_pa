<?php

namespace Drupal\acquia_contenthub_status\Form;

use Drupal\acquia_contenthub_status\StatusService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StatusForm.
 *
 * @package Drupal\acquia_contenthub\Form
 */
class StatusForm extends ConfigFormBase {


  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * The Content Hub Status service.
   *
   * @var \Drupal\acquia_contenthub_status\StatusService
   */
  protected $statusService;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, StatusService $status_controller, EmailValidator $email_validator) {
    parent::__construct($config_factory);
    $this->statusService = $status_controller;
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('acquia_contenthub_status.status'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_contenthub_status.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['acquia_contenthub_status.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('acquia_contenthub_status.settings');

    $form['description'] = [
      '#markup' => $this->t('Settings for Status check with Content Hub to detect not up to date imported content.'),
    ];

    $form['check_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Limit and Threshold settings'),
      '#collapsible' => TRUE,
    ];

    // Threshold in minutes.
    $threshold = [5, 10, 15, 30, 60, 120];
    $threshold = array_combine($threshold, $threshold);
    $form['check_settings']['threshold'] = [
      '#title' => $this->t('Threshold'),
      '#type' => 'select',
      '#name' => 'threshold',
      '#options' => $threshold,
      '#description' => $this->t('How many minutes imported entities can be behind by Content Hub.'),
      '#default_value' => $config->get('threshold'),
    ];

    $limit = [100, 200, 300, 400, 500];
    $limit = array_combine($limit, $limit);
    $form['check_settings']['limit'] = [
      '#title' => $this->t('Limit'),
      '#type' => 'select',
      '#name' => 'limit',
      '#options' => $limit,
      '#description' => $this->t('Number of last history events to check.'),
      '#default_value' => $config->get('limit'),
    ];

    $form['send_notification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send Email Notification'),
      '#description' => $this->t('Enable email notification when entities get behind by a given threshold.'),
      '#default_value' => $config->get('send_notification'),
    ];

    $form['email_notification_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Notification Settings'),
      '#states' => [
        'invisible' => [
          ':input[name="send_notification"]' => ['checked' => FALSE],
        ],
      ],
      '#open' => TRUE,
    ];

    $notification_emails = $config->get('notify_emails');
    $notification_emails = is_array($notification_emails) ? $notification_emails : [];
    $form['email_notification_settings']['emails'] = [
      '#type' => 'textarea',
      '#title' => t('Email addresses to notify when content outdated more than "threshold" value'),
      '#rows' => 4,
      '#default_value' => implode("\n", $notification_emails),
      '#description' => t('Put each address on a separate line. If blank, no emails will be sent.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#name' => 'save',
      '#value' => $this->t('Save configuration'),
      '#op' => 'save',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->set('notify_emails', []);
    if (!$form_state->isValueEmpty('emails')) {
      $valid = [];
      $invalid = [];
      foreach (explode("\n", trim($form_state->getValue('emails'))) as $email) {
        $email = trim($email);
        if (!empty($email)) {
          if ($this->emailValidator->isValid($email)) {
            $valid[] = $email;
          }
          else {
            $invalid[] = $email;
          }
        }
      }
      if (empty($invalid)) {
        $form_state->set('notify_emails', $valid);
      }
      elseif (count($invalid) == 1) {
        $form_state->setErrorByName('emails', $this->t('%email is not a valid email address.', ['%email' => reset($invalid)]));
      }
      else {
        $form_state->setErrorByName('emails', $this->t('%emails are not valid email addresses.', ['%emails' => implode(', ', $invalid)]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $config = $this->config('acquia_contenthub_status.settings');

    switch ($trigger['#op']) {
      case 'save':
        $config->set('threshold', $form_state->getValue('threshold'));
        $config->set('limit', $form_state->getValue('limit'));

        $emails = $form_state->get('notify_emails');
        $config->set('notify_emails', $emails);

        if (count($emails) > 0) {
          $config->set('send_notification', $form_state->getValue('send_notification'));
        }
        else {
          $config->set('send_notification', FALSE);
        }

        $config->save();
        break;
    }
  }

}
