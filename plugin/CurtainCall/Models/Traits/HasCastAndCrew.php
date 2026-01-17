<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Support\Query;
use Illuminate\Support\Arr;
use Throwable;
use wpdb;

/**
 * @property-read int $ID
 */
trait HasCastAndCrew
{
    /**
     * @param string $type
     * @return array
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getCastCrewIds(string $type = 'both'): array
    {
        $wpdb = ccwp_get_wpdb();
        $pivotTableName = CurtainCallPivot::getTableNameWithAlias();
        $query = Query::raw([
            Query::select([
                'ccwp_join.production_id',
                'ccwp_join.cast_and_crew_id',
                'ccwp_join.type',
            ]),
            "FROM `{$wpdb->posts}` AS `production_posts`",
            'INNER JOIN ' . $pivotTableName . ' ON `production_posts`.`ID` = `ccwp_join`.`production_id`',
            "INNER JOIN `{$wpdb->posts}` AS `castcrew_posts` ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`",
            'WHERE `production_posts`.`ID` = %d',
            Query::wherePivotType($type, 'AND'),
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, $this->ID);
        /** @var list<array<string, mixed>> $castcrew */
        $castcrew = $wpdb->get_results($sql, ARRAY_A);

        if (count($castcrew) === 0) {
            return [];
        }

        $ids = Arr::map($castcrew, static fn(array $member) => $member['cast_and_crew_id'] ?? null);

        return array_unique(array_filter($ids));
    }

    /**
     * @return array<int, string>
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getCastCrewNames(): array
    {
        $wpdb = ccwp_get_wpdb();

        $query = Query::raw([
            Query::select([
                'castcrew_posts.ID',
                'castcrew_posts.post_title',
                'castcrew_posts.post_type',
                'castcrew_posts.post_status',
            ]),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            'WHERE `castcrew_posts`.`post_type` = %s',
            'AND `castcrew_posts`.`post_status` = %s',
            'ORDER BY `castcrew_posts`.`post_title`',
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, 'ccwp_cast_and_crew', 'publish');
        /** @var list<array<string, mixed>> $names */
        $names = $wpdb->get_results($sql, ARRAY_A);

        if (count($names) === 0) {
            return [];
        }

        return array_column($names, 'post_title', 'ID');
    }

    /**
     * @param string $type
     * @return array<int, CastAndCrew>
     * @throws Throwable
     */
    public function getCastAndCrew(string $type = 'both'): array
    {
        $wpdb = ccwp_get_wpdb();
        $pivotTableName = CurtainCallPivot::getTableNameWithAlias();
        $query = Query::raw([
            'SELECT ' . Query::selectCastAndCrew(),
            "FROM `{$wpdb->posts}` AS `production_posts`",
            'INNER JOIN ' . $pivotTableName . ' ON `production_posts`.`ID` = `ccwp_join`.`production_id`',
            "INNER JOIN `{$wpdb->posts}` AS `castcrew_posts` ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`",
            'WHERE `production_posts`.`ID` = %d',
            Query::wherePivotType($type, 'AND'),
            'ORDER BY `ccwp_join`.`custom_order` DESC, `castcrew_posts`.`post_title` ASC;',
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, $this->ID);
        /** @var list<array<string, mixed>> $rows */
        $rows = $wpdb->get_results($sql, ARRAY_A);

        if (!$rows) {
            return [];
        }

        return collect($rows)
            ->map(static fn(array $row) => CastAndCrew::fromArray($row))
            ->sort(
                static fn(CastAndCrew $a, CastAndCrew $b) => (
                    [$a->getJoinOrder(), $a->name_last] <=> [$b->getJoinOrder(), $b->name_last]
                ),
            )
            ->values()
            ->all();
    }

    /**
     * @param string $type
     * @param list<array<string, mixed>> $castcrew
     * @return void
     * @throws Throwable
     */
    public function saveCastAndCrew(string $type, array $castcrew = []): void
    {
        $currentIds = $this->getCastCrewIds($type);

        /** @var list<int> $newIds */
        $newIds = [];
        if (!empty($castcrew)) {
            $newIds = Arr::map($castcrew, static fn(array $member) => $member['cast_and_crew_id'] ?? null);
            $newIds = array_filter($newIds);
        }

        // insert / update the production's castcrew
        if (count($newIds) > 0) {
            foreach ($castcrew as $member) {
                $castcrewId = is_numeric($member['cast_and_crew_id'])
                    ? (int) $member['cast_and_crew_id']
                    : null;

                if (!$castcrewId) {
                    continue;
                }

                $role = isset($member['role']) && is_string($member['role'])
                    ? sanitize_text_field($member['role'])
                    : null;
                $order = isset($member['custom_order']) && is_numeric($member['custom_order'])
                    ? (int) $member['custom_order']
                    : null;

                if (in_array($member['cast_and_crew_id'], $currentIds, true)) {
                    // if in both $newIds and $currentIds, update
                    $this->updateCastCrew($castcrewId, $type, $role, $order);
                } else {
                    // if in $newIds but not in $currentIds, insert
                    $this->insertCastCrew($castcrewId, $type, $role, $order);
                }
            }
        }

        // Determine the production's castcrew to be deleted
        /** @var list<int> $toDeleteIds */
        $toDeleteIds = [];
        if (!empty($currentIds)) {
            if (empty($newIds)) {
                $toDeleteIds = $currentIds;
            } else {
                $toDeleteIds = array_values(array_diff($currentIds, $newIds));
            }
        }

        // Delete the to be deleted
        if (count($toDeleteIds) > 0) {
            // if in the current c/c array but not in the to be saved array delete
            foreach ($toDeleteIds as $castcrewId) {
                $this->deleteCastCrew($castcrewId, $type);
            }
        }
    }

    /**
     * @param int $id
     * @param string $type
     * @param string|null $role
     * @param int|null $customOrder
     * @return void
     * @throws Throwable
     */
    protected function insertCastCrew(int $id, string $type, ?string $role, ?int $customOrder = null): void
    {
        $wpdb = ccwp_get_wpdb();

        $wpdb->insert(
            CurtainCallPivot::getTableName(),
            [
                // Data to be inserted
                'production_id' => $this->ID,
                'cast_and_crew_id' => $id,
                'type' => $type,
                'role' => $role,
                'custom_order' => $customOrder,
            ],
            [
                // Format of data to be inserted
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
            ],
        );

        $wpdb->flush();
    }

    /**
     * @param int $id
     * @param string $type
     * @param string|null $role
     * @param int|null $customOrder
     * @return void
     * @throws Throwable
     */
    protected function updateCastCrew(int $id, string $type, ?string $role, ?int $customOrder = null): void
    {
        $wpdb = ccwp_get_wpdb();

        $wpdb->update(
            CurtainCallPivot::getTableName(),
            [
                // Data to be updated
                'role' => $role,
                'custom_order' => $customOrder,
            ],
            [
                // Where Clauses
                'production_id' => $this->ID,
                'cast_and_crew_id' => $id,
                'type' => $type,
            ],
            [
                // Format of the data to be inserted
                '%s',
                '%d',
            ],
            [
                // Formatting of where clause data
                '%d',
                '%d',
                '%s',
            ],
        );

        $wpdb->flush();
    }

    /**
     * @param int $id
     * @param string $type
     * @return void
     * @throws Throwable
     */
    protected function deleteCastCrew(int $id, string $type): void
    {
        $wpdb = ccwp_get_wpdb();

        $wpdb->delete(
            CurtainCallPivot::getTableName(),
            [
                // Where Clauses
                'production_id' => $this->ID,
                'cast_and_crew_id' => $id,
                'type' => $type,
            ],
            [
                // Format of where clauses data
                '%d',
                '%d',
                '%s',
            ],
        );

        $wpdb->flush();
    }
}
