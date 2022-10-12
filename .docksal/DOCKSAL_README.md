# Setup instructions

## Docksal environment setup

Follow [Docksal install instructions](https://docksal.io/installation)

## Project setup

1. Include in bottom of each site `settings.php`:

        // Only do this for a docksal request.
        if (substr($_SERVER['VIRTUAL_HOST'] ?? '', -13) == '.docksal.site') {
          require('../.docksal/includes/docksal_settings.php');
        }

    This snippet (or similar) may already be in an included file.


2. For Drupal multi-sites, include in bottom of `sites/sites.php`:

        // Only do this for a docksal request.
        if (substr($_SERVER['VIRTUAL_HOST'] ?? '', -13) == '.docksal.site') {
          require('../.docksal/includes/docksal_sites.php');
        }

    This snippet (or similar) may already be in an included file.

3. Copy `PROJECT_ROOT/.docksal/example.docksal-local.env` to `docksal-local.env` and `PROJECT_ROOT/.docksal/example.docksal-local.yml` to `docksal-local.yml`.

    Put your own services and settings in docksal-local.env and docksal-local.yml.

    **Do not edit `docksal.env` or `docksal.yml`.**

4. (optional) Setup Acquia Cloud API credentials to import databases from Acquia Cloud.

    Create credentials at https://docs.acquia.com/cloud-platform/develop/api/auth/.

    Add the `API Key` and `API Secret` to docksal-local.env.

5. `fin start` to start Docksal and docker containers.

6. Site is available at http://[VIRTUAL_HOST].

    VIRTUAL_HOST is preset in docksal.env.

    If you want to use a different value, set it in docksal-local.env.

    For multi-sites, each site is available at http://[SITE_DIRECTORY].[VIRTUAL_HOST].

    Example 1: `http://giving.ucsf.edu.ucsf9-multisite.docksal.site`

    Example 2: `http://cls.intranet.docksal.site`

## Other services (if installed)

- phpMyAdmin ui available at `http://pma.[VIRTUAL_HOST]/`.

- Solr ui available at `http://solr.[VIRTUAL_HOST]/solr/`.
  Apachesolr module setting use solr server http://solr:8983/solr.

## Documentation

The full Docksal documentation is at https://docs.docksal.io/.

`fin help` will get you a list of Docksal commands.
Also available at https://docs.docksal.io/fin/fin-help/.


## Troubleshooting

Here's a list of things you can try:

-- `fin restart` the docker containers

-- Restart Docker

-- Restart the host machine

-- `fin stop`, delete existing project containers and volumes through the Docker UI, then recreate `fin start`

-- Ask for help in Slack in the UC Tech channel #drucsf https://app.slack.com/client/T0BMNCSBA/C0EE2SEET/details.
