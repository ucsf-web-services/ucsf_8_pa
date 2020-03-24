#!/bin/sh
#
# Cloud Hook: post-code-update
#
# Copy the database from production.

echo "Starting the Database copy from Production."

# Map the script inputs to convenient names.
site=$1
sand_alias=$site'.sand'
prod_alias=$site'.prod'

echo "Site: $site"
echo "Sand Drush Alias: $sand_alias"
echo "Prod Drush Alias: $prod_alias"

drush sql-sync @$prod_alias @$sand_alias --source-database=ucsfpa8 --target-database=ucsfpa8
