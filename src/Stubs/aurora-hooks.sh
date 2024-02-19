#!/bin/bash

set -e
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
MECURY_MARKER_PATH="/mercury.container"
IN_MERCURY_CONTAINER=false
HOOK_PATH=""
if [ -f "$MECURY_MARKER_PATH" ]; then
    IN_MERCURY_CONTAINER=true
    HOOK_PATH="/srv/www/"
    source /mercury/index.sh
    log_info "Running in Mercury container"
else
    source $SCRIPT_DIR/docker/app/mercury/logger.sh
    log_info "Running in local environment"
fi

# This script is used to run the various build lifecycle hooks for Aurora build system.
# It is called with a single argument which is the name of the hook to run.
case $1 in
  "prebuild")
    echo "Running prebuild hook"
    ;;
  "postbuild")
    echo "Running postbuild hook"
    ;;
  "predeploy")
    echo "Running predeploy hook"
    ;;
  "postdeploy")
    echo "Running postdeploy hook"
    ;;
  "prestart")
    echo "Running prestart hook"
    ;;
  "poststart")
    echo "Running poststart hook"
    ;;
  "prestop")
    echo "Running prestop hook"
    ;;
  "poststop")
    echo "Running poststop hook"
    ;;
  *)
    echo "Unknown hook"
    exit 1
    ;;
esac
