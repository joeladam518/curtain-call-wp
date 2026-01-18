#!/usr/bin/env bash

set -Eeo pipefail

SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"

source "${REPO_DIR}/.env"

docker compose exec -it --workdir /var/www/html/wp-content/plugins/CurtainCallWP wpsite.test bash -ic "$*"
