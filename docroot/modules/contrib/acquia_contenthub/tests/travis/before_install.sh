#!/usr/bin/env bash

set -ev

cd "$(dirname "$0")" || exit; source _includes.sh

if [[ "$ORCA_JOB" == "STATIC_CODE_ANALYSIS" ]]; then
  rm -Rf "$ORCA_SUT_DIR/acquia_contenthub_subscriber/ember"
fi
