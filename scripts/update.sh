#!/usr/bin/env bash

set -Eeo pipefail

SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"

cd "$REPO_DIR" || exit 1

# Install dependencies
# "composer install" will run composer install inside the plugin directory as well
composer install
npm install

# Build assets
npm run dev
