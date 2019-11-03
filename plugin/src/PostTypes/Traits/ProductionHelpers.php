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
    
    public static function saveCastAndCrew(int $production_id, string $type, array $castcrew_to_upsert = []): void
    {
        // Get the current cast/crew array ( c/c array ) and the ids
        $current_castcrew = static::getCurrentCastAndCrewIds($production_id, $type);
        
        $current_castcrew_ids = [];
        if (!empty($current_castcrew)) {
            $current_castcrew_ids = array_values(array_map(function($castcrew_member) {
                return $castcrew_member['cast_and_crew_id'];
            }, $current_castcrew));
        }
        
        // Get the cast/crew array ( c/c array ) to be saved and the ids
        $new_castcrew_ids = [];
        if (!empty($castcrew_to_upsert)) {
            $new_castcrew_ids = array_values(array_map(function($castcrew_member) {
                return $castcrew_member['cast_and_crew_id'];
            }, $castcrew_to_upsert));
        }
        
        if (!empty($new_castcrew_ids)) {
            foreach ($castcrew_to_upsert as $castcrew_member) {
                $castcrew_id  = is_numeric($castcrew_member['cast_and_crew_id'])
                    ? (int)$castcrew_member['cast_and_crew_id']
                    : null;
                $role = !empty($castcrew_member['role'])
                    ? sanitize_text_field($castcrew_member['role'])
                    : null;
                $custom_order = is_numeric($castcrew_member['custom_order'])
                    ? (int)$castcrew_member['custom_order']
                    : null;
                
                if (in_array($castcrew_member['cast_and_crew_id'], $current_castcrew_ids)) {
                    # if in both the new c/c array and the current c/c array update
                    static::updateCastCrew($production_id, $castcrew_id, $type, $role, $custom_order);
                } else {
                    # if in the new c/c array but not in the current c/c array insert
                    static::insertCastCrew($production_id, $castcrew_id, $type, $role, $custom_order);
                }
            }
        }
        
        # array_diff() the current c/c array with new c/c array. this gives us the cast crew to be deleted
        if (!empty($current_castcrew_ids) && !empty($new_castcrew_ids)) {
            $castcrew_to_delete_ids = array_values(array_diff($current_castcrew_ids, $new_castcrew_ids));
        } else if (!empty($current_castcrew_ids) && empty($new_castcrew_ids)) {
            $castcrew_to_delete_ids = $current_castcrew_ids;
        } else {
            $castcrew_to_delete_ids = [];
        }
        
        if (is_array($castcrew_to_delete_ids) && count($castcrew_to_delete_ids) > 0) {
            # if in the current c/c array but not in the to be saved array delete
            foreach ($castcrew_to_delete_ids as $castcrew_id) {
                static::deleteCastCrew($production_id, $castcrew_id, $type);
            }
        }
    }
    
    protected static function insertCastCrew(int $production_id, int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null)
    {
        global $wpdb;
        
        $result = $wpdb->insert(static::getJoinTableName(), [
            // Data to be inserted
            'production_id'    => $production_id,
            'cast_and_crew_id' => $castcrew_id,
            'type'             => $type,
            'role'             => $role,
            'custom_order'     => $custom_order,
        ], [
            // Format of data to be inserted
            '%d',
            '%d',
            '%s',
            '%s',
            '%d',
        ]);
    
        $wpdb->flush();
    }
    
    protected static function updateCastCrew(int $production_id, int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null)
    {
        global $wpdb;
        
        $result = $wpdb->update(static::getJoinTableName(), [
            // Data to be updated
            'role'         => $role,
            'custom_order' => $custom_order,
        ], [
            // Where Clauses
            'production_id'    => $production_id,
            'cast_and_crew_id' => $castcrew_id,
            'type'             => $type,
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
    
    
        $wpdb->flush();
    }
    
    protected static function deleteCastCrew(int $production_id, int $castcrew_id, string $type)
    {
        global $wpdb;
        
        $result = $wpdb->delete(static::getJoinTableName(), [
            // Where Clauses
            'production_id' => $production_id,
            'cast_and_crew_id' => $castcrew_id,
            'type' => $type,
        ], [
            // Format of where clauses data
            '%d',
            '%d',
            '%s',
        ]);
    
        $wpdb->flush();
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