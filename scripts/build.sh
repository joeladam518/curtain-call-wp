#!/usr/bin/env bash

set -Eeo pipefail

# Variables
SCRIPTS_DIR="$(cd "$(dirname "$0")" > /dev/null 2>&1 && pwd -P)"
REPO_DIR="$(dirname "$SCRIPTS_DIR")"
PLUGIN_DIR="${REPO_DIR}/plugin"
VERSION="${VERSION:-"$1"}"
TAG="${TAG:-"v${VERSION}"}"

if [ -z "$VERSION" ]; then
    echo "No version provided. Can't continue." 1>&2
    exit 1
fi

ZIP_DIR_NAME="CurtainCallWP"
ZIP_DIR="${REPO_DIR}/${ZIP_DIR_NAME}"
ZIP_FILE_NAME="$(echo "${ZIP_DIR_NAME}" | tr '[:upper:]' '[:lower:]')-${VERSION}.zip"

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

echo ""
echo "# Install dependencies"
composer run build
npm install

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

if [ -f "./$ZIP_DIR_NAME" ]; then
    rm "./$ZIP_DIR_NAME"
fi

# Zip up the Directory
cd "$REPO_DIR" || exit 1
zip -r "./${ZIP_FILE_NAME}" "./$ZIP_DIR_NAME"
rm -rf "$ZIP_DIR"

echo ""
echo "# Done!"
echo ""