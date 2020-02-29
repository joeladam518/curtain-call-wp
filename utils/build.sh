#!/usr/bin/env bash

# Variables
CWD=$(pwd);
plugin_dir_name="src"
zip_dir_name="CurtainCallWP"

# Determine plugin dir path
if [ -d "./${plugin_dir_name}" ]; then
    plugin_dir=$(cd "${CWD}/./${plugin_dir_name}"; pwd)
elif [ -d "../${plugin_dir_name}" ]; then
    plugin_dir=$(cd "${CWD}/../${plugin_dir_name}"; pwd)
else
    echo "Can't find the plugin directory..."
    echo "Have you tried running this script from the CurtainCallWP directory?";
    exit 1
fi

echo "Plugin Directory path: ${plugin_dir}"

# Determine the repos dir path
repo_dir=$(dirname -- "$plugin_dir")

if [ "$repo_dir" != "${HOME}/repos/CurtainCallWP" ]; then
    echo "Can't build. We're in the wrong directory..."
    exit 1
fi

echo "Repo Directory path: ${repo_dir}"

# Determine the path to the folder that will be zipped
cd "$repo_dir"
if [ ! -d "./$zip_dir_name" ]; then
    # make the directory to be zipped
    cd "$repo_dir" && mkdir "./$zip_dir_name"
fi

zip_dir="${repo_dir}/${zip_dir_name}"

echo "Zip Directory path: $zip_dir"

if [ "$zip_dir" != "${repo_dir}/${zip_dir_name}" ];then
    echo "Can't build. Didn't create the correct zip directory..."
    exit 1
fi

# build for production
cd "$plugin_dir" && npm run production
cd "$plugin_dir" && composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader

# copy the plugin to the directory to be zipped
rsync -arh --delete-delay "$plugin_dir/" "$zip_dir"

# set the file and directory permissions
find "$zip_dir" -type d -exec chmod 755 {} \;
find "$zip_dir" -type f -exec chmod 644 {} \;

# remove unneded files
cd "$zip_dir" && rm -rf "./node_modules"
cd "$zip_dir" && rm -rf "./resources"
cd "$zip_dir" && rm -f "./.gitignore"
cd "$zip_dir" && rm -f "./composer.json"
cd "$zip_dir" && rm -f "./composer.lock"
cd "$zip_dir" && rm -f "./package.json"
cd "$zip_dir" && rm -f "./package-lock.json"
cd "$zip_dir" && rm -f "./webpack.config.js"
cd "$zip_dir" && rm -f "./webpack.mix.js"

# set the owner
chown -R root:root "$zip_dir"

cd "$repo_dir" && zip -r "curtaincallwp.zip" "./$zip_dir_name"
rm -rf "$zip_dir"

# reset back to dev
cd "$plugin_dir" && npm run dev
cd "$plugin_dir" && composer install
