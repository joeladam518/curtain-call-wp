<?php

declare(strict_types=1);

namespace CurtainCall\Support;

use CurtainCall\Models\CurtainCallPivot;
use Illuminate\Support\Arr;

class Query
{
    protected static ?string $selectProductionCache = null;
    protected static ?string $selectCastAndCrewCache = null;
    /** @var list<string>|null */
    protected static ?array $selectPivotCache = null;

    /**
     * Stitch together a bunch of query parts
     *
     * @param string[]|string|null $query
     * @return string
     */
    public static function raw(array|string|null $query): string
    {
        if (is_null($query)) {
            return '';
        }

        /** @var string[] $query */
        $query = Arr::wrap($query);

        return collect($query)
            ->map(static fn($q) => trim($q))
            ->filter()
            ->implode(' ');
    }

    /**
     * @param list<string> $fields
     * @param bool $prependSelect
     * @return string
     */
    public static function select(array $fields = [], bool $prependSelect = true): string
    {
        /** @var string[] $fields */
        $fields = Arr::map($fields, static function (string $field) {
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
     * @return list<string>
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
        $alias = '`' . CurtainCallPivot::TABLE_ALIAS . '`';

        return match ($type) {
            'cast' => " {$clause} {$alias}.`type` = 'cast' ",
            'crew' => " {$clause} {$alias}.`type` = 'crew' ",
            default => " {$clause} ({$alias}.`type` = 'cast' OR {$alias}.`type` = 'crew') ",
        };
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

        /** @var string[] $fieldParts */
        $fieldParts = Arr::map(explode('.', $field), static function ($part) {
            if ($part === '*') {
                return $part;
            }

            return "`{$part}`";
        });

        $field = implode('.', $fieldParts);

        return $alias ? "{$field} AS {$alias}" : $field;
    }
}
