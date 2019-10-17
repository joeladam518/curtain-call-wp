#!/usr/bin/env bash

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.
#
# If you have user-specific configurations you would like
# to apply, you may also create user-customizations.sh,
# which will be run after this script.

# If you're not quite ready for Node 12.x
# Uncomment these lines to roll back to
# v11.x or v10.x

# Remove Node.js v12.x:
#sudo apt-get -y purge nodejs
#sudo rm -rf /usr/lib/node_modules/npm/lib
#sudo rm -rf //etc/apt/sources.list.d/nodesource.list

# Install Node.js v11.x
#curl -sL https://deb.nodesource.com/setup_11.x | sudo -E bash -
#sudo apt-get install -y nodejs

# Install Node.js v10.x
#curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
#sudo apt-get install -y nodejs

# Variables
repo_dir="${HOME}/CurtainCallWP"
plugin_dir="${repo_dir}/plugin"
wp_dir="${repo_dir}/public"
wp_plugin_dir="${wp_dir}/wp-content/plugins"

echo "Installing wordpres into ${wp_dir}"

# download Wordpress and create wp-config.php
cd "${wp_dir}" && wp core download && wp config create --dbname=homestead --dbuser=homestead --dbpass=secret

# link the plugin to the wordpress site
cd "${wp_plugin_dir}" && ln -sf ${plugin_dir} CurtainCallWP

# return HOME
cd "${HOME}"
