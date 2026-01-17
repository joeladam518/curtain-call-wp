<?php

declare(strict_types=1);

namespace CurtainCall\Models;

use CurtainCall\Exceptions\WordpressDbInstanceNotFoundException;
use CurtainCall\Models\Traits\HasAttributes;
use Illuminate\Contracts\Support\Arrayable;
use wpdb;

/**
 * @property int $production_id
 * @property int $cast_and_crew_id
 * @property string $type
 * @property string $role
 * @property string $custom_order -> numeric
 * @implements Arrayable<string, mixed>
 */
class CurtainCallPivot implements Arrayable
{
    use HasAttributes;

    /** @var string */
    public const TABLE_NAME = 'ccwp_castandcrew_production';
    /** @var string */
    public const TABLE_ALIAS = 'ccwp_join';
    /** @var string */
    public const ATTRIBUTE_PREFIX = self::TABLE_ALIAS . '_';

    protected static ?string $table;
    protected static ?string $tableWithAlias;

    /** @var list<string> */
    protected static array $fields = [
        'production_id',
        'cast_and_crew_id',
        'type',
        'role',
        'custom_order',
    ];

    /**
     * @param array<string, mixed> $data
     */
    final public function __construct(array $data = [])
    {
        $this->load($data);
    }

    /**
     * @param bool $withPrefix
     * @return array<int, string>
     */
    public static function getFields(bool $withPrefix = false): array
    {
        if (!$withPrefix) {
            return static::$fields;
        }

        return collect(static::$fields)
            ->map(static fn(string $field) => static::ATTRIBUTE_PREFIX . $field)
            ->all();
    }

    /**
     * @global wpdb $wpdb
     * @return string
     * @throws WordpressDbInstanceNotFoundException
     */
    public static function getTableName(): string
    {
        $wpdb = ccwp_get_wpdb();

        return static::$table ??= $wpdb->prefix . static::TABLE_NAME;
    }

    /**
     * @return string
     * @throws WordpressDbInstanceNotFoundException
     */
    public static function getTableNameWithAlias(): string
    {
        return static::$tableWithAlias ??= '`' . static::getTableName() . '` AS `' . static::TABLE_ALIAS . '`';
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isField(string $value): bool
    {
        return in_array($value, static::$fields, true);
    }

    /**
     * @param string $field
     * @return string
     */
    public static function stripPrefix(string $field): string
    {
        return preg_replace('~^' . static::ATTRIBUTE_PREFIX . '~', '', $field);
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function load(array $data): void
    {
        foreach ($data as $key => $value) {
            $field = static::stripPrefix($key);
            if (static::isField($field)) {
                $this->setAttribute($field, $value);
            }
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
