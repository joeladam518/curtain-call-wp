<?php

namespace CurtainCallWP\includes;

/**
 *  Fired during plugin activation
 *
 *  @link       http://example.com
 *  @since      0.0.1
 *
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
**/

/**
 *  Fired during plugin activation.
 *
 *  This class defines all code necessary to run during the plugin's activation.
 *
 *  @since      0.0.1
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
 *  @author     Joel Haker <joel@greenbar.co>
**/
class CurtainCallActivator 
{
    protected static function CurtainCallWPInstallTables() 
    {
    	global $wpdb;
    
    	$table_name = $wpdb->prefix . 'ccwp_castandcrew_production';
    	
    	$charset_collate = $wpdb->get_charset_collate();
    
    	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    		production_id BIGINT UNSIGNED NOT NULL,
    		cast_and_crew_id BIGINT UNSIGNED NOT NULL,
            type VARCHAR(191) DEFAULT 'cast' NULL,
    		role VARCHAR(191) DEFAULT NULL NULL,
    		custom_order SMALLINT UNSIGNED DEFAULT NULL NULL
    	) $charset_collate;";
    
    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    	dbDelta( $sql );
    
    	add_option( 'ccwp_db_version', CURTAINCALLWP_VERSION );
    }

    /**
     *  Short Description. (use period)
     *
     *  Long Description.
     *
     *  @since    0.0.1
    **/
    public static function activate() 
    {
        // Create join table for productions and cast and crew
        self::CurtainCallWPInstallTables();
        
        
        flush_rewrite_rules( false );
    }
}
