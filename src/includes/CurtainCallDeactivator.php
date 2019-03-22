<?php

namespace CurtainCallWP\includes;

/**
 *  Fired during plugin deactivation
 *
 *  @link       http://example.com
 *  @since      0.0.1
 *
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
**/

/**
 *  Fired during plugin deactivation.
 *
 *  This class defines all code necessary to run during the plugin's deactivation.
 *
 *  @since      0.0.1
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
 *  @author     Joel Haker <joel@greenbar.co>
**/
class CurtainCallDeactivator 
{
    
    /**
     *  Short Description. (use period)
     *
     *  Long Description.
     *
     *  @since    0.0.1
    **/
    public static function deactivate() 
    {
        unregister_post_type( 'ccwp_production' );
        unregister_post_type( 'ccwp_cast_and_crew' );
        
        flush_rewrite_rules( false );
    }
}
