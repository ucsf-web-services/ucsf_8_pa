2021-01-27
----------
Updated to be compatible with Docksal 1.15.0/1.15.1 (fin version 1.102.0/1.103.0) and Docker 2.5.0.1.

To upgrade Docker for Mac, download at https://docs.docker.com/docker-for-mac/release-notes/#docker-desktop-community-2501.
Removing existing containers and volumes might be necessary.
It might be faster to just reset Docker to factory defaults. Go to Docker dashboard and click on the bug icon in the upper right.

**WARNING:** Doing so will remove existing data so BACKUP YOUR DB/WORK before removing containers/volumes or resetting!

To upgrade Docksal: "fin update" while Docker is running.

Also add these Docksal variables to the global config:

>    fin config set --global DOCKSAL_DNS_DOMAIN="docksal.site";
>    
>    fin config set --global DOCKSAL_NO_DNS_RESOLVER=1;
>    
>    fin system reset;

The local dev domains are now accessible at "*.ucsfp1.docksal.site" instead of "*.[repo folder name].docksal".
eg. http://dev.pharmacy.ucsf.edu.ucsfp1.docksal.site/, http://pma.ucsfp1.docksal.site/




# Setup instructions

## Docksal environment setup

NOTE:
Do not go above the Docker version mentioned in the Docksal release notes https://github.com/docksal/docksal/releases.


1. Follow [Docksal install instructions](https://docksal.io/installation)

2. With Docker started, set Docksal global config variables

>     fin config set --global DOCKSAL_DNS_DOMAIN="docksal.site";
>     fin config set --global DOCKSAL_NO_DNS_RESOLVER=1;

3. Or, modify ~/.docksal/docksal.env to include DOCKSAL_DNS_DOMAIN="docksal.site" and DOCKSAL_NO_DNS_RESOLVER=1 on its own lines

>     fin system reset;



## Project setup

1. Check that your site's settings.php has an include or require statement for the ucsfp1/docroot/sites/all/ucsfp_acquia_settings.inc file or ucsfp1/.docksal/includes/docksal_settings.php file at the bottom.

    If it doesn't, add only 1 of either:

>     //load ucsfp_acquia_settings (memcache, newrelic, fast404, etc)
>     require_once("./sites/all/ucsfp_acquia_settings.inc");

  or,
     

>     if(!empty($_SERVER['VIRTUAL_HOST']) && $_SERVER['VIRTUAL_HOST'] == DOCKSAL_VIRTUAL_HOST) {
>       require('../.docksal/includes/docksal_settings.php'); # do this for docksal local env
>     }


2. Initialize the project

    cd [ucsfp1 git repo folder];

    fin init;

3. (optional) Setup Acquia Cloud API credentials for automatic database import. Create an API Token at https://cloud.acquia.com/a/profile/tokens and copy the API Key and API Secret.

   ` fin config set --env=local SECRET_ACQUIA_CLI_KEY="[API Key]";`

   ` fin config set --env=local SECRET_ACQUIA_CLI_SECRET="[API Secret]";`

  Or, just modify ucsfp1/.docksal/docksal-local.env to include API_KEY="[API Key] and API_SECRET="[API Secret]" on its own lines

 `   fin p restart;`

3. Site is available at:

    http://[site].ucsf9-multisite.docksal.site/ #*.ucsf8.docksal.site is the correct domain regardless of git repo folder name

    eg. http://dev.pharmacy.ucsf.edu.ucsf9-multisite.docksal.site/ for dev.pharmacy.ucsf.edu

    phpMyAdmin ui available at http://pma.ucsf9-multisite.docksal.site/.
    Solr ui available at http://solr.ucsf9-multisite.docksal.site/solr/.
      - Apachesolr module setting use solr server http://solr:8983/solr.


## Recommended

It's a good idea to read the `fin help` documentation (https://docs.docksal.io/fin/fin-help/).

The most commonly used commands are probably `fin project`, `fin db`, and `fin drush`.

`fin project start` to start up the docksal stack (and remember to start Docker Desktop).

`fin db drop [dbname]` is useful to clear out a site before doing a fresh auto-import.

And note that `fin drush` is just a shortcut for running drush commands in the cli container from the host machine, so it's best to be within the drupal docroot directory or in a site directory.

Additional advanced use cases documentation is TODO.

	Contact @jameshuang-ucsf in uctech slack if you have questions.