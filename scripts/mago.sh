#!/usr/bin/env bash

set -Eeo pipefail

SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/plugin"

"${PLUGIN_DIR}/vendor/bin/mago" --workspace "$PLUGIN_DIR" --config "${PLUGIN_DIR}/mago.toml" "$@"
