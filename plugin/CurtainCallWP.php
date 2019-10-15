<?php
/**
 * Plugin Name:       CurtainCallWP
 * Plugin URI:        http://joelhaker.com/curtain-call-wp/
 * Description:       CMS for theatres looking to display their productions, casts, and crews
 * Version:           1.0.0
 * Author:            Joel Haker, Gregg Hilferding, David Sams
 * Author URI:        http://joelhaker.com/
 * License:           GNU Lesser General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/lgpl-3.0.txt
 * Text Domain:       curtain-call-wp
 * Domain Path:       /languages
**/

# If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 *  Define Constants
**/
define('CCWP_VERSION', '0.1.0');
define('CCWP_PLUGIN_NAME', 'CurtainCallWP');
define('CCWP_TEXT_DOMAIN', 'curtain-call-wp');

/**
 *  Load Composer
**/
require_once 'vendor/autoload.php';
require_once 'src/includes/CurtainCallDebugHelperFunctions.php';


/**
 *  The code that runs during plugin activation.
 *  This action is documented in includes/CurtainCallActivator.php
**/
function activate_curtain_call_wp() {
    CurtainCallWP\includes\CurtainCallActivator::activate();
}

/**
 *  The code that runs during plugin deactivation.
 *  This action is documented in includes/CurtainCallDeactivator.php
**/
function deactivate_curtain_call_wp() {
    CurtainCallWP\includes\CurtainCallDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_curtain_call_wp');
register_deactivation_hook(__FILE__, 'deactivate_curtain_call_wp');

/**
 *  The core plugin class that is used to define internationalization,
 *  admin-specific hooks, and public-facing site hooks.
**/
//require plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name.php';

/**
 *  Begins execution of the plugin.
 *
 *  Since everything within the plugin is registered via hooks,
 *  then kicking off the plugin from this point in the file does
 *  not affect the page life cycle.
 *
 *  @since    0.0.1
**/
function run_curtain_call_wp() {
    $plugin = new CurtainCallWP\CurtainCall();
    $plugin->run();
}

run_curtain_call_wp();
