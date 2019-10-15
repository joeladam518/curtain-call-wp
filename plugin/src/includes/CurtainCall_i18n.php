<?php

namespace CurtainCallWP\includes;

/**
 *  Define the internationalization functionality
 *
 *  Loads and defines the internationalization files for this plugin
 *  so that it is ready for translation.
 *
 *  @link       http://example.com
 *  @since      0.0.1
 *
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
**/

/**
 *  Define the internationalization functionality.
 *
 *  Loads and defines the internationalization files for this plugin
 *  so that it is ready for translation.
 *
 *  @since      1.0.0
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
 *  @author     Joel Haker <joel@greenbar.co>
**/
class CurtainCall_i18n 
{
    /**
     *  Load the plugin text domain for translation.
     *
     *  @since    1.0.0
    **/
    public function load_plugin_textdomain() 
    {
        load_plugin_textdomain(
            CCWP_PLUGIN_NAME,
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
