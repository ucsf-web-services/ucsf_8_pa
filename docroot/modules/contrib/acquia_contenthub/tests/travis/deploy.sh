#!/usr/bin/env bash

# NAME
#     deploy.sh - Deploy a build artifact.
#
# SYNOPSIS
#     deploy.sh
#
# DESCRIPTION
#     Creates a build artifact and deploys it to the QA environment.

cd "$(dirname "$0")" || exit; source _includes.sh

cd "$ORCA_FIXTURE_DIR" || exit 1

# Edit composer config to not symlink the repo.
composer config \
  repositories.drupal/acquia_contenthub \
  '{"type": "path", "url": "../../acquia_contenthub", "options": { "symlink": false }}'

# Must rebuild the lock file to deploy it.
rm -rf composer.lock

# Run the BLT installer.
vendor/bin/blt artifact:deploy \
  --commit-msg "Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" \
  --branch "${DRUPAL}-${TRAVIS_BRANCH}" \
  --ignore-dirty \
  --no-interaction \
  --verbose \
  -Dgit.remotes.1='contenthubqa@svn-29892.prod.hosting.acquia.com:contenthubqa.git'
