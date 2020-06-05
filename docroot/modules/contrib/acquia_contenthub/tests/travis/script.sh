#!/usr/bin/env bash

# NAME
#     script.sh - Run tests
#
# SYNOPSIS
#     script.sh
#
# DESCRIPTION
#     Runs static code analysis and automated tests.

cd "$(dirname "$0")" || exit; source _includes.sh

# Restrict to the DEPLOY job.
[[ "$DEPLOY" ]] || exit 0

# Run tests.
cd "${ORCA_FIXTURE_DIR}/docroot" || exit 1
export SIMPLETEST_BASE_URL=http://127.0.0.1:8080
export SIMPLETEST_DB=mysql://root:@127.0.0.1/drupal
export SYMFONY_DEPRECATIONS_HELPER=disabled
orca fixture:run-server &
WEB_SERVER_PID=$!
phpunit \
  --colors=always \
  --debug \
  --configuration=core/phpunit.xml.dist \
  modules/contrib/acquia_contenthub
kill -9 $WEB_SERVER_PID
