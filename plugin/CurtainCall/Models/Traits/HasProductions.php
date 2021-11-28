<?php

namespace CurtainCall\Models\Traits;

use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\Query;
use wpdb;

trait HasProductions
{
    /**
     * @return array|Production[]
     * @global wpdb $wpdb
     */
    public function getProductions(): array
    {
        global $wpdb;

        $query = Query::raw([
            "SELECT " . Query::selectProductions(),
            "FROM `{$wpdb->posts}` AS `castcrew_posts`",
            "INNER JOIN ". CurtainCallPivot::getTableNameWithAlias() ." ON `castcrew_posts`.`ID` = `ccwp_join`.`cast_and_crew_id`",
            "INNER JOIN `{$wpdb->posts}` AS `production_posts` ON `production_posts`.`ID` = `ccwp_join`.`production_id`",
            "WHERE `castcrew_posts`.`ID` = %d",
            "ORDER BY `production_posts`.`post_title`;",
        ]);

        $sql = $wpdb->prepare($query, $this->ID);
        $productions = $wpdb->get_results($sql, ARRAY_A);

        if (count($productions) === 0) {
            return [];
        }

        /** @var Production[] $productions */
        $productions = static::toCurtainCallPosts($productions);

        usort($productions , function(Production $productionA, Production $productionB) {
            if ($productionA->hasStartDate() && !$productionB->hasStartDate()) {
                return -1;
            } else if ($productionB->hasStartDate() && !$productionA->hasStartDate()) {
                return 1;
            } else {
                $startDateA = Date::toCarbon($productionA->date_start);
                $startDateA = $startDateA ? $startDateA->endOfDay() : null;
                $startDateB = Date::toCarbon($productionB->date_start);
                $startDateB = $startDateB ? $startDateB->endOfDay() : null;

                if (isset($startDateA) && isset($startDateB)) {
                    if ($startDateA->lt($startDateB)) {
                        return 1;
                    } else if ($startDateA->gt($startDateB)) {
                        return -1;
                    } else {
                        return 0;
                    }
                } else {
                    if ($startDateA === null && $startDateB !== null) {
                        return -1;
                    } else if ($startDateA !== null && $startDateB === null) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            }
        });

        return $productions;
    }
}
