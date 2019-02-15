<?php
/**
 * Created by UCSF.
 * User: eguerin
 * Date: 1/24/19
 * Time: 11:44 AM
 */

/**
 * Implements hook_page_attachments().
 **/
function ucsf_wysiwyg_page_attachments(&$page) {
  //$page['#attached']['library'][] = 'ckeditor_y3ti_plugins/webcomponents';
  //$page['#attached']['library'][] = 'ckeditor_y3ti_plugins/buttonCss';
  //$page['#attached']['library'][] = 'ckeditor_y3ti_plugins/extraScript';
}

/**
 * Implements hook_editor_js_settings_alter
 */
function ucsf_wysiwyg_editor_js_settings_alter(array &$settings) {
  global $base_url;
  //$settings['editor']['formats']['sf_full_html']['editorSettings']['contentsCss'][] = $base_url.'/'.drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/css/CKEditorExtra.css';
  //$settings['editor']['formats']['sf_full_html']['editorSettings']['customConfig'] = $base_url.'/'.drupal_get_path('module', 'ckeditor_y3ti_plugins') . '/js/customCkeditor.config.js';
}