#!/usr/bin/env bash
# TODO: use this script to build a wordpress plugin .zip file for installation in a production environment

# variables
CWD=$(pwd);

# find plugin dir
if [ -d './plugin' ]; then
    plugin_dir=$(cd "${CWD}/./plugin"; printf %s "$PWD")
elif [ -d '../plugin' ]; then
    plugin_dir=$(cd "${CWD}/../plugin"; printf %s "$PWD")
else
    echo "Can't find the plugin directory..."
    echo "Have you tried running this script from the CurtainCallWP directory?";
    exit 1
fi

# find repos dir
repo_dir=$(dirname -- "$plugin_dir")

if [ "$repo_dir" != "${HOME}/repos/CurtainCallWP" ]; then
    echo "Can't build. We're in the wrong directory..."
    exit 1
fi

# TODO: finish the build script
exit

# build for production
cd "$plugin_dir" && npm run production
cd "$plugin_dir" && composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader

# reset back to dev
cd "$plugin_dir" && npm run dev
cd "$plugin_dir" && composer install
