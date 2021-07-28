<?php

namespace Drupal\applenews\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configures applenews settings for this site.
 *
 * @internal
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\applenews\SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   *   The aggregator processor plugin manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TranslationInterface $string_translation) {
    parent::__construct($config_factory);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'applenews_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['applenews.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('applenews.settings');
    $curl_opts = $config->get('curl_options');

    $form['credentials'] = [
      '#type' => 'fieldset',
      '#title' => t('Apple News credentials'),
      '#description' => t('You can find your connection information in News Publisher. Go to Channel Info tab to view your API Key and Channel ID.'),
    ];

    $form['credentials']['endpoint'] = [
      '#type' => 'url',
      '#title' => t('API endpoint URL'),
      '#default_value' => $config->get('endpoint'),
      '#description' => t('Publisher API endpoint URL'),
      '#required' => TRUE,
    ];

    $form['credentials']['api_key'] = [
      '#type' => 'textfield',
      '#title' => t('API key'),
      '#default_value' => $config->get('api_key'),
      '#description' => t('Publisher API key.'),
      '#required' => TRUE,
    ];

    $form['credentials']['api_secret'] = [
      '#type' => 'password',
      '#title' => t('API secret'),
      '#default_value' => $config->get('api_secret'),
      '#description' => t('Publisher API secret key. This can be blank if the secret is set through settings.php.'),
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => t('Advanced'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['advanced']['ssl'] = [
      '#type' => 'checkbox',
      '#title' => t('SSL verification'),
      '#default_value' => $curl_opts['ssl'],
      '#description' => t('Disabling verification makes the communication insecure.'),
    ];

    $form['advanced']['proxy'] = [
      '#type' => 'textfield',
      '#title' => t('Proxy address'),
      '#default_value' => $curl_opts['proxy'],
      '#description' => t('Proxy server address.'),
    ];

    $form['advanced']['proxy_port'] = [
      '#type' => 'textfield',
      '#title' => t('Proxy port'),
      '#default_value' => $curl_opts['proxy_port'],
      '#size' => 10,
      '#description' => t('Proxy server port number.'),
    ];

    // Show delete button only when all the fields are prepopulated.
    if (!empty($endpoint) && !empty($api_key) && !empty($api_secret)) {
      $form['delete_config'] = [
        '#markup' => $this->t('<a href="!url">Delete configuration</a>', ['!url' => Url::fromUri('/admin/config/content/applenews/settings/delete')]),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $endpoint = $form_state->getValue('endpoint');
    if (UrlHelper::isValid($endpoint, TRUE)) {
      // Trim any trailing '/' for consistency.
      $endpoint = preg_replace('~/$~', '', $endpoint);
      $form_state->setValue('endpoint', $endpoint);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('applenews.settings');
    // Only save the secret if there is one set.
    $secret = trim($form_state->getValue('api_secret'));
    if (!empty($secret)) {
      $config->set('api_secret', $secret);
    }
    $config
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('curl_options.ssl', $form_state->getValue('ssl'))
      ->set('curl_options.proxy', $form_state->getValue('proxy'))
      ->set('curl_options.proxy_port', $form_state->getValue('proxy_port'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
