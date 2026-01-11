#!/bin/bash
set -e

# Run the original WordPress entrypoint
docker-entrypoint.sh "$@" &
PID=$!

# Wait for wp-config.php to be created
while [ ! -f /var/www/html/wp-config.php ]; do
    sleep 1
done

wait $PID
