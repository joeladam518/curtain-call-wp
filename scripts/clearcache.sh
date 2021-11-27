#!/usr/bin/env bash

set -Eeo pipefail

SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/plugin"

cd "$REPO_DIR" && composer dumpautoload
cd "$PLUGIN_DIR" && composer dumpautoload
