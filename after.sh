#!/bin/bash

# Variables
repo_dir="${HOME}/CurtainCallWP"
repo_plugin_dir="${repo_dir}/plugin"
wp_dir="${repo_dir}/public"
wp_plugin_dir="${wp_dir}/wp-content/plugins"

echo "Installing wordpres into ${wp_dir}"

# Download WordPress and create wp-config.php
cd "${wp_dir}" && wp core download
cd "${wp_dir}" && wp config create --dbname=homestead --dbuser=homestead --dbpass=secret

# Write debug constants into the wp-config.php file
if ! grep -q "WP_DEBUG" "${wp_dir}/wp-config.php"; then
cd "${wp_dir}" && sed -i "/^\$table_prefix/ r /dev/stdin" "${wp_dir}/wp-config.php" <<EOL

// Set WordPress into debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
define('SCRIPT_DEBUG', false);
define('SAVEQUERIES', false);
define('FS_METHOD','direct');
EOL
fi

# Link the plugin to the wordpress site
cd "${wp_plugin_dir}" && ln -sf "${repo_plugin_dir}" CurtainCallWP

# return to the repo directory
cd "${repo_dir}"
