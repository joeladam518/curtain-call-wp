<?php

namespace CurtainCallWP\PostTypes\Traits;

use CurtainCallWP\Helpers\CurtainCallHelper;
use CurtainCallWP\Helpers\QueryHelper;
use CurtainCallWP\PostTypes\Production;

trait HasProductions
{
    public function getProductions($include_post_meta = true)
    {
        global $wpdb;
        
        // SELECT
        // `production_posts`.*,
        // `ccwp_join`.`production_id` AS `ccwp_join_production_id`,
        // `ccwp_join`.`cast_and_crew_id` AS `ccwp_join_castcrew_id`,
        // `ccwp_join`.`type` AS `ccwp_join_type`,
        // `ccwp_join`.`role` AS `ccwp_join_role`,
        // `ccwp_join`.`custom_order` AS `ccwp_join_custom_order`

        $query = "
            SELECT ". QueryHelper::selectProductions() ."
            FROM `". $wpdb->posts ."` AS `castcrew_posts`
            INNER JOIN ". static::getJoinTableNameWithAlias() ." ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`
            INNER JOIN `". $wpdb->posts ."` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`
            WHERE `castcrew_posts`.`ID` = %d
            ORDER BY `production_posts`.`post_title`
        ";
    
        $sql = $wpdb->prepare($query, $this->ID);
        $productions = $wpdb->get_results($sql, ARRAY_A);
        
        if (count($productions) > 0) {
            /** @var array|Production[] $productions */
            $productions = CurtainCallHelper::convertToCurtainCallPosts($productions);
    
            usort($productions , function($production_a, $production_b) {
                /** @var Production $production_a */
                $a_has_start_date = empty($production_a->date_start);
                /** @var Production $production_b */
                $b_has_start_date = empty($production_b->date_start);
                if ($a_has_start_date && !$b_has_start_date) {
                    return -1;
                } else if ($b_has_start_date && !$a_has_start_date) {
                    return 1;
                } else {
                    $a_start_date = CurtainCallHelper::toCarbon($production_a->date_start);
                    $a_start_date = $a_start_date ? $a_start_date->endOfDay() : null;
                    $b_start_date = CurtainCallHelper::toCarbon($production_b->date_start);
                    $b_start_date = $b_start_date ? $b_start_date->endOfDay() : null;
                    
                    if (isset($a_start_date) && isset($b_start_date)) {
                        if ($a_start_date->lt($b_start_date)) {
                            return 1;
                        } else if ($a_start_date->gt($b_start_date)) {
                            return -1;
                        } else {
                            return 0;
                        }
                    } else {
                        if ($a_start_date === null && $b_start_date !== null) {
                            return -1;
                        } else if ($a_start_date !== null && $b_start_date === null) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                }
            });
        }

        return $productions;
    }
}
