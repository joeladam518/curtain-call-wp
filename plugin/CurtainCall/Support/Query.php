<?php

declare(strict_types=1);

namespace CurtainCall\Support;

use CurtainCall\Models\CurtainCallPivot;
use Illuminate\Support\Arr;

class Query
{
    protected static ?string $selectProductionCache = null;
    protected static ?string $selectCastAndCrewCache = null;
    protected static ?array $selectPivotCache = null;

    /**
     * Stitch together a bunch of query parts
     *
     * @param array|string[]|string|null $query
     * @return string
     */
    public static function raw($query): string
    {
        $query = Arr::wrap($query);

        return implode(' ', Arr::map($query, fn($part) => trim($part)));
    }

    /**
     * @param array $fields
     * @param bool $prependSelect
     * @return string
     */
    public static function select(array $fields = [], bool $prependSelect = true): string
    {
        $fields = Arr::map($fields, function ($field) {
            $field = str_replace('`', '', $field);
            return static::backtickField($field);
        });

        $query = implode(', ', $fields);

        return $prependSelect ? "SELECT {$query}" : $query;
    }

    /**
     * @return string
     */
    public static function selectProductions(): string
    {
        if (empty(static::$selectProductionCache)) {
            $select = array_merge(['`production_posts`.*'], static::selectPivotFields());
            static::$selectProductionCache = implode(', ', $select);
        }

        return static::$selectProductionCache;
    }

    /**
     * @return string
     */
    public static function selectCastAndCrew(): string
    {
        if (empty(static::$selectCastAndCrewCache)) {
            $select = array_merge(['`castcrew_posts`.*'], static::selectPivotFields());
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
        $alias = '`'.CurtainCallPivot::TABLE_ALIAS.'`';

        switch ($type) {
            case 'cast':
                return " {$clause} {$alias}.`type` = 'cast' ";
            case 'crew':
                return " {$clause} {$alias}.`type` = 'crew' ";
            case 'both':
            default:
                return " {$clause} ({$alias}.`type` = 'cast' OR {$alias}.`type` = 'crew') ";
        }
    }

    /**
     * @param string $value
     * @return string
     */
    protected static function backtickField(string $value): string
    {
        $parts = preg_split('~\s+AS\s+~i', $value);
        $field = trim($parts[0]);
        $alias = trim($parts[1] ?? '');

        $fieldParts = Arr::map(explode('.', $field), function ($part) {
            if ($part === '*') {
                return $part;
            }

            return "`{$part}`";
        });

        $field = implode('.', $fieldParts);

        return $alias ? "{$field} AS {$alias}" : $field;
    }
}
