#!/bin/sh
#
# Cloud Hook: post-db-copy
#
# Setup On-Demand Environment after the database has been pulled from Prod. Run
# drush cache-clear all in the target environment.


# Map the script inputs to convenient names.
site=$1
target_env=$2
drush_alias=$site'.'$target_env

echo "Site: $site"
echo "Target Env: $target_env"

# Only run if target environment is not Prod.
if [ "$target_env" != 'prod' ]; then
  # Enable Stage File Proxy so that files don't need to be copied from Prod.
  echo "Enabling Stage File Proxy"
  drush @$drush_alias en stage_file_proxy -y
  drush @$drush_alias config-set stage_file_proxy.settings origin "https://www.ucsf.edu" -y

  # Disable Google Analitics if target environment is not Prod.
  echo "Disabling Google Analitics"
  drush @$drush_alias pm-uninstall google_analytics -y
fi

# Clear the cache.
echo "Clearing Cache"
drush @$drush_alias cr

# Update database.
echo "Updating Database"
drush @$drush_alias updb
