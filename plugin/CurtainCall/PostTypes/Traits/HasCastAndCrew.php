<?php

namespace CurtainCall\PostTypes\Traits;

use CurtainCall\Helpers\CurtainCallHelper;
use CurtainCall\Helpers\QueryHelper;
use CurtainCall\PostTypes\CastAndCrew;
use Throwable;
use wpdb;

trait HasCastAndCrew
{
    /**
     * @global wpdb $wpdb
     * @param string $type
     * @return array
     */
    public function getCastCrewIds(string $type = 'both'): array
    {
        global $wpdb;

        $query = "
            SELECT
                `ccwp_join`.`production_id`,
                `ccwp_join`.`cast_and_crew_id`,
                `ccwp_join`.`type`
            FROM `". $wpdb->posts ."` AS `production_posts`
            INNER JOIN ". static::getJoinTableNameWithAlias() ." ON `production_posts`.`ID` = `ccwp_join`.`production_id`
            INNER JOIN `". $wpdb->posts ."` AS `castcrew_posts` ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`
            WHERE `production_posts`.`ID` = %d
        ";

        $query .= QueryHelper::whereCCWPJoinType($type, 'AND');

        $sql = $wpdb->prepare($query, $this->ID);
        $castcrew = $wpdb->get_results($sql, ARRAY_A);

        $castcrew_ids = [];
        if (!empty($castcrew)) {
            $castcrew_ids = array_values(array_map(function($castcrew_member) {
                return $castcrew_member['cast_and_crew_id'];
            }, $castcrew));
        }

        return $castcrew_ids;
    }

    /**
     * @global wpdb $wpdb
     * @return array
     */
    public function getCastCrewNames(): array
    {
        global $wpdb;

        $query = "
            SELECT
                `castcrew_posts`.`ID`,
                `castcrew_posts`.`post_title`,
                `castcrew_posts`.`post_type`,
                `castcrew_posts`.`post_status`
            FROM `". $wpdb->posts ."` AS `castcrew_posts`
            WHERE `castcrew_posts`.`post_type` = %s
            AND `castcrew_posts`.`post_status` = %s
            ORDER BY `castcrew_posts`.`post_title`
        ";

        $sql = $wpdb->prepare($query, 'ccwp_cast_and_crew', 'publish');
        $castcrew_names = $wpdb->get_results($sql, ARRAY_A);

        if (count($castcrew_names) > 0) {
            $castcrew_names = array_column($castcrew_names, 'post_title', 'ID');
        }

        return $castcrew_names;
    }

    /**
     * @param string $type
     * @return array
     * @throws Throwable
     */
    public function getCastAndCrew(string $type = 'both'): array
    {
        global $wpdb;

        $whereJoinTypeClause = QueryHelper::whereCCWPJoinType($type, 'AND');

        $query = "
            SELECT ". QueryHelper::selectCastAndCrew() ."
            FROM `". $wpdb->posts ."` AS `production_posts`
            INNER JOIN ". static::getJoinTableNameWithAlias() ." ON `production_posts`.`ID` = `ccwp_join`.`production_id`
            INNER JOIN `". $wpdb->posts ."` AS `castcrew_posts` ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`
            WHERE `production_posts`.`ID` = %d
            ". $whereJoinTypeClause ."
            ORDER BY `ccwp_join`.`custom_order` DESC, `castcrew_posts`.`post_title` ASC
        ";

        $sql = $wpdb->prepare($query, $this->ID);
        $castcrew = $wpdb->get_results($sql, ARRAY_A);

        if (count($castcrew) > 0) {
            /** @var array|CastAndCrew[] $castcrew */
            $castcrew = CurtainCallHelper::convertToCurtainCallPosts($castcrew);
        }

        return $castcrew;
    }

    /**
     * @param string $type
     * @param array $castcrew_to_upsert
     * @return void
     */
    public function saveCastAndCrew(string $type, array $castcrew_to_upsert = []): void
    {
        // Get the currently saved cast/crew ids
        $current_castcrew_ids = $this->getCastCrewIds($type);

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

    /**
     * @global wpdb $wpdb
     * @param int $castcrew_id
     * @param string $type
     * @param string|null $role
     * @param int|null $custom_order
     * @return void
     */
    protected function insertCastCrew(int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null): void
    {
        global $wpdb;

        $wpdb->insert(static::getJoinTableName(), [
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

    /**
     * @global wpdb $wpdb
     * @param int $castcrew_id
     * @param string $type
     * @param string|null $role
     * @param int|null $custom_order
     * @return void
     */
    protected function updateCastCrew(int $castcrew_id, string $type, ?string $role = null, ?int $custom_order = null): void
    {
        global $wpdb;

        $wpdb->update(static::getJoinTableName(), [
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

    /**
     * @global wpdb $wpdb
     * @param int $castcrew_id
     * @param string $type
     * @return void
     */
    protected function deleteCastCrew(int $castcrew_id, string $type): void
    {
        global $wpdb;

        $wpdb->delete(static::getJoinTableName(), [
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
