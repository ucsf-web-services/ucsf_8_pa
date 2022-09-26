<?php

/**
 * Adds docksal required site settings.
 *
 * This file should be included at the bottom each sites 'settings.php'.
 */

// Only do this for a docksal request.
if (substr($_SERVER['VIRTUAL_HOST'] ?? '', -13) == '.docksal.site') {

  // Set database name for 'default' site.
  // The default site hostname is the same as the docksal virtual host.
  // For multi-sites, see ./docksal/includes/docksal_sites.php.
  if (($_SERVER['SERVER_NAME'] ?? '') == $_SERVER['VIRTUAL_HOST']) {
    putenv('MYSQL_DATABASE=' . $_SERVER['VIRTUAL_HOST']);
  }

  // Performs database create and import from acquia or file.
  require('../.docksal/includes/docksal_auto_db.php');

  // Do not change unless you know better.
  $databases['default']['default'] = [
    'database' => getenv('MYSQL_DATABASE'),
    'username' => 'root',
    'password' => 'root',
    'host' => 'db',
    'driver' => 'mysql',
  ];

  # Trusted host configuration.
  $settings['trusted_host_patterns'][] = '.+';
  // $settings['trusted_host_patterns'][] = '^.+\.ucsf\.edu\.ucsf9-multisite\.docksal\.site$';
  // $settings['trusted_host_patterns'][] = '^.+\.ucsfbenioffchildrens\.org\.ucsf9-multisite\.docksal\.site$';

  # File system settings.
  $setting['file_temporary_path'] = '/tmp';
  # Workaround for permission issues with NFS shares in Vagrant
  $setting['file_chmod_directory'] = 0777;
  $setting['file_chmod_file'] = 0666;

  # Reverse proxy configuration (Docksal's vhost-proxy)
  $conf['reverse_proxy'] = TRUE;
  $conf['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);
  // HTTPS behind reverse-proxy
  if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' &&
    !empty($setting['reverse_proxy']) && in_array($_SERVER['REMOTE_ADDR'], $setting['reverse_proxy_addresses'])
  ) {
    $_SERVER['HTTPS'] = 'on';
    // This is hardcoded because there is no header specifying the original port.
    $_SERVER['SERVER_PORT'] = 443;
  }
}
