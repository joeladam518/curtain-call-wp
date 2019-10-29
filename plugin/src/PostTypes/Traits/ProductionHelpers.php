<?php

namespace CurtainCallWP\PostTypes\Traits;

trait ProductionHelpers
{
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
        ";
        
        switch ($type) {
            case 'cast':
                $query .= "
                AND
                    ccwp_join.type = 'cast'
                ";
                break;
            case 'crew':
                $query .= "
                AND
                    ccwp_join.type = 'crew'
                ";
                break;
            case 'both':
            default:
                $query .= "
                AND
                    ( ccwp_join.type = 'cast' OR ccwp_join.type = 'crew' )
                ";
                break;
        }
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        return $cast_and_crew;
    }
    
    public static function getCastAndCrewForSelectBox()
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
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        return $cast_and_crew;
    }
    
    public static function getCastAndCrew(int $post_id, string $type = 'both', bool $include_post_meta = true): array
    {
        global $wpdb;
        
        $cast_and_crew = null;
        $ccwp_join_tablename = $wpdb->prefix . 'ccwp_castandcrew_production';
        
        $query = "
            SELECT
                castcrew_posts.*,
                ccwp_join.production_id AS ccwp_production_post_id,
                ccwp_join.cast_and_crew_id AS ccwp_castcrew_post_id,
                ccwp_join.type AS ccwp_type,
                ccwp_join.role AS ccwp_role,
                ccwp_join.custom_order AS ccwp_custom_order
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
        
        switch ($type) {
            case 'cast':
                $query .= "
                WHERE
                    ccwp_join.type = 'cast'
                ";
                break;
            case 'crew':
                $query .= "
                WHERE
                    ccwp_join.type = 'crew'
                ";
                break;
            case 'both':
            default:
                $query .= "
                WHERE
                    ( ccwp_join.type = 'cast' OR ccwp_join.type = 'crew' )
                ";
                break;
        }
        
        $query .= "
        AND
            production_posts.ID = ". $post_id ."
        ORDER BY
            ccwp_join.custom_order DESC, castcrew_posts.post_title ASC
        ";
        
        $cast_and_crew = $wpdb->get_results($query, ARRAY_A);
        
        if ($include_post_meta && count($cast_and_crew) > 0) {
            foreach ($cast_and_crew as &$castcrew) {
                $castcrew_post_meta = get_post_meta($castcrew['ccwp_castcrew_post_id']);
                $castcrew['post_meta'] = [];
                foreach ($castcrew_post_meta as $key2 => $the_post_meta){
                    $castcrew['post_meta'][$key2] = $the_post_meta[0];
                }
            }
        }
        
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
                        // Formatting of where clause data
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
        
        # array_diff() the current c/c array with new c/c array. this gives us the cast crew to be deleted
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
                $result = $wpdb->delete($ccwp_join_tablename, [
                    // Where Clauses
                    'production_id' => $production_id,
                    'cast_and_crew_id' => $todelete_castcrew_member_id,
                    'type' => $type,
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
    
    public static function sortCastAndCrew(array $cast_and_crew)
    {
        if (count($cast_and_crew) > 1) {
            usort($cast_and_crew, function($a, $b){
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
        }
        
        return $cast_and_crew;
    }
}