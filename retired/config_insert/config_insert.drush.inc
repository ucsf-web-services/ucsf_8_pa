<?php
/**
 * @file ucsf.drush.inc
 *
 * Drush custom commands for managing UCSF's Drupal environments.
 */


/**
 * Implements ucsf_drush_command().
 */
function config_insert_drush_command() {
  $items = array();

  $items['quiet'] = array(
    'description' => 'This does not do anything.');

  $items['insert_config'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'description' => 'insters custom configuration changes into drupal environments',
  );

  return $items;

}

/**
 * Implements drush_COMMANDFILE_COMMANDNAME().
 *
 * Does nothing but itself, but may be useful if run via the <code>iterator</code> script.
 */
function drush_config_insert_quiet() {}

function drush_config_insert_insert_config() {

  print_r("testing sucessful");
}
