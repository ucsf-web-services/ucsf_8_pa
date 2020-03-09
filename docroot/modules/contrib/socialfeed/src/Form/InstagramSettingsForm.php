<?php

namespace Drupal\socialfeed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;

/**
 * Class InstagramSettingsForm.
 *
 * @package Drupal\socialfeed\Form
 */
class InstagramSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'socialfeed.instagramsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'instagram_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('socialfeed.instagramsettings');
    $redirect_uri = \Drupal::request()->getSchemeAndHttpHost() . '/socialfeed/instagram/auth';

    $form['header']['#markup'] = $this->t('To get the App ID and App Secret you need to follow steps 1-3 <a href="@site" target="@blank">here</a> (command line not needed). Then add them here and save the page. Once the credentials are saved, a link to generate the Access Token will appear.', [
      '@site' => 'https://developers.facebook.com/docs/instagram-basic-display-api/getting-started',
      '@blank' => '_blank',
    ]);

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#description' => $this->t('App ID from Instagram account'),
      '#default_value' => $config->get('client_id'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#description' => $this->t('App Secret from Instagram account'),
      '#default_value' => $config->get('app_secret'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['redirect_uri'] = [
      '#type' => 'item',
      '#title' => $this->t('Redirect URI'),
      '#markup' => $redirect_uri,
      '#default_value' => $redirect_uri,
    ];

    $token_message = $this->t('Once the App ID and Secret Key have been saved, a link to generate the Access Key will appear.');
    if ($config->get('client_id')) {
      $instagram = new InstagramBasicDisplay([
        'appId' => $config->get('client_id'),
        'appSecret' => $config->get('app_secret'),
        'redirectUri' => $redirect_uri,
      ]);

      $token_message = $this->t('<a href="@this" target="_blank">Login with Instagram to generate the Access Token</a>', [
        '@this' => Url::fromUri($instagram->getLoginUrl())->toString(),
      ]);
    }

    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#field_prefix' => '<div>' . $token_message . '</div>',
      '#description' => $this->t('This access token will automatically be renewed before the current one expires in 60 days.'),
      '#default_value' => $config->get('access_token'),
      '#size' => 60,
      '#maxlength' => 300,
    ];

    $form['picture_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Default Picture Count'),
      '#default_value' => $config->get('picture_count'),
      '#size' => 60,
      '#maxlength' => 100,
      '#min' => 1,
    ];
    $form['video_thumbnail'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show video thumbnails instead of actual videos'),
      '#default_value' => $config->get('video_thumbnail'),
    ];
    $form['post_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show post URL'),
      '#default_value' => $config->get('post_link'),
    ];

    if ($config->get('access_token')) {
      $form['feed'] = [
        '#type' => 'item',
        '#title' => $this->t('Feed URL'),
        '#markup' => $this->t('https://graph.instagram.com/me/media?fields=id,media_type,media_url,username,timestamp&limit=@picture_count&access_token=@access_token',
          [
            '@access_token' => $config->get('access_token'),
            '@picture_count' => $config->get('picture_count'),
          ]
        ),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('socialfeed.instagramsettings');
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
