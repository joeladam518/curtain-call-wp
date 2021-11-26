<?php

namespace CurtainCall\Support;

use CurtainCall\PostTypes\CurtainCallPivot;

class Query
{
    protected static ?string $selectProductionCache = null;
    protected static ?string $selectCastAndCrewCache = null;
    protected static ?array $selectPivotCache = null;

    /**
     * @return string
     */
    public static function selectProductions(): string
    {
        if (empty(static::$selectProductionCache)) {
            $select = [
                '`production_posts`.*'
            ];

            $select = array_merge($select, static::selectPivotFields());

            static::$selectProductionCache  = implode(', ', $select);
        }

        return static::$selectProductionCache;
    }

    /**
     * @return string
     */
    public static function selectCastAndCrew(): string
    {
        if (empty(static::$selectCastAndCrewCache)) {
            $select = [
                '`castcrew_posts`.*'
            ];

            $select = array_merge($select, static::selectPivotFields());

            static::$selectCastAndCrewCache = implode(', ', $select);
        }

        return static::$selectCastAndCrewCache;
    }

    /**
     * @return array
     */
    protected static function selectPivotFields(): array
    {
        if (empty(static::$selectPivotCache)) {
            $pivotAlias = CurtainCallPivot::TABLE_ALIAS;
            $prefix = CurtainCallPivot::ATTRIBUTE_PREFIX;

            $select = [];
            foreach (CurtainCallPivot::getFields() as $field) {
                $select[] = "`{$pivotAlias}`.`{$field}` AS `{$prefix}{$field}`";
            }

            static::$selectPivotCache = $select;
        }

        return static::$selectPivotCache;
    }

    /**
     * @param string $type
     * @param string $clause
     * @return string
     */
    public static function wherePivotType(string $type = 'both', string $clause = 'WHERE'): string
    {
        $query = "    ";

        switch($type) {
            case 'cast':
                $query .= $clause . " `" . CurtainCallPivot::TABLE_ALIAS . "`.`type` = 'cast'";
                break;
            case 'crew':
                $query .= $clause . " `" . CurtainCallPivot::TABLE_ALIAS . "`.`type` = 'crew'";
                break;
            case 'both':
            default:
                $query .= $clause . " (`" . CurtainCallPivot::TABLE_ALIAS . "`.`type` = 'cast' OR `" . CurtainCallPivot::TABLE_ALIAS . "`.`type` = 'crew')";
                break;
        };

        $query .= PHP_EOL;

        return $query;
    }
}
