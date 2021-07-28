#!/usr/bin/env bash

# NAME
#     install.sh - Install Travis CI dependencies
#
# SYNOPSIS
#     install.sh
#
# DESCRIPTION
#     Creates the test fixture.

cd "$(dirname "$0")" || exit; source _includes.sh

# Create a fixture for the DEPLOY job.
if [[ "$DEPLOY" ]]; then
  mysql -e 'CREATE DATABASE drupal;'
  orca fixture:init \
    -f \
    --sut=drupal/acquia_contenthub \
    --sut-only \
    --core="$CORE" \
    --no-sqlite \
    --no-site-install
  cd "$ORCA_FIXTURE_DIR/docroot/sites/default" || exit 1
  cp default.settings.php settings.php
  chmod 775 settings.php
  drush site:install \
    minimal \
    --db-url=mysql://root:@127.0.0.1/drupal \
    --site-name=ORCA \
    --account-name=admin \
    --account-pass=admin \
    --no-interaction \
    --verbose \
    --ansi
fi

# Exit early in the absence of a fixture.
[[ -d "$ORCA_FIXTURE_DIR" ]] || exit 0

composer -d"$ORCA_FIXTURE_DIR" require drupal/paragraphs
composer -d"$ORCA_FIXTURE_DIR" require symfony/phpunit-bridge "^3.4.3" --dev
