<?php
/**
 * Plugin Name: CurtainCallWP
 * Plugin URI: https://github.com/joeladam518/CurtainCallWP
 * Description: CMS for theatres looking to display their productions, casts, and crews
 * Version: 0.7.0
 * Requires at least: 6.3
 * Requires PHP: 8.3
 * Author: Joel Haker, Gregg Hilferding, David Sams
 * Author URI: https://joelhaker.com/
 * License: MIT
 * License URI: https://github.com/joeladam518/CurtainCallWP/blob/master/LICENSE
 * Text Domain: curtain-call-wp
 * Domain Path: /languages
**/
if (!defined('ABSPATH')) {
    die;
}

define('CCWP_PLUGIN_NAME', 'CurtainCallWP');
define('CCWP_PLUGIN_VERSION', '0.7.0');
define('CCWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CCWP_TEXT_DOMAIN', 'curtain-call-wp');
define('CCWP_DEBUG', true);

require_once dirname(__FILE__) . '/vendor/autoload.php';

\CurtainCall\CurtainCall::registerLifeCycleHooks(__FILE__);
\CurtainCall\CurtainCall::run();
