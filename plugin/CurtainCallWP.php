<?php if (!defined('ABSPATH')) die;
/**
 * Plugin Name:  CurtainCallWP
 * Plugin URI:   https://github.com/joeladam518/CurtainCallWP
 * Description:  CMS for theatres looking to display their productions, casts, and crews
 * Version:      0.4.1
 * Author:       Joel Haker, Gregg Hilferding, David Sams
 * Author URI:   https://joelhaker.com/
 * License:      MIT
 * License URI:  https://github.com/joeladam518/CurtainCallWP/blob/master/LICENSE
 * Text Domain:  curtain-call-wp
 * Domain Path:  CurtainCall/languages
**/

use CurtainCallWP\CurtainCall;

// Plugin constants
define('CCWP_PLUGIN_NAME', 'CurtainCallWP');
define('CCWP_PLUGIN_VERSION', '0.4.1');
define('CCWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CCWP_TEXT_DOMAIN', 'curtain-call-wp');
define('CCWP_DEBUG', false);

// Load dependencies
require_once dirname(__FILE__) . '/vendor/autoload.php';

// Register Plugin Life Cycle Hooks
CurtainCall::registerLifeCycleHooks(__FILE__);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
**/
function ccwp_run_plugin()
{
    $ccwp_plugin = new CurtainCall();
    $ccwp_plugin->run();
}
ccwp_run_plugin();
