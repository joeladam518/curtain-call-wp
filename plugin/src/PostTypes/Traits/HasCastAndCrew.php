<?php

namespace CurtainCallWP\PostTypes\Traits;

use CurtainCallWP\PostTypes\Production;

trait HasCastAndCrew
{
    public function getCurrentCastCrewIds(string $type = 'both')
    {
        global $wpdb;
        
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
                ". static::getJoinTableName() ." AS ccwp_join
            ON
                production_posts.ID = ccwp_join.production_id
            LEFT JOIN
                ". $wpdb->posts ." AS castcrew_posts
            ON
                castcrew_posts.ID = ccwp_join.cast_and_crew_id
            WHERE
                ccwp_join.production_id = ". $this->ID ."
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
    
        $current_castcrew = $wpdb->get_results($query, ARRAY_A);
    
        $current_castcrew_ids = [];
        if (!empty($current_castcrew)) {
            $current_castcrew_ids = array_values(array_map(function($castcrew_member) {
                return $castcrew_member['cast_and_crew_id'];
            }, $current_castcrew));
        }
        
        return $current_castcrew_ids;
    }
    
    public function getSelectBoxCastCrew()
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
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    public function getCastAndCrew(string $type = 'both', $include_post_meta = true): array
    {
        global $wpdb;
        
        $cast_and_crew = null;
        
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
                ". static::getJoinTableName() ." AS ccwp_join
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
            production_posts.ID = ". $this->ID  ."
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
    
    public function saveCastAndCrew(string $type, array $castcrew_to_upsert = []): void
    {
        // Get the currently saved cast/crew ids
        $current_castcrew_ids = $this->getCurrentCastCrewIds($type);
        
        // Get the ids of the cast/crew to be upserted
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
                    $this->updateCastCrew($castcrew_id, $type, $role, $custom_order);
                } else {
                    # if in the new c/c array but not in the current c/c array insert
                    $this->insertCastCrew($castcrew_id, $type, $role, $custom_order);
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
        
        # delete the to be deleted
        if (is_array($castcrew_to_delete_ids) && count($castcrew_to_delete_ids) > 0) {
            # if in the current c/c array but not in the to be saved array delete
            foreach ($castcrew_to_delete_ids as $castcrew_id) {
                $this->deleteCastCrew($castcrew_id, $type);
            }
        }
    }
    
    protected function insertCastCrew(int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null)
    {
        global $wpdb;
        
        $result = $wpdb->insert(static::getJoinTableName(), [
            // Data to be inserted
            'production_id'    => $this->ID,
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
    
    protected function updateCastCrew(int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null)
    {
        global $wpdb;
        
        $result = $wpdb->update(static::getJoinTableName(), [
            // Data to be updated
            'role'         => $role,
            'custom_order' => $custom_order,
        ], [
            // Where Clauses
            'production_id'    => $this->ID,
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
    
    protected function deleteCastCrew(int $castcrew_id, string $type)
    {
        global $wpdb;
        
        $result = $wpdb->delete(static::getJoinTableName(), [
            // Where Clauses
            'production_id' => $this->ID,
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
}