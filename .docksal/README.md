
# Setup instructions

## Docksal environment setup

Follow [Docksal install instructions](https://docksal.io/installation)

## Project setup

1. Include in site `settings.php`:

        // Only do this for docksal local dev.
        if (getenv('IS_DOCKSAL')) {
          require('../.docksal/includes/docksal_settings.php');
        }

    This snippet (or similar) may already be in an included file.


2. For Drupal multi-sites, include in `sites/sites.php`:

        // Only do this for docksal local dev.
        if (getenv('IS_DOCKSAL')) {
          require('../.docksal/includes/docksal_sites.php');
        }

    This snippet (or similar) may already be in an included file.

3. In `.docksal/`, copy:
   - `example.docksal-local.env` => `docksal-local.env`
   - `example.docksal-local.yml` => `docksal-local.yml`.

    Put your own services and settings in the -local files.

    **Do not edit `docksal.env` or `docksal.yml`.**

4. (optional) Setup Acquia Cloud API credentials to import databases from Acquia
    Cloud.

    Create credentials at
    https://docs.acquia.com/cloud-platform/develop/api/auth/.

    Add the `API Key` and `API Secret` to `docksal-local.env`.

5. `fin start` to start Docksal and docker containers.
  `fin up` whenever changes are made to docksal env/yml files to reload new
  settings.

6. Site is available at `http://[VIRTUAL_HOST]`.

    For multi-sites, sites are available at
    `http://[SITE_DIRECTORY].[VIRTUAL_HOST]`.

    Example 1: `http://giving.ucsf.edu.ucsf9-multisite.docksal.site`

    Example 2: `http://cls.intranet.docksal.site`

    `VIRTUAL_HOST` is preset in `docksal.env`.

    If you want to use a different value, set it in `docksal-local.env`.


## Other services (if installed)

- phpMyAdmin ui available at `http://pma.[VIRTUAL_HOST]/`.

- Solr ui available at `http://solr.[VIRTUAL_HOST]/`.

  Enable the solr instance in `docksal-local.yml`.

  The project is setup to override config of Acquia Search Solr server with
  a local solr core named `acquia_copy`. If you want the override to use a
  different solr core, set `SOLR_CORE` to your new core name in
  `docksal-local.env`, put the new core configset in `.docksal/etc/solr/`
  with the same folder name, and `fin up` to load the changes.


## Documentation

The full Docksal documentation is at https://docs.docksal.io/.

`fin help` will get you a list of Docksal commands.
Also available at https://docs.docksal.io/fin/fin-help/.


## Troubleshooting

Here's a list of things you can try:

-- `fin restart` the docker containers

-- Restart Docker

-- Restart the host machine

-- `fin project reset` deletes existing project containers and volumes and
  recreates them

-- Ask for help in Slack in the UC Tech channel #drucsf
  https://app.slack.com/client/T0BMNCSBA/C0EE2SEET/details.

### VPN:

Known issue with Pulse Secure VPN alters NFS in the rpcbind service.

This at least affects Mac OSX, other OS uncertain.

The issue causes some Docksal containers to be unable to start because it
can't mount the nfs volumes.

The solutions are:

1. restart host machine, or

2. kill the rpcbind service and restart nfs service which will in turn restart
  the rpcbind service.
