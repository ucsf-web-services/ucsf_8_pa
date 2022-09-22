<?php
# DOCKSAL_VIRTUAL_HOST defined in sites.php
# DOCKSAL_SITE_HOSTNAME, DOCKSAL_DB_NAME defined in docksal_db_name.php
# got this from looking at cloud.acquia.com ucsfp1 prod environment overview url

$environment_id = '27609-26c31c4e-a5f8-4d86-926e-7b641bde4228'; 

# updated 1/30/2020 to use acquia cloud api v2

// only do this in a browser, and if auto db name conf isn't false
if(!empty($_SERVER['SERVER_NAME']) && empty($conf['docksal_auto_db_disable'])) {

  /**
   * Ask if user wants to execute auto db create and import
   */
  $db_master = new mysqli("db", "root", "root");
  if (mysqli_connect_errno()) {
    die(sprintf("Connect failed: %s\n", mysqli_connect_error()));
  }
  $output = <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Docksal: No database found</title>
    <style>
      body{
        font-family: HelveticaNeue, HelveticaNeueRoman, "HelveticaNeue-Roman", HelveticaNeue-regular, TeXGyreHerosRegular, Helvetica, "Helvetica Neue", Arial, Geneva, sans-serif;
        font-size: 1.25em;
        line-height: 1.4;
        padding: 0 3em 3em;
      }
      h1,h2{
        font-weight: normal;
      }
      h1{
        font-family: Granjon, Garamond, Baskerville, "Baskerville Old Face", "Hoefler Text", "Times New Roman", serif;
        font-size: 2em;
      }
      h2{
        margin: 2em 0 0;
      }
      table{
        border: 1px solid #4d4d4d; /* black 70 */
        border-collapse: collapse;
      }
      th{
        text-align: left;
      }
      th,td{
        border-bottom: 1px solid #4d4d4d; /* black 70 */
        vertical-align: top;
        padding: .5em;
      }
      ol{
        padding-inline-start: 0;
      }
      table ol:first-child{
        margin-top: 0;
      }
      table li p:first-child{
        margin-block-start: 0;
      }
      table thead th:nth-child(1)
      ,table tbody th
      {
        background-color: #ededee; /* gray 40 */
      }
      code{
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      }
      a{
        color: #0071ad;
        text-decoration: none;
      }
      a:link{
        border-bottom: 1px solid #0071ad;
      }
      a:visited{
        border-bottom: 1px solid #716fb2;
      }
      a:hover{
        border-bottom: 1px solid #6ea400;
        color: #000;
        background-color: #fff199;
      }
      a:focus{
        color: #000;
        background-color: #ffdd00;
        border-bottom: 2px solid #6ea400;
        outline: none;
      }
      a:active{
        color: #000;
        border-bottom: 2px solid #f26d04;
      }

      @media screen and (max-width: 800px){
        th,td{
          display: block;
        }
        thead th:nth-child(2)
        ,thead th:nth-child(3)
        ,td
        {
          margin-left: 3em;
        }
        tbody th{
          margin-top: 2em;
        }
        tbody tr:last-child td:nth-child(3){
          border-bottom: none;
        }
      }
      @media screen and (min-width: 1024px){
        body{
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          grid-column-gap: 4em;
        }
        section:nth-child(1)
        ,section:nth-child(5)
        ,section:nth-child(6)
        {
          grid-column: 1 / span 3;
        }
      }
    </style>
  </head>
  <body>
    <section>
<h1>Docksal: No database found</h1>
<p>
The database called <strong>%s</strong> doesn’t exist yet.
</p>
    </section>
    <section>
<h2>Import it using the latest Acquia Cloud backup</h2>
<ol>
<li><p>If you haven’t yet set up your Acquia Cloud API (ACAPI) key and secret, see <strong>ucsf9_multisite/.docksal/README.txt</strong>.</p></li>
<li><p><a href="?DOCKSAL_AUTO_DB=yes">Import</a>, then after a few minutes the site should appear.</p></li>
</ol>
    </section>
    <section>
<h2>Import it using a specific file</h2>
<ol>
<li><p>Place your <strong>%s.sql</strong> file into the folder at <strong>ucsf9_multisite/db</strong>.</p></li>
<li><p><a href="?DOCKSAL_AUTO_DB=yes">Import</a>, then after a few minutes the site should appear.</p></li>
</ol>
<p>Future imports will continue to use this .sql file until it is deleted or replaced.</p>
    </section>
    <section>
<h2>Import it manually</h2>
<ol>
<li><p>Use <a href="http://pma.%s">PhpMyAdmin</a></p><p>OR:</p></li>
<li><p>Use <a href="https://docs.docksal.io/fin/fin-help/#db">fin db commands</a> and/or <a href="https://docs.docksal.io/tools/acquia-drush">fin drush commands</a></p></li>
</ol>
<p>
to create an empty database called <strong>%s</strong> and import a .sql file into it.
</p>
    </section>
    <section>
<h2>Extras</h2>
<p>
The following <a href="https://docs.docksal.io/fin/custom-commands/">custom commands</a> are also available. You can find them in <strong>ucsfp1/.docksal/commands</strong>.
</p>
<table>
  <thead>
    <tr>
      <th scope="col">Command</th>
      <th scope="col">Description</th>
      <th scope="col">Usage</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">dbexport</th>
      <td>Exports the database called [dbname] to a file at <strong>ucsf9_multisite/db/[dbname].sql</strong>, overwriting any existing file. Future imports will continue to use this .sql file until it is deleted or replaced.</td>
      <td><code>fin dbexport [dbname]</code></td>
    </tr>
    <tr>
      <th scope="row">dbwipe</th>
      <td>Destroys current databases and recreates the database container and service.</td>
      <td><code>fin dbwipe</code></td>
    </tr>
  </tbody>
</table>
    </section>
    <section>
<h2>Troubleshooting, feedback, help?</h2>
<ol>
<li><p>See <strong>ucsf9_multisite/.docksal/README.md</strong>.</p></li>
<li><p>Ask in the UC Tech Slack channel called <a href="https://app.slack.com/client/T0BMNCSBA/C0EE2SEET/details/">#drucsf</a>.</p></li>
<li><p>Submit feedback or a help request at <a href="https://pharmacy.ucsf.edu/support">Website and Communications Support</a>.</p></li>
</ol>
    </section>
  </body>
</html>
EOT;

  if(empty($_REQUEST['DOCKSAL_AUTO_DB']) && $db_master->select_db(DOCKSAL_DB_NAME) === FALSE) {
    die(sprintf($output, DOCKSAL_DB_NAME, DOCKSAL_DB_NAME, DOCKSAL_VIRTUAL_HOST, DOCKSAL_DB_NAME));
  }

  /**
   * Do auto db create and import
   */
  if(!empty($_REQUEST['DOCKSAL_AUTO_DB']) && $_REQUEST['DOCKSAL_AUTO_DB'] == 'yes' && $db_master->select_db(DOCKSAL_DB_NAME) === FALSE) {

    // figure out matching database name on acquia
    $db_name = DOCKSAL_DB_NAME;
    $host_name = DOCKSAL_SITE_HOSTNAME;
    //echo "dbname $db_name".'<br>';
    //$alt_db_name = '';
    /** 
    * We already did this in docksal_db_name.php.
    * if(strpos($db_name, '--') > 0) {
    *  list($alt_db_name) = explode('--', $db_name); # check if using alternative database name convention 'alt_db_name--hostname'
    * }
    */
    $remove = array('http://', 'https://', 'dev.', 'stage.', 'test.', 'ra.', 'sand.', '.ucsf.edu', '.'.DOCKSAL_VIRTUAL_HOST);
    $acquia_db_name = str_replace($remove, '', $db_name);
    //exec("php ../scripts/guesssitename $acquia_db_name --realdbname", $output);
    //$acquia_db_name = $output[0];
    if (DOCKSAL_DEBUG) echo "Acquia_db_name: $acquia_db_name" . '<br>';

    // use repo/db/[database name].sql if it exists
    $db_import_file = sprintf("../db/%s.sql", $acquia_db_name);
    $db_import_rm_flag = FALSE;

    // if matching db name sql file doesn't exist in repo/db, download and use latest backup from acquia
    if(!file_exists($db_import_file)) {

      // get api key and secret
      $docksal_local_env = file_get_contents('../.docksal/docksal-local.env');
      $lines = explode("\n", $docksal_local_env);
      foreach($lines as $line) {
        if(strpos($line, 'SECRET_ACQUIA_CLI_KEY=') === 0) {
          $client_id = trim(str_replace('SECRET_ACQUIA_CLI_KEY=', '', $line), '"');
        }
        if(strpos($line, 'SECRET_ACQUIA_CLI_SECRET=') === 0) {
          $client_secret = trim(str_replace('SECRET_ACQUIA_CLI_SECRET=', '', $line), '"');
        }
      }

      if(empty($client_id) || empty($client_secret)) {
        die('API_KEY and API_SECRET not set in ucsf9_multisite/.docksal/docksal-local.env or could not be read. See ucsf9_multisite/.docksal/README.txt step 2.');
      }

      $ch = curl_init();

      // get access token
      curl_setopt($ch, CURLOPT_URL, "https://accounts.acquia.com/api/auth/oauth/token");
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id='.urlencode($client_id).'&client_secret='.urlencode($client_secret).'&grant_type=client_credentials');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      $json = json_decode($output);
      if(!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on Token Authorization to Acquia.<br>";
        die($output);
      }
      $access_token = $json->access_token;

      // get backup id
      // endpoint: /environments/{environmentId}/databases/{databaseName}/backups
      //$environment_id = '26c31c4e-a5f8-4d86-926e-7b641bde4228'; #got this from looking at cloud.acquia.com ucsfp1 prod environment overview url
      curl_reset($ch);
      curl_setopt($ch, CURLOPT_URL, "https://cloud.acquia.com/api/environments/$environment_id/databases/$acquia_db_name/backups?sort=-created&limit=1");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token));
      $output = curl_exec($ch);
      $json = json_decode($output);
      if(!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on environment and db resource lookup on Acquia.<br>";
        die($output);
      }
      $backup_id = $json->_embedded->items[0]->id;

      // get download link
      // endpoint: /environments/{environmentId}/databases/{databaseName}/backups/{backupId}/actions/download
      curl_reset($ch);
      curl_setopt($ch, CURLOPT_URL, "https://cloud.acquia.com/api/environments/$environment_id/databases/$acquia_db_name/backups/$backup_id/actions/download");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$access_token));
      $output = curl_exec($ch);
      $json = json_decode($output);
      if(!empty($json->error)) {
        echo $json->error . "<br>";
        echo "JSON Error on file backup download from Acquia.<br>";
        die($output);
      }
      $download_url = $json->url;
      curl_close($ch);

      // save file
      $output_document = '/tmp/'.$acquia_db_name.'.sql.gz';
      exec("wget --no-check-certificate -O $output_document \"$download_url\"");

      // extract file
      exec("/bin/gunzip /tmp/$acquia_db_name.sql.gz");

      $db_import_file = "/tmp/$acquia_db_name.sql"; # use downloaded db backup
      $db_import_rm_flag = TRUE; # mark downloaded db backup for removal after import
    }

    if(file_exists($db_import_file)) {
	    // create database
	    $sql_create_db = sprintf('CREATE DATABASE `%s`', $acquia_db_name);
	    if($db_master->query($sql_create_db) === FALSE) {
	      die('Failed to create database: '. $acquia_db_name);
	    }

	    // import db
	    $output = shell_exec("/usr/local/bin/drush -l {$host_name}  sql:query --file={$db_import_file}");

      if ($output===FALSE) {
        echo "Error importing database. Shell could not execute.";
        die();
      }

      if ($output) {
        echo '<pre>'.$output.'</pre>' . PHP_EOL;
        
        // exec(sprintf("/usr/local/bin/drush -l %s pm-disable memcache memcache_admin -y", DOCKSAL_SITE_HOSTNAME));

        $output = shell_exec(sprintf("/usr/local/bin/drush -l %s cr", DOCKSAL_SITE_HOSTNAME));
        if ($output) {
          echo $output . PHP_EOL;
        }
	      // exec(sprintf("/usr/local/bin/drush -l %s cc all", $db_name));

        if($db_import_rm_flag == TRUE) {
          echo "Clean up file /tmp/{$acquia_db_name}.sql on successful import.<br>".PHP_EOL;
          echo "If for some reason the DB import failed, you can access the download here: {$download_url}<br>".PHP_EOL;
          //exec("rm -f /tmp/$acquia_db_name.sql");
        }

        die("Importing complete, reload page to continue.");
      }
      
	  } else {
	  	die('Failed to import database: could not download or find import file.');
	  }
  }

}