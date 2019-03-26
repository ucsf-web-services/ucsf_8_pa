<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

function ucsf_form_views_exposed_form_alter(&$form, FormStateInterface $form_state, $form_id)
{

  if ($form['#id'] == 'views-exposed-form-news-filter-page-1') {

    // Set globals.
    global $pager_total_items;

    // Attach library.
    $form['#attached']['library'][] = 'ucsf/search_news';

    // Create top container.
    $form['top'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'search-filter__top'
        ]
      ],
      '#weight' => -10,
    ];

    // Organize and build items in top container.
    $combine = $form['combine'];
    $actions = $form['actions'];
    $form['top']['combine'] = $combine;
    $form['top']['actions'] = $actions;

    // Top Container - Advanced button.
    $form['top']['advanced'] = [
      '#type' => 'link',
      '#url' => Url::fromUri('internal:#open'),
      '#title' => t('Advanced'),
      '#attributes' => [
        'class' => [
          'search-filter__advanced'
        ]
      ]
    ];

    // Top Container - Result Count.
    $result_count = $pager_total_items[0] ? Html::escape($pager_total_items[0]) : '0';
    $form['top']['count'] = [
      '#type' => 'container',
      'count_markup' => ['#markup' => $result_count . ' Results <span class="desktop-results">in the UCSF News Center<span>'],
      '#attributes' => [
        'class' => [
          'count'
        ]
      ]
    ];

    // Create dropdown container.
    $form['dropdown'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'search-filter__dropdown'
        ],
      ],
      '#weight' => -10,
    ];

    // Organize and build items in dropdown container.
    $form['tid']['#title'] = $form['#info']['filter-tid']['label'];
    $form['tid_1']['#title'] = $form['#info']['filter-tid_1']['label'];
    $form['field_primary_area_target_id']['#title'] = $form['#info']['filter-field_primary_area_target_id']['label'];
    $form['field_topics_target_id']['#title'] = $form['#info']['filter-field_topics_target_id']['label'];
    $form['field_date_and_time_value_1']['#title'] = $form['#info']['filter-field_date_and_time_value_1']['label'];
    $form['field_date_and_time_value_2']['#title'] = $form['#info']['filter-field_date_and_time_value_2']['label'];

    $tid = $form['tid'];
    $tid_1 = $form['tid_1'];
    $field_primary_area_target_id = $form['field_primary_area_target_id'];
    $field_topics_target_id = $form['field_topics_target_id'];
    $field_date_and_time_value_1 = $form['field_date_and_time_value_1'];
    $field_date_and_time_value_2 = $form['field_date_and_time_value_2'];

    $form['dropdown']['tid'] = $tid; // Type of Article
    $form['dropdown']['field_primary_area_target_id'] = $field_primary_area_target_id; // Area of Focus
    $form['dropdown']['pub_year']['#type'] = 'fieldset';
    $form['dropdown']['pub_year']['#title'] = t('Date of Publication');
    $form['dropdown']['pub_year']['#attributes'] = ['class' => ['fieldgroup']];
    $form['dropdown']['pub_year']['field_date_and_time_value_1'] = $field_date_and_time_value_1;
    $form['dropdown']['pub_year']['field_date_and_time_value_2'] = $field_date_and_time_value_2;
    $form['dropdown']['tid_1'] = $tid_1; // Health and Science
    $form['dropdown']['field_topics_target_id'] = $field_topics_target_id; //Campus Topics
    $form['dropdown']['actions'] = $actions;

    // Unset duplicate arrays.
    unset($form['combine']);
    unset($form['tid']);
    unset($form['tid_1']);
    unset($form['field_primary_area_target_id']);
    unset($form['field_topics_target_id']);
    unset($form['field_date_and_time_value_1']);
    unset($form['field_date_and_time_value_2']);
    unset($form['actions']);
  }
}
