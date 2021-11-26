<?php

namespace CurtainCall\Support;

use CurtainCall\PostTypes\CurtainCallPivot;

class Query
{
    protected static $selectProductionCache;
    protected static $selectCastAndCrewCache;
    protected static $joinFieldCache;

    /**
     * @return string
     */
    public static function selectProductions(): string
    {
        if (empty(static::$selectProductionCache)) {
            $query_array = [
                '`production_posts`.*'
            ];

            if (empty(static::$joinFieldCache)) {
                static::$joinFieldCache = static::selectJoinFields();
            }

            $query_array = array_merge($query_array, static::$joinFieldCache);

            static::$selectProductionCache  = implode(', ', $query_array);
        }

        return static::$selectProductionCache;
    }

    /**
     * @return string
     */
    public static function selectCastAndCrew(): string
    {
        if (empty(static::$selectCastAndCrewCache)) {
            $query_array = [
                '`castcrew_posts`.*'
            ];

            if (empty(static::$joinFieldCache)) {
                static::$joinFieldCache = static::selectJoinFields();
            }

            $query_array = array_merge($query_array, static::$joinFieldCache);

            static::$selectCastAndCrewCache  = implode(', ', $query_array);
        }

        return static::$selectCastAndCrewCache;
    }

    /**
     * @return array
     */
    protected static function selectJoinFields(): array
    {
        $join_alias = CurtainCallPivot::TABLE_ALIAS;
        $prefix = CurtainCallPivot::ATTRIBUTE_PREFIX;

        $query_array = [];
        foreach (CurtainCallPivot::getFields() as $field) {
            $query_array[] = "`{$join_alias}`.`{$field}` AS `{$prefix}{$field}`";
        }

        return $query_array;
    }

    /**
     * @param string $type
     * @param string $clause
     * @return string
     */
    public static function whereCCWPJoinType(string $type = 'both', string $clause = 'WHERE'): string
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
