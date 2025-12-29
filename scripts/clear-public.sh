#!/bin/bash

# Script to empty the public folder
# This removes all contents from the public directory

set -Eeo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
PUBLIC_DIR="$PROJECT_ROOT/public"

echo "Clearing public folder..."

if [ ! -d "$PUBLIC_DIR" ]; then
    echo "Public directory does not exist at: $PUBLIC_DIR"
    exit 1
fi

# Remove all contents from the public folder
rm -rf "$PUBLIC_DIR"/*
rm -rf "$PUBLIC_DIR"/.[!.]*

echo "Public folder cleared successfully!"
