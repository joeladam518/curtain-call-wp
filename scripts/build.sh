#!/usr/bin/env bash

set -Eeo pipefail

# Variables
SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/plugin"
VERSION="${VERSION:-"${1:-""}"}"
ZIP_DIR_NAME="CurtainCallWP"
ZIP_DIR="${REPO_DIR}/${ZIP_DIR_NAME}"

if [ -n "$VERSION" ]; then
    ZIP_FILE_NAME="$(echo "${ZIP_DIR_NAME}" | tr '[:upper:]' '[:lower:]')-${VERSION}.zip"
else
    ZIP_FILE_NAME="$(echo "${ZIP_DIR_NAME}" | tr '[:upper:]' '[:lower:]').zip"
fi

#echo "     REPO_DIR: ${REPO_DIR}"
#echo "  SCRIPTS_DIR: ${SCRIPTS_DIR}"
#echo "   PLUGIN_DIR: ${PLUGIN_DIR}"
#echo "      ZIP_DIR: ${ZIP_DIR}"
#echo " ZIP_DIR_NAME: ${ZIP_DIR_NAME}"
#echo "ZIP_FILE_NAME: ${ZIP_FILE_NAME}"
#echo ""
#exit 0

# Start Logic
cd "$REPO_DIR" || exit 1
if [ ! -d "$ZIP_DIR" ]; then
    mkdir -p "$ZIP_DIR"
fi

echo ""
echo "# Install dependencies"
composer run build
npm install

echo ""
echo "# Refresh the autoload files"
cd "${PLUGIN_DIR}" && composer dumpautoload

echo ""
echo "# Build production assets"
npm run prod

# Copy the plugin to the directory to be zipped
echo ""
echo "# Create Plugin zip file"
cd "$REPO_DIR" || exit 1
rsync -arh --delete-delay --exclude-from "${SCRIPTS_DIR}/exclude-from.txt" "${PLUGIN_DIR}/" "${ZIP_DIR}"

# Copy the license and readme to the zip dir
cp "${REPO_DIR}/LICENSE" "${ZIP_DIR}/LICENSE"
cp "${REPO_DIR}/README.md" "${ZIP_DIR}/README.md"

# Set the file and directory permissions
find "$ZIP_DIR" -type d -exec chmod 755 {} \;
find "$ZIP_DIR" -type f -exec chmod 644 {} \;

# Zip up the Directory
cd "$REPO_DIR" || exit 1
zip -r "./${ZIP_FILE_NAME}" "./${ZIP_DIR_NAME}"
rm -rf "$ZIP_DIR"

echo ""
echo "# Done!"
echo ""
