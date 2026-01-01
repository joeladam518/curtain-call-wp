#!/bin/bash

# Script to clear built assets from plugin/assets directory
# This removes all built assets and sets up .gitignore files

set -Eeo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ASSETS_DIR="$PROJECT_ROOT/plugin/assets"

echo "Clearing built assets..."

# Check if assets directory exists
if [ ! -d "$ASSETS_DIR" ]; then
    echo "Assets directory does not exist at: $ASSETS_DIR"
    exit 1
fi

# Define the directories we want to keep
KEEP_DIRS=("admin" "fonts" "frontend")

# Empty the admin, fonts, and frontend directories
for dir in "${KEEP_DIRS[@]}"; do
    DIR_PATH="$ASSETS_DIR/$dir"

    if [ -d "$DIR_PATH" ]; then
        echo "Clearing $dir folder..."
        rm -rf "$DIR_PATH"/*
        rm -rf "$DIR_PATH"/.[!.]*
    else
        echo "Creating $dir folder..."
        mkdir -p "$DIR_PATH"
    fi

    # Create .gitignore that ignores everything except itself
    echo "Creating .gitignore in $dir..."
    cat > "$DIR_PATH/.gitignore" << 'EOF'
*
!.gitignore
EOF
done

# Remove any other files or directories in plugin/assets
echo "Removing other files/directories from plugin/assets..."
cd "$ASSETS_DIR"
for item in *; do
    # Skip if it's one of the directories we want to keep
    if [[ ! " ${KEEP_DIRS[*]} " =~ " ${item} " ]]; then
        echo "Removing: $item"
        rm -rf "$item"
    fi
done

# Also remove hidden files/directories that aren't in our keep list
for item in .[!.]*; do
    if [ -e "$item" ]; then
        echo "Removing: $item"
        rm -rf "$item"
    fi
done

echo "Built assets cleared successfully!"
