#!/usr/bin/env bash

set -Eeo pipefail

# Setup
SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/src"

if ! [ -x "$(command -v docker-compose)" ]; then
    shopt -s expand_aliases
    alias docker-compose='docker compose'
fi

UNAMEOUT="$(uname -s)"

WHITE='\033[1;37m'
NC='\033[0m'

# Verify operating system is supported...
case "${UNAMEOUT}" in
    Linux*)  MACHINE=linux;;
    Darwin*) MACHINE=mac;;
    *)       MACHINE="UNKNOWN"
esac

if [ "$MACHINE" == "UNKNOWN" ]; then
    echo "Unsupported operating system [$(uname -s)]. Laravel Sail supports macOS, Linux, and Windows (WSL2)." >&2
    exit 1
fi

# Source the ".env" file
if [ -f ./.env ]; then
    source ./.env
fi

# Define environment variables...
export APP_PORT=${APP_PORT:-80}
export APP_SERVICE=${APP_SERVICE:-"wpsite.test"}
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Functions
container_is_not_running() {
    echo -e "${WHITE}The container is not running.${NC}" >&2
    echo "" >&2
    echo -e "${WHITE}You may run it using the following commands:${NC} './scripts/dkr.sh up' or './scripts/dkr.sh up -d'" >&2
    exit 1
}

# Start script logic

if [ -z "$SKIP_CHECKS" ]; then
    # Ensure that Docker is running...
    if ! docker info > /dev/null 2>&1; then
        echo -e "${WHITE}Docker is not running.${NC}" >&2
        exit 1
    fi

    # Determine if Sail is currently up...
    PSRESULT="$(docker-compose ps -q)"
    if docker-compose ps | grep "$APP_SERVICE" | grep 'Exit'; then
        echo -e "${WHITE}Shutting down old container processes...${NC}" >&2
        docker-compose down > /dev/null 2>&1

        EXEC="no"
    elif [ -n "$PSRESULT" ]; then
        EXEC="yes"
    else
        EXEC="no"
    fi
else
    EXEC="yes"
fi

if [ $# -gt 0 ]; then
    # Initiate a MySQL CLI terminal session within the "mysql" container...
    if [ "$1" == "mysql" ]; then
        shift 1

        if [ "$EXEC" == "yes" ]; then
            docker-compose exec \
                mysql \
                bash -c 'MYSQL_PWD=${MYSQL_PASSWORD} mysql -u ${MYSQL_USER} ${MYSQL_DATABASE}'
        else
            container_is_not_running
        fi

    # Initiate a MySQL CLI terminal session within the "mariadb" container...
    elif [ "$1" == "mariadb" ]; then
        shift 1

        if [ "$EXEC" == "yes" ]; then
            docker-compose exec \
                mariadb \
                bash -c 'MYSQL_PWD=${MYSQL_PASSWORD} mysql -u ${MYSQL_USER} ${MYSQL_DATABASE}'
        else
            container_is_not_running
        fi

    # Initiate a Bash shell within the application container...
    elif [ "$1" == "shell" ] || [ "$1" == "bash" ]; then
        shift 1

        if [ "$EXEC" == "yes" ]; then
            docker-compose exec \
                -u wpuser \
                "$APP_SERVICE" \
                bash "$@"
        else
            container_is_not_running
        fi

    # Initiate a root user Bash shell within the application container...
    elif [ "$1" == "root-shell" ] ; then
        shift 1

        if [ "$EXEC" == "yes" ]; then
            docker-compose exec \
                "$APP_SERVICE" \
                bash "$@"
        else
            container_is_not_running
        fi

    # Pass unknown commands to the "docker-compose" binary...
    else
        docker-compose "$@"
    fi
else
    docker-compose ps
fi
