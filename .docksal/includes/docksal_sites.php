<?php

$site_alias = '';
// if browser request
if(!empty($_SERVER['SERVER_NAME'])) {
	$site_alias = $_SERVER['SERVER_NAME'];
}
// to drush on alt database, drush -l/--uri option must be set to alt_db_name--hostname 
if(PHP_SAPI == "cli") {
	$shortopts = "l:";
	$longopts  = array("uri::");
	$options = getopt($shortopts, $longopts);
	if(!empty($options['l'])) {
	  $site_alias = $options['l'];
	}elseif(!empty($options['uri'])) {
	  $site_alias = $options['uri'];
	}else{
		$cwd_parts = explode('/', getcwd());
		if(in_array('sites', $cwd_parts)) {
			$site_alias = end($cwd_parts);
		}
	}
}

if(!empty($site_alias)) {
	// check if using alternative database name convention 'alt_db_name--hostname'
	$alt_db_name = '';
	if(strpos($site_alias, '--') > 0) {
		list($alt_db_name) = explode('--', $site_alias);
	}
	// figure out real site hostname by removing 
	// alternative db name, virtual host, and other extra strings
	$remove = array('http://', 'https://', $alt_db_name.'--', '.'.DOCKSAL_VIRTUAL_HOST); # DOCKSAL_VIRTUAL_HOST set in sites.php
	$site_hostname = str_replace($remove, '', $site_alias);

	// auto-set site alias
	// ASK ERIC DAVILA WHAT THIS DOES		
	if(!empty($sites[$site_hostname])) {
		$site_hostname = $sites[$site_hostname];
	} 
	if(!file_exists(__DIR__ . '/../../docroot/sites/' . $site_hostname)) {
		$parts = explode('.', $site_hostname);
		array_shift($parts);
		$site_hostname = implode('.', $parts);
	}
	if(!empty($sites[$site_hostname])) {
		$site_hostname = $sites[$site_hostname];
	} 
	
	if(!defined('DOCKSAL_SITE_HOSTNAME')) {
		define('DOCKSAL_SITE_HOSTNAME', $site_hostname);
	}
}
$sites[$site_alias] = $site_hostname;