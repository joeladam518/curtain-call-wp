<?php

declare(strict_types=1);

namespace CurtainCall\LifeCycle;

use CurtainCall\Exceptions\WordpressDbInstanceNotFoundException;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Models\Production;
use CurtainCall\Support\Query;

class Uninstaller implements LifeCycleHook
{
    /**
     * @return void
     * @throws WordpressDbInstanceNotFoundException
     */
    public static function run(): void
    {
        $wpdb = ccwp_get_wpdb();

        static::deleteTable(CurtainCallPivot::getTableName());
        static::deletePosts(CastAndCrew::POST_TYPE);
        static::deletePosts(Production::POST_TYPE);
        static::deletePostMeta();
        static::deleteTaxonomy(Production::SEASONS_TAXONOMY);
        $wpdb->flush();

        delete_option('ccwp_db_version');
        delete_option('ccwp_default_ticket_url');
        delete_option('ccwp_color_link_highlight');
        delete_option('ccwp_color_button_background');
        delete_option('ccwp_color_button_text');
        flush_rewrite_rules(false);
    }

    /**
     * @param string $postType
     * @return void
     * @throws WordpressDbInstanceNotFoundException
     */
    protected static function deletePosts(string $postType): void
    {
        $wpdb = ccwp_get_wpdb();

        $sql = "DELETE FROM `{$wpdb->posts}` WHERE `post_type` = '{$postType}';";

        $wpdb->query($sql);
    }

    /**
     * @return void
     * @throws WordpressDbInstanceNotFoundException
     */
    protected static function deletePostMeta(): void
    {
        $wpdb = ccwp_get_wpdb();

        $sql = Query::raw([
            "DELETE FROM `{$wpdb->postmeta}`",
            "WHERE (`meta_key` LIKE '_ccwp_cast_crew_%' OR `meta_key` LIKE '_ccwp_production_%');",
        ]);

        $wpdb->query($sql);
    }

    /**
     * @param string $table
     * @return void
     * @throws WordpressDbInstanceNotFoundException
     */
    protected static function deleteTable(string $table): void
    {
        $wpdb = ccwp_get_wpdb();

        $sql = "DROP TABLE IF EXISTS {$table};";

        $wpdb->query($sql);
    }

    /**
     * @param string $taxonomy
     * @return void
     * @throws WordpressDbInstanceNotFoundException
     */
    protected static function deleteTaxonomy(string $taxonomy): void
    {
        $wpdb = ccwp_get_wpdb();

        $sql = Query::raw("
            DELETE FROM `{$wpdb->terms}`
            WHERE `term_id` IN (
                SELECT *
                FROM (
                    SELECT `{$wpdb->terms}`.`term_id`
                    FROM `{$wpdb->terms}`
                    JOIN `{$wpdb->term_taxonomy}` ON `{$wpdb->term_taxonomy}`.`term_id` = `{$wpdb->terms}`.`term_id`
                    WHERE `{$wpdb->term_taxonomy}`.`taxonomy` = '{$taxonomy}'
                ) as t
            );
        ");

        $wpdb->query($sql);

        $sql = Query::raw("
            DELETE FROM `{$wpdb->term_taxonomy}` WHERE `{$wpdb->term_taxonomy}`.`taxonomy` = '{$taxonomy}';
        ");

        $wpdb->query($sql);
    }
}
