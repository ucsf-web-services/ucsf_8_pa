<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

function ucsf_form_views_exposed_form_alter(&$form, FormStateInterface $form_state, $form_id) {

  if ($form['#id'] == 'views-exposed-form-news-filter-page-1') {

    // Create the Advanced button.
    $form['advanced'] = [
      '#type' => 'link',
      '#url' => Url::fromUri('internal:#open'),
      '#title' => t('Advanced'),
      '#attributes' => [
        'class' => [
          'advanced'
        ]
      ]
    ];
  }
}
