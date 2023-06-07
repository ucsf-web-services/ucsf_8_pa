<?php

/**
 * Dynamically add site alias and set database name if the request url matches
 * our convention for a site utilizing the Drupal multi-site feature.
 *
 * For local db, we set the multi-site db name to its site directory name.
 *
 * Our convention for docksal uses the multi-site directory name as a subdomain
 * of the docksal virtual hostname.
 *
 * Example 1: http://giving.ucsf.edu.ucsfp9-multisite.docksal.site/
 *
 * 'giving.ucs.edu' would have a matching directory in sites.
 *
 * 'ucsfp9-multisite.docksal.site' would be the virtual host defined in
 * .docksal/docksal.env.
 *
 * To use this feature, include this file at the bottom of sites/sites.php.
 */

// Only do this for a docksal request.
if (getenv('IS_DOCKSAL')) {

  // For browser requests to a multi-site.
  if (stripos($_SERVER['SERVER_NAME'] ?? '', '.' . $_SERVER['VIRTUAL_HOST']) !== FALSE) {
    $directory_name = str_replace('.' . $_SERVER['VIRTUAL_HOST'], '', $_SERVER['SERVER_NAME']);
    // Set alias.
    $sites[$_SERVER['SERVER_NAME']] = $directory_name;
    // Set database name.
    putenv('MYSQL_DATABASE=' . $directory_name);
  }

  // Drush commands must use the -l/--uri option to target a multi-site.
  if (PHP_SAPI == "cli") {
    $shortopts = "l:";
    $longopts  = ["uri::"];
    $options = getopt($shortopts, $longopts);
    if (!empty($options['l'])) {
      putenv('MYSQL_DATABASE=' . $options['l']);
    }
    elseif (!empty($options['uri'])) {
      putenv('MYSQL_DATABASE=' . $options['uri']);
    }
  }
}
