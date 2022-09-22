<?php
# This script tries to figure out what database name to use for this site.

$site_alias = '';
//$db_name = '';
if(defined('DOCKSAL_SITE_HOSTNAME') && DOCKSAL_DEBUG) {
	echo 'DOCKSAL_SITE_HOSTNAME: ' . DOCKSAL_SITE_HOSTNAME . '<br>';
}
// if browser request
if(!empty($_SERVER['SERVER_NAME'])) {
	$site_alias = $_SERVER['SERVER_NAME'];
}

if (PHP_SAPI != "cli") {
	if (strpos($_SERVER['SERVER_NAME'], '.ucsf.edu')===false && strpos($_SERVER['SERVER_NAME'], '.ucsfbenioffchildrens.org')===false) {
		echo "You're missing the domain suffix [.ucsf.edu|.ucsfbenioffchildrens.org] please go back and add it to the address bar to continue.";
		die();
	}
}

// if drush
if(PHP_SAPI == "cli") {
	$shortopts = "l:";
	$longopts  = array("uri::");
	$options = getopt($shortopts, $longopts);

	if(!empty($options['l'])) {
	  $site_alias = $options['l'];
	}elseif(!empty($options['uri'])) {
	  $site_alias = $options['uri'];
	}
	if (DOCKSAL_DEBUG) echo "site_alias" . PHP_EOL;
}

// check if using alternative database name convention 'alt_db_name--hostname'
$alt_db_name = '';
if(strpos($site_alias, '--') > 0) {
	list($alt_db_name) = explode('--', $site_alias);
}

$db_name = DOCKSAL_SITE_HOSTNAME; # DOCKSAL_SITE_HOSTNAME defined in docksal_sites.php
if(!empty($alt_db_name)) {
	$db_name = $alt_db_name;
} else {
	$db_name = explode('.', $db_name);
	$db_name = array_shift($db_name);
}

if(!defined('DOCKSAL_DB_NAME')) {
	define('DOCKSAL_DB_NAME', $db_name);
}

if (DOCKSAL_DEBUG) echo "DOCKSAL_DB_NAME: {$db_name} <br>\n";