<?php

// Don't do this in command line.
if (PHP_SAPI != "cli") {

  mysqli_report(MYSQLI_REPORT_ERROR);

  $db_master = new mysqli("db", "root", "root");
  if (mysqli_connect_errno()) {
    die (sprintf("Connect failed: %s\n", mysqli_connect_error()));
  }

  // Discontinue and do nothing if database already exists.
  $db_name = getenv('MYSQL_DATABASE');
  if ($db_master->select_db($db_name) !== FALSE) {
    return true;
  }

  // Initialize some variables.
  $server_name = $_SERVER['SERVER_NAME'];
  $virtual_host = $_SERVER['VIRTUAL_HOST'];

  $environment_id = getenv('ACQUIA_ENV_ID');
  $client_id = getenv('API_KEY');
  $client_secret = getenv('API_SECRET');

  $db_dir = __DIR__ . '/../db/'; # .docksal/db
  $db_files = array_filter(scandir($db_dir), fn($filename):string => (strtolower(substr($filename, -4)) == '.sql'));
  $db_files_html = ''; # init empty
  foreach ($db_files as $file) {
    $db_files_html .= "<a href='?docksal_import=$file'>$file</a><br><br>";
  }
  $file_name = ''; # init empty
  $download_url = ''; # init empty
  $db_import_file = ''; # init empty

  // Display import options to user.
  if (empty($_GET['docksal_import'])) {
    echo "
      Welcome to docksal database import.<br>
      The database name for this site in docksal db will be <b>`$db_name`</b>.<br>
      Choose a sql backup to import.<br>
      <br>
      From acquia cloud:<br>
      <br>
      <a href='?docksal_import=ac'>See database list and select</a><br>
      <br>
      From local sql file in .docksal/db:<br>
      <br>
      $db_files_html
    ";
    die();
  }

  if ($_GET['docksal_import'] == 'ac') {
    if (empty($client_id) || empty($client_secret) || empty($environment_id)) {
      echo "Some required variables missing.<br>";
      echo "Set the API_KEY, API_SECRET in .docksal/docksal-local.env and <b>fin up</b>.<br>";
      echo "If ACQUIA_ENV_ID is not set in .docksal/docksal.env, set it in .docksal-local.env.<br>";
      echo "<a href='//$server_name'>Go back</a><br>";
      die();
    }

    $ch = curl_init();
    // get access token
    curl_setopt($ch, CURLOPT_URL, "https://accounts.acquia.com/api/auth/oauth/token");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id='.urlencode($client_id).'&client_secret='.urlencode($client_secret).'&grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    $json = json_decode($output);
    if (!empty($json->error)) {
      echo $json->error . "<br>";
      echo "JSON Error on Token Authorization to Acquia.<br>";
      die($output);
    }
    $access_token = $json->access_token;

    if (empty($_GET['ac_db_name'])) {
      // get list of databases
      // endpoint: /environments/{environmentId}/databases
      curl_reset($ch);
      curl_setopt($ch, CURLOPT_URL, "https://cloud.acquia.com/api/environments/$environment_id/databases");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      $json = json_decode($output);
      if (!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on databases list lookup on Acquia.<br>";
        die($output);
      }
      $databases_list_html = '';
      foreach($json->_embedded->items as $db) {
        $databases_list_html .= "<a href='?docksal_import=ac&ac_db_name={$db->name}'>{$db->name}</a><br><br>";
      }

      echo "Select the acquia database to import.<br>
        The most recent backup will be imported into <b>`$db_name`</b>.<br>
        A copy of the sql backup file will also be downloaded to .docksal/db.<br>
        <br>
        $databases_list_html
      ";
      die();
    }
    else {
      $acquia_db_name = $_GET['ac_db_name'];

      // get backup id
      // endpoint: /environments/{environmentId}/databases/{databaseName}/backups
      curl_reset($ch);
      curl_setopt($ch, CURLOPT_URL, "https://cloud.acquia.com/api/environments/$environment_id/databases/$acquia_db_name/backups?sort=-created&limit=1");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      $json = json_decode($output);
      if (!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on environment and db resource lookup on Acquia.<br>";
        die($output);
      }
      $backup_id = $json->_embedded->items[0]->id;
      // get download link
      // endpoint: /environments/{environmentId}/databases/{databaseName}/backups/{backupId}/actions/download
      curl_reset($ch);
      curl_setopt($ch, CURLOPT_URL, "https://cloud.acquia.com/api/environments/$environment_id/databases/$acquia_db_name/backups/$backup_id/actions/download");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      $json = json_decode($output);
      if (!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on file backup download from Acquia.<br>";
        die($output);
      }
      $download_url = $json->url;

      // setup local filename for downloaded file
      $url_query = parse_url($download_url, PHP_URL_QUERY);
      parse_str($url_query, $query_vars);
      $acquia_file_path = $query_vars['d'];
      $file_name = end(explode('/', $acquia_file_path));
      $db_import_file = $db_dir . $file_name;
      // download file
      exec("wget --no-check-certificate -O $db_import_file \"$download_url\"");
      // decompress gz file to sql file (assume download is gz)
      exec("gunzip $db_import_file");
      // set new sql filename
      $db_import_file = str_replace('.gz', '', $db_import_file);
    }

    curl_close($ch);
  }
  else {
    $file_name = $_GET['docksal_import'];
    $db_import_file = $db_dir . $file_name;
  }

  if (!file_exists($db_import_file)) {
    echo "File does not exist: .docksal/db/$file_name<br>";
    if ($download_url) echo "You can download the database backup here: {$download_url}<br>";
    echo "<a href='//$server_name'>Go back</a><br>";
    die();
  }

  // import file is determined now try to import
  if (file_exists($db_import_file)) {
    // create database
    if ($db_master->query("CREATE DATABASE `$db_name`") === FALSE) {
      echo "Failed to create database: $db_name";
      die();
    }

    // import
    $drush_site_uri = $db_name; # our convention is to use site uri as the local database name
    $command = "mysql --user=root --password=root --force {$drush_site_uri} < {$db_import_file}";
    $output = shell_exec($command);

    // revert earlier database create if sql import fails
    if ($output === FALSE) {
      $db_master->query("DROP DATABASE `$db_name`");
      echo "Error importing with command: <b>$command</b><br>";
      die();
    }
    echo '<pre>'.$output.'</pre>';

    $command = "drush -l $drush_site_uri cr";
    exec($command);

    $modules_enabled = getenv('MODULES_ENABLED');
    if (!empty($modules_enabled)) {
      foreach(explode(' ', $modules_enabled) as $module_name) {
        $command = "drush -l $drush_site_uri pm-enable $module_name";
        exec($command);
      }
    }

    $modules_disabled = getenv('MODULES_DISABLED');
    if (!empty($modules_disabled)) {
      foreach(explode(' ', $modules_disabled) as $module_name) {
        $command = "drush -l $drush_site_uri pm-uninstall $module_name";
        exec($command);
      }
    }

    echo "Import complete.<br><br>";

    echo "<a href='//{$server_name}'>Site home</a><br><br>";

    $command = "drush -l $drush_site_uri uli";
    $output = shell_exec($command);
    if ($output) {
      $admin_url = str_replace($drush_site_uri, $server_name, $output);
      echo "<a href='$admin_url'>Site admin one-time login</a><br><br>";
    }

    die();
  }
}
