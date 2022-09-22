<?php
define('DOCKSAL_DEBUG', false);



if(defined('DOCKSAL_SITE_HOSTNAME')) { # DOCKSAL_SITE_HOSTNAME defined in docksal_sites.php
	if (DOCKSAL_DEBUG) echo "DOCKSAL_SITE_HOSTNAME:" . DOCKSAL_SITE_HOSTNAME . "<br>";
  require('../.docksal/includes/docksal_db_name.php'); # Script to determine db name for this site
  require('../.docksal/includes/docksal_auto_db.php'); # Script to do auto db create and import

  # DOCKSAL_SITE_HOSTNAME defined in docksal_db_name.php
  $docksal_env = 'prod';
  $docksal_env = (stripos(DOCKSAL_SITE_HOSTNAME, 'dev.') !== FALSE) ? 'dev' : $docksal_env;
  $docksal_env = (stripos(DOCKSAL_SITE_HOSTNAME, 'sand.') !== FALSE) ? 'sand' : $docksal_env;
  $docksal_env = (stripos(DOCKSAL_SITE_HOSTNAME, 'ra.') !== FALSE) ? 'ra' : $docksal_env;
  $docksal_env = (stripos(DOCKSAL_SITE_HOSTNAME, 'stage.') !== FALSE) ? 'stage' : $docksal_env;
  $docksal_env = (stripos(DOCKSAL_SITE_HOSTNAME, 'test.') !== FALSE) ? 'test' : $docksal_env;
  $_ENV['DOCKSAL_SITE_ENVIRONMENT'] = $docksal_env;

  if(empty($setting['docksal_auto_db_disable'])) {
    
    $databases['default']['default'] = array (
      'database' => DOCKSAL_DB_NAME, # DOCKSAL_DB_NAME set in docksal_db_name.php
      'username' => 'root',
      'password' => 'root',
      'host' => 'db',
      'driver' => 'mysql',
    );

  }

  $settings['trusted_host_patterns'][] = '^.+\.ucsf\.edu\.ucsf9-multisite\.docksal\.site$';
  $settings['trusted_host_patterns'][] = '^.+\.ucsfbenioffchildrens\.org\.ucsf9-multisite\.docksal\.site$';
  # File system settings.
  $setting['file_temporary_path'] = '/tmp';
  # Workaround for permission issues with NFS shares in Vagrant
  $setting['file_chmod_directory'] = 0777;
  $setting['file_chmod_file'] = 0666;

  # Reverse proxy configuration (Docksal's vhost-proxy)
  $setting['reverse_proxy'] = TRUE;
  $remote = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
  $setting['reverse_proxy_addresses'] = array($remote);
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