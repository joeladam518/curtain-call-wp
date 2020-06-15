<?php
/**
 * Plugin Name:     CurtainCallWP
 * Plugin URI:      http://joelhaker.com/curtain-call-wp/
 * Description:     CMS for theatres looking to display their productions, casts, and crews
 * Version:         1.0.1
 * Author:          Joel Haker, Gregg Hilferding, David Sams
 * Author URI:      http://joelhaker.com/
 * License:         BSD 3-Clause "New" or "Revised" License
 * License URI:     https://github.com/joeladam518/CurtainCallWP/blob/master/LICENSE
 * Text Domain:     curtain-call-wp
 * Domain Path:     /languages
**/

if (!defined('ABSPATH')) {
    die;
}

// Plugin constants
define('CCWP_PLUGIN_NAME', 'CurtainCallWP');
define('CCWP_PLUGIN_VERSION', '1.0.1');
define('CCWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CCWP_TEXT_DOMAIN', 'curtain-call-wp');
define('CCWP_DEBUG', false);

// Load composer dependencies
require_once dirname(__FILE__) . '/vendor/autoload.php';

// Register Plugin Life Cycle Hooks
\CurtainCallWP\CurtainCall::registerLifeCycleHooks(__FILE__);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
**/
function ccwp_run_plugin()
{
    $ccwp_plugin = new \CurtainCallWP\CurtainCall();
    $ccwp_plugin->run();
}
ccwp_run_plugin();
