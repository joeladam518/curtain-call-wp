<?php

declare(strict_types=1);

namespace CurtainCall\Models\Traits;

use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\Query;
use Illuminate\Support\Arr;
use Throwable;
use wpdb;

trait HasProductions
{
    /**
     * @param string $type
     * @return array
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getProductionIds(string $type = 'both'): array
    {
        $wpdb = ccwp_get_wpdb();

        $query = Query::raw([
            Query::select([
                'ccwp_join.production_id',
                'ccwp_join.cast_and_crew_id',
                'ccwp_join.type',
            ]),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            'INNER JOIN '
                . CurtainCallPivot::getTableNameWithAlias()
                . ' ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`',
            "INNER JOIN `{$wpdb->posts}` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`",
            'WHERE `castcrew_posts`.`ID` = %d',
            Query::wherePivotType($type, 'AND'),
        ]);

        $sql = $wpdb->prepare($query, $this->ID);
        $productions = $wpdb->get_results($sql, ARRAY_A);

        if (count($productions) === 0) {
            return [];
        }

        $ids = Arr::map($productions, static fn($production) => $production['production_id'] ?? null);

        return array_unique(array_filter($ids));
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

        $sql = $wpdb->prepare($query, 'ccwp_production', 'publish');
        $names = $wpdb->get_results($sql, ARRAY_A);

        if (count($names) === 0) {
            return [];
        }

        return array_column($names, 'post_title', 'ID');
    }

    /**
     * @return Production[]
     * @throws Throwable
     * @global wpdb $wpdb
     */
    public function getProductions(): array
    {
        $wpdb = ccwp_get_wpdb();

        $query = Query::raw([
            'SELECT ' . Query::selectProductions(),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            'INNER JOIN '
                . CurtainCallPivot::getTableNameWithAlias()
                . ' ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`',
            "INNER JOIN `{$wpdb->posts}` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`",
            'WHERE `castcrew_posts`.`ID` = %d',
            'ORDER BY `production_posts`.`post_title`;',
        ]);

        $sql = $wpdb->prepare($query, $this->ID);
        $productions = $wpdb->get_results($sql, ARRAY_A);

        if (count($productions) === 0) {
            return [];
        }

        /** @var Production[] $productions */
        $productions = static::toCurtainCallPosts($productions);

        usort($productions, static function (Production $productionA, Production $productionB) {
            if ($productionA->hasStartDate() && !$productionB->hasStartDate()) {
                return -1;
            } elseif ($productionB->hasStartDate() && !$productionA->hasStartDate()) {
                return 1;
            } else {
                $startDateA = Date::toCarbon($productionA->date_start);
                $startDateA = $startDateA ? $startDateA->endOfDay() : null;
                $startDateB = Date::toCarbon($productionB->date_start);
                $startDateB = $startDateB ? $startDateB->endOfDay() : null;

                if (isset($startDateA, $startDateB)) {
                    if ($startDateA->lt($startDateB)) {
                        return 1;
                    } elseif ($startDateA->gt($startDateB)) {
                        return -1;
                    } else {
                        return 0;
                    }
                } else {
                    if ($startDateA === null && $startDateB !== null) {
                        return -1;
                    } elseif ($startDateA !== null && $startDateB === null) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            }
        });

        return $productions;
    }

    /**
     * @param string $type
     * @param array $productions
     * @return void
     * @throws Throwable
     */
    public function saveProductions(string $type, array $productions = []): void
    {
        $currentIds = $this->getProductionIds($type);

        $newIds = [];
        if (!empty($productions)) {
            $newIds = Arr::map($productions, static fn($production) => $production['production_id'] ?? null);
            $newIds = array_filter($newIds);
        }

        // insert / update the productions
        if (count($newIds) > 0) {
            foreach ($productions as $production) {
                $productionId = is_numeric($production['production_id']) ? (int) $production['production_id'] : null;
                $role = !empty($production['role']) ? sanitize_text_field($production['role']) : null;
                $order = is_numeric($production['custom_order']) ? (int) $production['custom_order'] : null;

                if (!$productionId) {
                    continue;
                }

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
        if (!empty($currentIds) && !empty($newIds)) {
            $toDeleteIds = array_values(array_diff($currentIds, $newIds));
        } elseif (!empty($currentIds) && empty($newIds)) {
            $toDeleteIds = $currentIds;
        } else {
            $toDeleteIds = [];
        }

        // Delete the to be deleted
        if (is_array($toDeleteIds) && count($toDeleteIds) > 0) {
            foreach ($toDeleteIds as $productionId) {
                $this->deleteProduction((int) $productionId, $type);
            }
        }
    }

    /**
     * @param int $id
     * @param string $type
     * @param string|null $role
     * @param int|null $customOrder
     * @return void
     * @global wpdb $wpdb
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
     * @global wpdb $wpdb
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
     * @global wpdb $wpdb
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
