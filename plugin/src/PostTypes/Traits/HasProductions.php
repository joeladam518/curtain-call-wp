<?php

namespace CurtainCallWP\PostTypes\Traits;

use Carbon\CarbonImmutable as Carbon;

trait HasProductions
{
    public function getProductions($include_post_meta = true)
    {
        global $wpdb;
        
        $query = "
            SELECT
                `production_posts`.*,
                `ccwp_join`.`production_id` AS `ccwp_join_production_id`,
                `ccwp_join`.`cast_and_crew_id` AS `ccwp_join_castcrew_id`,
                `ccwp_join`.`type` AS `ccwp_join_type`,
                `ccwp_join`.`role` AS `ccwp_join_role`,
                `ccwp_join`.`custom_order` AS `ccwp_join_custom_order`
            FROM `". $wpdb->posts ."` AS `castcrew_posts`
            INNER JOIN `". static::getJoinTableName() ."` AS `ccwp_join` ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`
            INNER JOIN `". $wpdb->posts ."` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`
            WHERE `castcrew_posts`.`ID` = %d
            ORDER BY production_posts.post_title
        ";
    
        $sql = $wpdb->prepare($query, $this->ID);
        $productions = $wpdb->get_results($sql, ARRAY_A);
        
        if ($include_post_meta && count($productions) > 0) {
            foreach ($productions as &$production) {
                $post_meta = get_post_meta($production['ccwp_join_production_id']);
                $production['post_meta'] = array_map(function($meta) {
                    return $meta[0] ?? null;
                }, $post_meta);
            }
            
            usort($productions , function($a, $b) {
                if (empty($a['post_meta']['_ccwp_production_date_start']) && !empty($b['post_meta']['_ccwp_production_date_start'])) {
                    return -1;
                }
                if (!empty($a['post_meta']['_ccwp_production_date_start']) && empty($b['post_meta']['_ccwp_production_date_start'])) {
                    return 1;
                }
                
                $a_start_date = Carbon::parse($a['post_meta']['_ccwp_production_date_start']);
                $b_start_date = Carbon::parse($b['post_meta']['_ccwp_production_date_start']);
                
                if ($a_start_date->eq($b_start_date)) {
                    return 0;
                }
                if ($a_start_date->gt($b_start_date)) {
                    return -1;
                }
                if ($a_start_date->lt($b_start_date)) {
                    return 1;
                }
            });
        }

        return $productions;
    }
}
