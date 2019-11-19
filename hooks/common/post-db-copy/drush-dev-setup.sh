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

# Enable Stage File Proxy so that files don't need to be copied from Prod.
drush8 @$drush_alias en stage_file_proxy
drush8 @$drush_alias config-set stage_file_proxy.settings origin "https://www.ucsf.edu"

# Clear the cache.
drush @$drush_alias cr
