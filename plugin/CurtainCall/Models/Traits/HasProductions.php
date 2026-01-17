<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Models\Production;
use CurtainCall\Support\Query;
use Illuminate\Support\Arr;
use Throwable;
use wpdb;

/**
 * @property-read int $ID
 */
trait HasProductions
{
    /**
     * @param string $type
     * @return list<int>
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getProductionIds(string $type = 'both'): array
    {
        $wpdb = ccwp_get_wpdb();
        $pivotTableName = CurtainCallPivot::getTableNameWithAlias();
        $query = Query::raw([
            Query::select([
                'ccwp_join.production_id',
                'ccwp_join.cast_and_crew_id',
                'ccwp_join.type',
            ]),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            'INNER JOIN ' . $pivotTableName . ' ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`',
            "INNER JOIN `{$wpdb->posts}` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`",
            'WHERE `castcrew_posts`.`ID` = %d',
            Query::wherePivotType($type, 'AND'),
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, $this->ID);
        /** @var list<array<string, mixed>> $productions */
        $productions = $wpdb->get_results($sql, ARRAY_A);

        if (count($productions) === 0) {
            return [];
        }

        $ids = Arr::map($productions, static fn(array $production) => $production['production_id'] ?? null);

        /** @var list<int> $result */
        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @return array<int, string>
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getProductionNames(): array
    {
        $wpdb = ccwp_get_wpdb();

        $query = Query::raw([
            Query::select([
                'production_posts.ID',
                'production_posts.post_title',
                'production_posts.post_type',
                'production_posts.post_status',
            ]),
            "FROM `{$wpdb->posts}` AS `production_posts`",
            'WHERE `production_posts`.`post_type` = %s',
            'AND `production_posts`.`post_status` = %s',
            'ORDER BY `production_posts`.`post_title`',
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, 'ccwp_production', 'publish');
        /** @var list<array<string, mixed>> $names */
        $names = $wpdb->get_results($sql, ARRAY_A);

        if (count($names) === 0) {
            return [];
        }

        /** @var array<int, string> $result */
        return array_column($names, 'post_title', 'ID');
    }

    /**
     * @return array<int, Production>
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getProductions(): array
    {
        $wpdb = ccwp_get_wpdb();
        $pivotTableName = CurtainCallPivot::getTableNameWithAlias();
        $query = Query::raw([
            'SELECT ' . Query::selectProductions(),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            'INNER JOIN ' . $pivotTableName . ' ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`',
            "INNER JOIN `{$wpdb->posts}` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`",
            'WHERE `castcrew_posts`.`ID` = %d',
            'ORDER BY `production_posts`.`post_title`;',
        ]);

        /** @var string $sql */
        $sql = $wpdb->prepare($query, $this->ID);
        /** @var list<array<string, mixed>> $rows */
        $rows = $wpdb->get_results($sql, ARRAY_A);

        if (!$rows) {
            return [];
        }

        /** @var array<int, Production> $result */
        return collect($rows)
            ->map(static fn(array $row) => Production::fromArray($row))
            ->sort(static fn(Production $a, Production $b) => [$b->date_start, $a->name] <=> [$a->date_start, $b->name])
            ->values()
            ->all();
    }

    /**
     * @param string $type
     * @param list<array<string, mixed>> $productions
     * @return void
     * @throws Throwable
     */
    public function saveProductions(string $type, array $productions = []): void
    {
        $currentIds = $this->getProductionIds($type);

        /** @var list<int> $newIds */
        $newIds = [];
        if (!empty($productions)) {
            $newIds = Arr::map($productions, static fn(array $production) => $production['production_id'] ?? null);
            $newIds = array_filter($newIds);
        }

        // insert / update the productions
        if (count($newIds) > 0) {
            foreach ($productions as $production) {
                $productionId = is_numeric($production['production_id'])
                    ? (int) $production['production_id']
                    : null;

                if (!$productionId) {
                    continue;
                }

                $role = isset($production['role']) && is_string($production['role'])
                    ? sanitize_text_field($production['role'])
                    : null;
                $order = isset($production['custom_order']) && is_numeric($production['custom_order'])
                    ? (int) $production['custom_order']
                    : null;

                if (in_array($production['production_id'], $currentIds, true)) {
                    // if in both $newIds and $currentIds, update
                    $this->updateProduction($productionId, $type, $role, $order);
                } else {
                    // if in $newIds but not in $currentIds, insert
                    $this->insertProduction($productionId, $type, $role, $order);
                }
            }
        }

        // Determine the productions to be deleted
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
            foreach ($toDeleteIds as $productionId) {
                $this->deleteProduction($productionId, $type);
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
    protected function insertProduction(int $id, string $type, ?string $role, ?int $customOrder = null): void
    {
        $wpdb = ccwp_get_wpdb();

        $wpdb->insert(
            CurtainCallPivot::getTableName(),
            [
                // Data to be inserted
                'production_id' => $id,
                'cast_and_crew_id' => $this->ID,
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
    protected function updateProduction(int $id, string $type, ?string $role, ?int $customOrder = null): void
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
                'production_id' => $id,
                'cast_and_crew_id' => $this->ID,
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
    protected function deleteProduction(int $id, string $type): void
    {
        $wpdb = ccwp_get_wpdb();

        $wpdb->delete(
            CurtainCallPivot::getTableName(),
            [
                // Where Clauses
                'production_id' => $id,
                'cast_and_crew_id' => $this->ID,
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
