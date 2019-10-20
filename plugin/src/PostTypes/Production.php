<?php

namespace CurtainCallWP\PostTypes;

class Production extends CurtainCallPostType
{
    public static function getConfig(): array
    {
        return [
            'description'   => 'Displays your theatre company\'s productions and their relevant data',
            'labels'        => [
                'name'               => _x('Productions', 'post type general name'),
                'singular_name'      => _x('Production', 'post type singular name'),
                'add_new'            => _x('Add New', 'Production'),
                'add_new_item'       => __('Add New Production'),
                'edit_item'          => __('Edit Production'),
                'new_item'           => __('New Production'),
                'all_items'          => __('All Productions'),
                'view_item'          => __('View Production'),
                'search_items'       => __('Search productions'),
                'not_found'          => __('No productions found'),
                'not_found_in_trash' => __('No productions found in the Trash'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Productions',
            ],
            'public'        => true,
            'menu_position' => 2.5,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_production_seasons'
            ],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'productions',
            ],
        ];
    }
    
    public static function getSeasonsTaxonomyConfig(): array
    {
        // Add new taxonomy, make it hierarchical (like categories)
        return [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => _x('Seasons', 'taxonomy general name', 'curtain-call-wp'),
                'singular_name'     => _x('Season', 'taxonomy singular name', 'curtain-call-wp'),
                'search_items'      => __('Search Seasons', 'curtain-call-wp'),
                'all_items'         => __('All Seasons', 'curtain-call-wp'),
                'parent_item'       => __('Parent Season', 'curtain-call-wp'),
                'parent_item_colon' => __('Parent Season:', 'curtain-call-wp'),
                'edit_item'         => __('Edit Season', 'curtain-call-wp'),
                'update_item'       => __('Update Season', 'curtain-call-wp'),
                'add_new_item'      => __('Add New Season', 'curtain-call-wp'),
                'new_item_name'     => __('New Season Title', 'curtain-call-wp'),
                'menu_name'         => __('Seasons', 'curtain-call-wp'),
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug' => 'seasons',
            ],
        ];
    }
    
    public static function getCurrentCastAndCrewIds(int $production_id, string $type = 'cast')
    {
        global $wpdb;
        
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        $query = "
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
                ccwp_join.type = '". $type ."'
        ";
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        return $cast_and_crew;
    }
    
    public static function addCastAndCrew(int $production_id, string $type, array $castcrew = []): void
    {
        global $wpdb;
    
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
    
        // Get the current cast/crew array ( c/c array ) and the ids
        $current_castcrew = self::getCurrentCastAndCrewIds($production_id, $type);
    
        $current_castcrew_ids = [];
        if (!empty($current_castcrew)) {
            $current_castcrew_ids = array_values(array_map(function($current_castcrew_member) {
                return $current_castcrew_member['cast_and_crew_id'];
            }, $current_castcrew));
        }
    
        // Get the cast/crew array ( c/c array ) to be saved and the ids
        $new_castcrew_ids = [];
        if (!empty($castcrew)) {
            $new_castcrew_ids = array_values(array_map(function($new_castcrew_member) {
                return $new_castcrew_member['cast_and_crew_id'];
            }, $castcrew));
        }
    
        if (!empty($new_castcrew_ids)) {
            foreach ($castcrew as $new_castcrew_member) {
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
                        'type' => $type,
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
                        'type'             => $type,
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
}