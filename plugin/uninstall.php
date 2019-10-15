<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    CurtainCallWP
**/

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function CurtainCallWPRemoveCreatedTables() 
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ccwp_castandcrew_production';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
}
CurtainCallWPRemoveCreatedTables();

delete_option('ccwp_db_version');

// Remove every instance of the custom post type Productions and Cast and Crew in the post table
// Also, Remove the meta
function CurtainCallWPPostTypeRows() 
{
    global $wpdb;
    
    $sql = "
        DELETE FROM ". $wpdb->posts ."
        WHERE `post_type` = 'ccwp_cast_and_crew'
        OR `post_type` = 'ccwp_production'
    ";
    $wpdb->query($sql);
    
    $sql = "
        DELETE FROM ". $wpdb->postmeta ."
        WHERE `meta_key` = '_ccwp_cast_crew_name_first'
        OR `meta_key` = '_ccwp_cast_crew_name_last'
        OR `meta_key` = '_ccwp_cast_crew_self_title'
        OR `meta_key` = '_ccwp_cast_crew_birthday'
        OR `meta_key` = '_ccwp_cast_crew_hometown'
        OR `meta_key` = '_ccwp_cast_crew_website_link'
        OR `meta_key` = '_ccwp_cast_crew_facebook_link'
        OR `meta_key` = '_ccwp_cast_crew_twitter_link'
        OR `meta_key` = '_ccwp_cast_crew_instagram_link'
        OR `meta_key` = '_ccwp_cast_crew_fun_fact'
        OR `meta_key` = '_ccwp_production_name'
        OR `meta_key` = '_ccwp_production_date_start'
        OR `meta_key` = '_ccwp_production_date_end'
        OR `meta_key` = '_ccwp_production_press'
        OR `meta_key` = '_ccwp_production_show_times'
        OR `meta_key` = '_ccwp_production_ticket_url'
        OR `meta_key` = '_ccwp_production_venue'
    ";
    $wpdb->query($sql);
}
CurtainCallWPPostTypeRows();

flush_rewrite_rules( false );
