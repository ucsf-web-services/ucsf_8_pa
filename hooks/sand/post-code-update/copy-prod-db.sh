#!/bin/sh
#
# Cloud Hook: post-code-update
#
# Copy the database from production.

echo "Starting the Database copy from Production."

# Map the script inputs to convenient names.
site=$1
sand_env=$2
sand_alias=$site'.'$target_env
prod_alias=$site'.prod'

echo "Site: $site"
echo "Sand Env: $target_env"
echo "Sand Drush Alias: $sand_alias"

drush sql-sync @$prod_alias @$sand_alias
