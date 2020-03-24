#!/bin/sh
#
# Cloud Hook: post-code-update
#
# Copy the database from production.


# Map the script inputs to convenient names.
site=$1
target_env=$2
source_branch="$3"
drush_alias=$site'.'$target_env

echo "Site: $site"
echo "Target Env: $target_env"
echo "Source Branch: $source_branch"
