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

if (!defined('WPINC')) {
    die; // If this file is called directly, abort.
}

// Plugin constants
define('CCWP_VERSION', '0.1.0');
define('CCWP_PLUGIN_NAME', 'CurtainCallWP');
define('CCWP_TEXT_DOMAIN', 'curtain-call-wp');

// Load composer dependencies
require_once 'vendor/autoload.php';

// Register the activation and deactivation hooks
register_activation_hook(__FILE__, function() {
	CurtainCallWP\includes\CurtainCallActivator::activate();
});
register_deactivation_hook(__FILE__, function() {
	CurtainCallWP\includes\CurtainCallDeactivator::deactivate();
});

/*
 | Begins execution of the plugin.
 |
 | Since everything within the plugin is registered via hooks,
 | then kicking off the plugin from this point in the file does
 | not affect the page life cycle.
 */
function run_curtain_call_wp() {
	$plugin = new CurtainCallWP\CurtainCall();
	$plugin->run();
}

run_curtain_call_wp();
