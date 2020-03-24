#!/bin/sh
#
# Cloud Hook: post-code-update
#
# Copy the database from production.

echo "Starting the Database copy from Production."

# Map the script inputs to convenient names.
site=$1
target_env=$2
source_branch=$3
sand_alias=$site'.'$target_env
prod_alias=$site'.prod'

echo "Site: $site"
echo "Target Env: $target_env"
echo "Source Branch: $source_branch"
echo "Sand Drush Alias: $sand_alias"

# drush sql-sync @$prod_alias @$sand_alias
