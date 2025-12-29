<?php

declare(strict_types=1);

namespace CurtainCall\LifeCycle;

class Activator implements LifeCycleHook
{
    public static function run(): void
    {
        static::createPluginTables();
        add_option('ccwp_db_version', CCWP_PLUGIN_VERSION);
        flush_rewrite_rules(false);
    }

    protected static function createPluginTables(): void
    {
        $wpdb = ccwp_get_wpdb();
        $table_name = "{$wpdb->prefix}ccwp_castandcrew_production";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `production_id` BIGINT UNSIGNED NOT NULL,
            `cast_and_crew_id` BIGINT UNSIGNED NOT NULL,
            `type` VARCHAR(191) DEFAULT 'cast' NULL,
            `role` VARCHAR(191) DEFAULT NULL NULL,
            `custom_order` SMALLINT UNSIGNED DEFAULT NULL NULL
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
