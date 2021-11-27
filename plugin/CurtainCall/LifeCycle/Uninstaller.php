<?php

namespace CurtainCall\LifeCycle;

use CurtainCall\PostTypes\CastAndCrew;
use CurtainCall\PostTypes\CurtainCallPivot;
use CurtainCall\PostTypes\Production;

class Uninstaller implements LifeCycleHook
{
    public static function run(): void
    {
        static::deletePluginTables();
        static::deletePluginPosts();
        static::deletePluginPostMeta();
        delete_option('ccwp_db_version');
        flush_rewrite_rules(false);
    }

    protected static function deletePluginTables(): void
    {
        global $wpdb;
        $table = CurtainCallPivot::getTableName();
        $sql = "DROP TABLE IF EXISTS {$table};";
        $wpdb->query($sql);
    }

    protected static function deletePluginPosts(): void
    {
        global $wpdb;

        $sql = implode(' ', [
            "DELETE FROM {$wpdb->posts}",
            "WHERE `post_type` = '". CastAndCrew::POST_TYPE ."'",
            "OR `post_type` = '". Production::POST_TYPE ."'",
        ]);

        $wpdb->query($sql);
    }

    protected static function deletePluginPostMeta(): void
    {
        global $wpdb;

        $sql = "
            DELETE FROM {$wpdb->postmeta}
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
}
