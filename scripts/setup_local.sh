#!/usr/bin/env bash

set -Eeo pipefail

# Variables
SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"

# Start logic
cd "$REPO_DIR" || exit 1

# source the env file for the docker compose
source "./.env"

# install dependencies
composer install
npm install

# refresh the autoload files
bash "${SCRIPTS_DIR}/clearcache.sh"

# build assets
npm run dev

# build the container
docker-compose up -d --build
