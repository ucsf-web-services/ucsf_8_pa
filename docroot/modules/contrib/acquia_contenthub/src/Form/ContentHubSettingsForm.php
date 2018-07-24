<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\acquia_contenthub\Client\ClientManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Uuid\Uuid;
use Drupal\acquia_contenthub\ContentHubSubscription;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the form to configure the Content Hub connection settings.
 */
class ContentHubSettingsForm extends ConfigFormBase {

  /**
   * The entity manager.
   *
   * @var |Drupal\acquia_contenthub\Client\ClientManagerInterface
   */
  protected $clientManager;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription
   */
  protected $contentHubSubscription;

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
    return 'acquia_contenthub.admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['acquia_contenthub.admin_settings'];
  }

  /**
   * ContentHubSettingsForm constructor.
   *
   * @param \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription
   *   The content hub subscription.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ClientManagerInterface $client_manager, ContentHubSubscription $contenthub_subscription, ConfigFactoryInterface $config_factory) {
    $this->clientManager = $client_manager;
    $this->contentHubSubscription = $contenthub_subscription;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\acquia_contenthub\Client\ClientManagerInterface $client_manager */
    $client_manager = $container->get('acquia_contenthub.client_manager');
    /** @var \Drupal\acquia_contenthub\ContentHubSubscription $contenthub_subscription */
    $contenthub_subscription = $container->get('acquia_contenthub.acquia_contenthub_subscription');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    return new static(
      $client_manager,
      $contenthub_subscription,
      $config_factory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('acquia_contenthub.admin_settings');
    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Connection Settings'),
      '#collapsible' => TRUE,
      '#description' => $this->t('Settings for connection to Acquia Content Hub'),
    ];

    $form['settings']['hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Acquia Content Hub Hostname'),
      '#description' => $this->t('The hostname of the Acquia Content Hub API without trailing slash at end of URL, e.g. http://localhost:5000'),
      '#default_value' => $config->get('hostname'),
      '#required' => TRUE,
    ];

    $form['settings']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['settings']['secret_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $config->get('secret_key'),
    ];

    $client_name = $config->get('client_name');

    $form['settings']['hmac_version'] = [
      '#type' => 'select',
      '#title' => 'HMAC Version',
      '#options' => [
        'V1' => 'Version 1',
        'V2' => 'Version 2',
      ],
      '#default' => 'V1',
      '#description' => 'Choose which HMAC version your subscribers and publisher talk. Version 1 Is the only supported option, Version 2 is coming soon.',
      '#attributes' => [
        'disabled' => TRUE
      ],
    ];

    $readonly = empty($client_name) ? [] : ['readonly' => TRUE];

    $form['settings']['client_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Name'),
      '#default_value' => $client_name,
      '#required' => TRUE,
      '#description' => $this->t('A unique client name by which Acquia Content Hub will identify this site. The name of this client site cannot be changed once set.'),
      '#attributes' => $readonly,
    ];

    $form['settings']['origin'] = [
      '#type' => 'item',
      '#title' => $this->t("Site's Origin UUID"),
      '#markup' => $config->get('origin'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $hostname = NULL;
    if (UrlHelper::isValid($form_state->getValue('hostname'), TRUE)) {
      // Remove trailing slash at end of URL.
      $hostname = rtrim($form_state->getValue('hostname'), '/');
    }
    else {
      return $form_state->setErrorByName('hostname', $this->t('This is not a valid URL. Please insert it again.'));
    }

    $api = NULL;
    // Important. This should never validate if it is an UUID. Lift 3 does not
    // use UUIDs for the api_key but they are valid for Content Hub.
    if ($form_state->getValue('api_key')) {
      $api = $form_state->getValue('api_key');
    }
    else {
      return $form_state->setErrorByName('api_key', $this->t('Please insert an API Key.'));
    }

    $secret = NULL;
    if ($form_state->hasValue('secret_key')) {
      $secret = $form_state->getValue('secret_key');
    }
    else {
      return $form_state->setErrorByName('secret_key', $this->t('Please insert a Secret Key.'));
    }

    if ($form_state->hasValue('client_name')) {
      $client_name = $form_state->getValue('client_name');
    }
    else {
      return $form_state->setErrorByName('client_name', $this->t('Please insert a Client Name.'));
    }

    if (Uuid::isValid($form_state->getValue('origin'))) {
      $origin = $form_state->getValue('origin');
    }
    else {
      $origin = '';
    }

    // Validate that the client name does not exist yet.
    $this->clientManager->resetConnection([
      'hostname' => $hostname,
      'api' => $api,
      'secret' => $secret,
      'origin' => $origin,
    ]);

    if ($this->clientManager->isClientNameAvailable($client_name) === FALSE) {
      $message = $this->t('The client name "%name" is already being used. Please insert another one.', [
        '%name' => $client_name,
      ]);
      return $form_state->setErrorByName('client_name', $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // We assume here all inserted values have passed validation.
    // First Register the site to Content Hub.
    $client_name = $form_state->getValue('client_name');

    if ($this->contentHubSubscription->registerClient($client_name)) {
      // Registration was successful. Save the rest of the values.
      // Get the admin config.
      $config = $this->config('acquia_contenthub.admin_settings');
      if ($form_state->hasValue('hostname')) {
        // Remove trailing slash at end of URL.
        $hostname = rtrim($form_state->getValue('hostname'), '/');
        $config->set('hostname', $hostname);
      }

      if ($form_state->hasValue('api_key')) {
        $config->set('api_key', $form_state->getValue('api_key'));
      }

      if ($form_state->hasValue('secret_key')) {
        $secret = $form_state->getValue('secret_key');
        $config->set('secret_key', $secret);
      }

      $config->save();

    }
    else {
      // Get the admin config.
      $config = $this->config('acquia_contenthub.admin_settings');

      // Call drupal_get_messages, to override the dsm. Otherwise,
      // on save it will show two messages.
      drupal_get_messages();
      if (!empty($config->get('origin'))) {
        drupal_set_message($this->t('Client is already registered with Acquia Content Hub.'), 'error');
      }
      else {
        drupal_set_message($this->t('There is a problem connecting to Acquia Content Hub. Please ensure that your hostname and credentials are correct.'), 'error');
      }
    }

  }

}
