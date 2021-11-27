<?php

namespace CurtainCall\PostTypes;

use CurtainCall\PostTypes\Interfaces\Arrayable;
use CurtainCall\PostTypes\Traits\HasAttributes;
use CurtainCall\Support\Arr;
use wpdb;

/**
 * @property int    $production_id
 * @property int    $cast_and_crew_id
 * @property string $type
 * @property string $role
 * @property int    $custom_order
 */
class CurtainCallPivot implements Arrayable
{
    use HasAttributes;

    const TABLE_NAME  = 'ccwp_castandcrew_production';
    const TABLE_ALIAS = 'ccwp_join';
    const ATTRIBUTE_PREFIX =  self::TABLE_ALIAS . '_';

    protected static ?string $table;
    protected static ?string $tableWithAlias;

    /** @var array|string[] */
    protected static $fields = [
        'production_id',
        'cast_and_crew_id',
        'type',
        'role',
        'custom_order',
    ];

    public function __construct(array $data = [])
    {
        $this->load($data);
    }

    /**
     * @param bool $withPrefix
     * @return array|string[]
     */
    public static function getFields(bool $withPrefix = false): array
    {
        if (!$withPrefix) {
            return static::$fields;
        }

        return Arr::map(static::$fields, fn($field) => static::ATTRIBUTE_PREFIX . $field);
    }

    /**
     * @global wpdb $wpdb
     * @return string
     */
    public static function getTableName(): string
    {
        global $wpdb;

        return static::$table ??= $wpdb->prefix.static::TABLE_NAME;
    }

    /**
     * @return string
     */
    public static function getTableNameWithAlias(): string
    {
        return static::$tableWithAlias ??= '`'.static::getTableName().'` AS `'.static::TABLE_ALIAS.'`';
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isField(string $value): bool
    {
        $field = static::stripPrefix($value);
        return in_array($field, static::$fields);
    }

    /**
     * @param string $field
     * @return string
     */
    public static function stripPrefix(string $field): string
    {
        return preg_replace('~^'.static::ATTRIBUTE_PREFIX.'~', '', $field);
    }

    /**
     * @param array $data
     */
    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            if (static::isField($key)) {
                $key = static::stripPrefix($key);
                $this->setAttribute($key, $value);
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
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
