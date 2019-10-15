<?php

namespace CurtainCallWP\includes;

use Carbon\Carbon;

class CurtainCallHelpers 
{
    public static function get_custom_field($field_name = null, $post_id = null)
    {
        if (!empty($field_name)) {
            if (!empty($post_id)) {
                return get_post_meta($post_id, $field_name, true);
            } else {
                return get_post_meta(get_the_ID(), $field_name, true);
            }
        }
        
        return false;
    }
    
    public static function strip_http($str = null)
    {
        if (empty($str)) return false;
        
        $stripped_url = preg_replace('#^https?://#', '', $str);
        return $stripped_url;
    }
    
    public static function sortCastAndCrew($cast_and_crew_array)
    {
        if (!is_array($cast_and_crew_array) || !count($cast_and_crew_array)) {
            throw new \Exception('cast_and_crew_array must be an array and not empty... (#' . __LINE__ . ')');
            return false;
        }
            
        $success = usort ($cast_and_crew_array, function($a, $b){
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
                return strcmp($a['role'], $b['role']); // TODO: Change to use cast and crew member last name...
            }
        });
        
        /*
        if (!$success){
            throw new \Exception('Could not sort cast & crew. (#' . __LINE__ . ')');
            return false;
        }
        */
        
        return $cast_and_crew_array;
    }
    
    public static function sortProductions($productions_array)
    {
        if (!is_array($productions_array) || !count($productions_array)) {
            throw new \Exception('productions_array must be an array and not empty... (#' . __LINE__ . ')');
            return false;
        }
            
        $success = usort ($productions_array, function($a, $b) {
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
        
        /*
        if (!$success){
            throw new \Exception('Could not sort productions... (#' . __LINE__ . ')');
            return false;
        }
        */
        
        return $productions_array;
    }
    
    public static function get_all_cast_and_crew_for_select()
    {
        global $wpdb;
        
        $query = "
            SELECT
                castcrew_posts.ID,
                castcrew_posts.post_title,
                castcrew_posts.post_name,
                castcrew_posts.post_type,
                castcrew_posts.post_status
            FROM
                ". $wpdb->posts ." AS castcrew_posts
            WHERE
                castcrew_posts.post_type = 'ccwp_cast_and_crew'
            AND
                castcrew_posts.post_status = 'publish'
            ORDER BY
                castcrew_posts.post_title ASC
        ";
        
        //pr($query,1);
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        return $cast_and_crew;
    }
    
    public static function get_cast_and_crew($args = null, $options = [])
    {
        global $wpdb;
        
        $cast_and_crews = null;
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        if (is_numeric($args)) {
            $post_id = (int)$args;
        } else if (is_array($args)) {
            // TODO: All $args should be set to deafult value if false...
            $post_id = isset($args['post_id']) ? $args['post_id'] : null; 
        } else {
            throw new \Exception('You must pass the proper arguments to get_cast_and_crew_by_production() function');
            return false;
        }
        
        $get_post_meta = isset($options['get_post_meta']) ? $options['get_post_meta'] : false;
        
        $query = "";
        if (isset($args['select'])) {
            
            if (is_array($args['select'])) {
                $query .= "SELECT " . implode(', ', $args['select']);
            } else if (empty($args['select'])) {
                $query .= "SELECT * ";
            }
        
        } else {
            $query .= "
            SELECT
                castcrew_posts.*,
                ccwp_join.production_id AS ccwp_production_post_id,
                ccwp_join.cast_and_crew_id AS ccwp_castcrew_post_id,
                ccwp_join.type AS ccwp_type,
                ccwp_join.role AS ccwp_role,
                ccwp_join.custom_order AS ccwp_custom_order
            ";
        }
        
        $query .= "
            FROM 
                ". $wpdb->posts ." AS production_posts
            LEFT JOIN 
                ". $ccwp_join_tablename ." AS ccwp_join
            ON 
                production_posts.ID = ccwp_join.production_id
            LEFT JOIN
                ". $wpdb->posts ." AS castcrew_posts
            ON
                castcrew_posts.ID = ccwp_join.cast_and_crew_id
        ";
        
        // Select Custom type
        if (!empty($args['type']) && ($args['type'] === 'cast' || $args['type'] === 'crew')) {
            if ($args['type'] === 'cast') {
                $query .= "
                WHERE 
                    ccwp_join.type = 'cast' 
                ";
            } else {
                $query .= "
                WHERE 
                    ccwp_join.type = 'crew' 
                ";
            }
        } else {
            $query .= "
            WHERE 
                ( ccwp_join.type = 'cast' OR ccwp_join.type = 'crew' ) 
            ";
        }
        
        // If post_id is set find castcrew related to post
        if (isset($post_id)) { 
            $query .= "
            AND production_posts.ID = ". $post_id ." 
            ";
        }
        
        // Set custom WHERE clauses
        if (isset($args['where'])) {
            if (is_array($args['where'])) {
                $query .= implode(" \r", $args['where']);
            } else {
                $query .= $args['where'];
            }
        }
        
        // Set custom ORDER BY
        if (isset($args['order_by'])) {
            if (is_array($args['order_by'])) {
                $query .= "ORDER BY " . implode(', ', $args['order_by']);
            } else if (empty($args['order_by'])) {
                // do nothing... 
            }
        } else {
            $query .= "
            ORDER BY ccwp_join.custom_order DESC, castcrew_posts.post_title ASC
            ";
        }
        
        // Set custom LIMIT
        if (!isset($args['limit']) && is_int($args['limit'])) {
            $query .= "
            LIMIT ". $args['limit'] ."
            ";
        }
        
        //pr($query,1);

        $cast_and_crews = $wpdb->get_results($query, ARRAY_A);
        
        if (count($cast_and_crews) && $get_post_meta) {
            foreach ($cast_and_crews as $key1 => &$castcrew) {           
                $castcrew_post_meta = get_post_meta($castcrew['ccwp_castcrew_post_id']);
                
                $castcrew['post_meta'] = [];
                foreach ($castcrew_post_meta as $key2 => $the_post_meta){
                    $castcrew['post_meta'][$key2] = $the_post_meta[0];
                }
            }
        }
        
        return $cast_and_crews;
    }
    
    public static function get_current_cast_and_crew_ids_for_production($production_id, $castcrew_type = 'cast')
    {
        global $wpdb; 
       
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
       
        $query .= "
            SELECT
                ccwp_join.production_id,
                ccwp_join.cast_and_crew_id,
                ccwp_join.type,
                ccwp_join.role,
                ccwp_join.custom_order
            FROM 
                ". $wpdb->posts ." AS production_posts
            LEFT JOIN 
                ". $ccwp_join_tablename ." AS ccwp_join
            ON 
                production_posts.ID = ccwp_join.production_id
            LEFT JOIN
                ". $wpdb->posts ." AS castcrew_posts
            ON
                castcrew_posts.ID = ccwp_join.cast_and_crew_id
            WHERE 
                ccwp_join.production_id = ". $production_id ."
            AND
                ccwp_join.type = '". $castcrew_type ."'
        ";
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        return $cast_and_crew;
    }
    
    public static function save_cast_and_crew_to_production($production_id, $new_castcrew, $castcrew_type) 
    {
        global $wpdb;
        
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        // Get the current cast/crew array ( c/c array ) and the ids
        $current_castcrew = self::get_current_cast_and_crew_ids_for_production($production_id, $castcrew_type);
        
        $current_castcrew_ids = [];
        if (!empty($current_castcrew)) {
            $current_castcrew_ids = array_values(array_map(function($current_castcrew_member) {
                return $current_castcrew_member['cast_and_crew_id'];
            }, $current_castcrew));
        }
        
        // Get the cast/crew array ( c/c array ) to be saved and the ids
        $new_castcrew_ids = [];
        if (!empty($new_castcrew)) {
            $new_castcrew_ids = array_values(array_map(function($new_castcrew_member) {
                return $new_castcrew_member['cast_and_crew_id'];
            }, $new_castcrew));
        }
        
        if (!empty($new_castcrew_ids)) {
            foreach ($new_castcrew as $new_castcrew_member) {
                if (in_array($new_castcrew_member['cast_and_crew_id'], $current_castcrew_ids)) {
                    
                    # if in both the new c/c array and the current c/c array update
                    
                    $result = $wpdb->update($ccwp_join_tablename, [
                        // Data to be updated
                        'role' => !empty($new_castcrew_member['role']) ? sanitize_text_field($new_castcrew_member['role']) : null,
                        'custom_order' => is_numeric($new_castcrew_member['custom_order']) ? (int)$new_castcrew_member['custom_order'] : null,
                    ], [
                        // Where Clauses
                        'production_id' => $production_id,
                        'cast_and_crew_id' => $new_castcrew_member['cast_and_crew_id'],
                        'type' => $castcrew_type,
                    ], [
                        // Format of the data to be inserted
                        '%s',
                        '%d',
                    ], [
                        // Fomatting of where clause data
                        '%d',
                        '%d',
                        '%s',
                    ]);
                    
                    /*
                    if ($result === false) {
                        pr($wpdb->last_error);
                        echo "<br><br>\n\n";
                        pr($wpdb->last_query);
                        exit(1);
                    }
                    */
                    
                    $wpdb->flush();
    
                } else {
                    
                    # if in the new c/c array but not in the current c/c array insert
                    
                    $result = $wpdb->insert($ccwp_join_tablename, [
                        // Data to be inserted
                        'production_id'    => $production_id,
                        'cast_and_crew_id' => $new_castcrew_member['cast_and_crew_id'],
                        'type'             => $castcrew_type,
                        'role'             => sanitize_text_field($new_castcrew_member['role']),
                        'custom_order'     => is_numeric($new_castcrew_member['custom_order']) ? (int)$new_castcrew_member['custom_order'] : null,
                    ], [
                        // Format of data to be inserted
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                    ]);
                    
                    /*
                    if ($result === false) {
                        pr($wpdb->last_error);
                        echo "<br><br>\n\n";
                        pr($wpdb->last_query);
                        exit(1);
                    }
                    */
                    
                    $wpdb->flush();
                }
            }        
        }

        # array_diff() the current c/c array with new c/c array. this give the cast crew to be deleted
        if (!empty($current_castcrew_ids) && !empty($new_castcrew_ids)) {
            $todelete_castcrew_ids = array_values(array_diff($current_castcrew_ids, $new_castcrew_ids));
        } else if (!empty($current_castcrew_ids) && empty($new_castcrew_ids)) {
            $todelete_castcrew_ids = $current_castcrew_ids;
        } else {
            $todelete_castcrew_ids = null;
        }
        
        if (!empty($todelete_castcrew_ids) && is_array($todelete_castcrew_ids)) {
            
            # if in the current c/c array but not in the to be saved array delete 
            
            foreach ($todelete_castcrew_ids as $todelete_castcrew_member_id) {
                $result = $wpdb->delete( $ccwp_join_tablename, [
                    // Where Clauses
                    'production_id' => $production_id,
                    'cast_and_crew_id' => $todelete_castcrew_member_id,
                    'type' => $castcrew_type,
                ], [
                    // Format of where clauses data
                    '%d',
                    '%d',
                    '%s',
                ]);
                
                /*
                if ($result === false) {
                    pr($wpdb->last_error);
                    echo "<br><br>\n\n";
                    pr($wpdb->last_query);
                    exit(1);
                }
                */
                
                $wpdb->flush();
            }
        }
    }
    
    public static function get_productions($args, $options = [])
    {
        global $wpdb;
        
        $productions = null;
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        if (is_numeric($args)) {
            $post_id = (int)$args;
        } else if (is_array($args)) {
            // TODO: All $args should be set to deafult value if false...
            $post_id = isset($args['post_id']) ? $args['post_id'] : null; 
        } else {
            throw new \Exception('You must pass the proper arguments to get_cast_and_crew_by_production() function');
            return false;
        }
        
        $get_post_meta = isset($options['get_post_meta']) ? $options['get_post_meta'] : false;
        
        $query .= "
            SELECT
            	production_posts.*,
            	ccwp_join.production_id AS ccwp_production_post_id,
            	ccwp_join.cast_and_crew_id AS ccwp_castcrew_post_id,
            	ccwp_join.type AS ccwp_type,
            	ccwp_join.role AS ccwp_role,
            	ccwp_join.custom_order AS ccwp_custom_order       
            FROM ". $wpdb->posts ." AS castcrew_posts
            INNER JOIN ". $ccwp_join_tablename ." AS ccwp_join
            ON castcrew_posts.ID = ccwp_join.cast_and_crew_id
            INNER JOIN ". $wpdb->posts ." AS production_posts
            ON production_posts.ID = ccwp_join.production_id
            WHERE (ccwp_join.type = 'cast' OR ccwp_join.type = 'crew') 
            AND castcrew_posts.ID = ". $post_id ." 
            ORDER BY production_posts.post_title ASC
        ";
        
        // Set custom LIMIT
        if (!isset($args['limit']) && is_int($args['limit'])) {
            $query .= "
            LIMIT ". $args['limit'] ."
            ";
        }
        
        //pr($query,1);

        $productions = $wpdb->get_results($query, ARRAY_A);
        
        if (count($productions) && $get_post_meta) {
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
        /*foreach($productions as $key => $production) {
            if ($production['ID'] === $productions[$key + 1]['ID']) {
                $roles[$production['ID']][] = $production['ccwp_role'];
                $roles[$production['ID']][] = $production[$key + 1]['ccwp_role']; 
            } else {
                $roles[$production['ID']][] = $production['ccwp_role'];
            }
            $roles = array_unique(array_values($roles[$production['ID']]));
        }*/
         
        return [ 
            'roles' => $roles,
            'productions' => $productions,
        ];
    } 
}
