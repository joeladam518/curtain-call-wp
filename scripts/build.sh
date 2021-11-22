#!/usr/bin/env bash

set -Eeo pipefail

# Environment

# Setup
SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/plugin"
ZIP_DIR_NAME="CurtainCallWP"
ZIP_DIR="${REPO_DIR}/${ZIP_DIR_NAME}"
ZIP_FILE_NAME="$(echo "${ZIP_DIR_NAME}" | tr '[:upper:]' '[:lower:]').zip"

#echo "     REPO_DIR: ${REPO_DIR}"
#echo "  SCRIPTS_DIR: ${SCRIPTS_DIR}"
#echo "   PLUGIN_DIR: ${PLUGIN_DIR}"
#echo "      ZIP_DIR: ${ZIP_DIR}"
#echo " ZIP_DIR_NAME: ${ZIP_DIR_NAME}"
#echo "ZIP_FILE_NAME: ${ZIP_FILE_NAME}"
#echo ""

# Start Logic
cd "$REPO_DIR" || exit 1
if [ ! -f "$ZIP_DIR" ]; then
    mkdir -p "$ZIP_DIR"
fi

# Build for production
npm run prod
composer run build

# Copy the plugin to the directory to be zipped
cd "$REPO_DIR" || exit 1
rsync -arh --delete-delay --exclude-from "${SCRIPTS_DIR}/exclude-from.txt" "${PLUGIN_DIR}/" "${ZIP_DIR}"

# Copy the license and readme to the zip dir
cp "${REPO_DIR}/LICENSE" "${ZIP_DIR}/LICENSE"
cp "${REPO_DIR}/README.md" "${ZIP_DIR}/README.md"

# Set the file and directory permissions
find "$ZIP_DIR" -type d -exec chmod 755 {} \;
find "$ZIP_DIR" -type f -exec chmod 644 {} \;

if [ -f "./$ZIP_DIR_NAME" ]; then
    rm "./$ZIP_DIR_NAME"
fi

# Zip up the Directory
cd "$REPO_DIR" || exit 1
zip -r "./${ZIP_FILE_NAME}" "./$ZIP_DIR_NAME"
rm -rf "$ZIP_DIR"

# Reset back to dev
npm run dev
composer run src-install
