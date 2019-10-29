<?php

namespace CurtainCallWP\PostTypes\Traits;

use Carbon\CarbonImmutable as Carbon;

trait CastAndCrewHelpers
{
    public static function getProductions(int $post_id, $include_post_meta = true)
    {
        global $wpdb;
        
        $productions = null;
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        $query = "
            SELECT
                production_posts.*,
                ccwp_join.production_id AS ccwp_production_post_id,
                ccwp_join.cast_and_crew_id AS ccwp_castcrew_post_id,
                ccwp_join.type AS ccwp_type,
                ccwp_join.role AS ccwp_role,
                ccwp_join.custom_order AS ccwp_custom_order
            FROM " . $wpdb->posts . " AS castcrew_posts
            INNER JOIN " . $ccwp_join_tablename . " AS ccwp_join
            ON castcrew_posts.ID = ccwp_join.cast_and_crew_id
            INNER JOIN " . $wpdb->posts . " AS production_posts
            ON production_posts.ID = ccwp_join.production_id
            WHERE (ccwp_join.type = 'cast' OR ccwp_join.type = 'crew')
            AND castcrew_posts.ID = " . $post_id . "
            ORDER BY production_posts.post_title
        ";
        
        $productions = $wpdb->get_results($query, ARRAY_A);
        
        if ($include_post_meta && count($productions) > 0) {
            foreach ($productions as $key1 => &$production) {
                $production_post_meta = get_post_meta($production['ccwp_production_post_id']);
                $production['post_meta'] = [];
                foreach ($production_post_meta as $key2 => $the_post_meta){
                    $production['post_meta'][$key2] = $the_post_meta[0];
                }
            }
            
            // Carbon::parse($production['post_meta']['_ccwp_production_date_start'])->toFormattedDateString();
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
        
        $roles = [];
        // TODO: what does this do?
        //foreach($productions as $key => $production) {
        //    if ($production['ID'] === $productions[$key + 1]['ID']) {
        //        $roles[$production['ID']][] = $production['ccwp_role'];
        //        $roles[$production['ID']][] = $production[$key + 1]['ccwp_role'];
        //    } else {
        //        $roles[$production['ID']][] = $production['ccwp_role'];
        //    }
        //    $roles = array_unique(array_values($roles[$production['ID']]));
        //}
        
        return [
            'roles' => $roles,
            'productions' => $productions,
        ];
    }
    
    public static function sortProductions(array $productions)
    {
        if (count($productions) > 1) {
            usort($productions_array, function($a, $b) {
                if (!empty($a['custom_order']) && !empty($b['custom_order'])) {
                    if ($a['custom_order'] == $b['custom_order']) {
                        return 0;
                    }
                    return ($a < $b) ? -1 : 1;
                } else if (!empty($a['custom_order']) && empty($b['custom_order'])) {
                    return 1;
                } else if (empty($a['custom_order']) && !empty($b['custom_order'])) {
                    return -1;
                } else {
                    return strcmp($a['role'], $b['role']); // TODO: Change to use production title
                }
            });
        }
        
        return $productions;
    }
}
