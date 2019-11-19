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

test_env="ode5"

# Check if this is an On-Demand Environmnet starting with "ode" prefix.
if [[ $test_env = ode* ]]; then
  # Enable Stage File Proxy so that files don't need to be copied from Prod.
  echo "Enabling Stage File Proxy"
  # drush @$drush_alias en stage_file_proxy -y
  # drush @$drush_alias config-set stage_file_proxy.settings origin "https://www.ucsf.edu" -y
fi

# Clear the cache.
echo "Clearing Cache"
# drush @$drush_alias cr
