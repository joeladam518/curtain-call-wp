<?php

namespace CurtainCall\PostTypes;

use CurtainCall\PostTypes\Interfaces\Arrayable;
use CurtainCall\PostTypes\Traits\HasAttributes;

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

    protected static $fields = [
        'production_id',
        'cast_and_crew_id',
        'type',
        'role',
        'custom_order',
    ];

    public function __construct(array $data)
    {
        $this->load($data);
    }

    /**
     * @param string $key
     * @return string
     */
    public static function stripJoinPrefix($key): string
    {
        return preg_replace('~^'. static::ATTRIBUTE_PREFIX .'~', '', $key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function isJoinField($key): bool
    {
        $join_field = static::stripJoinPrefix($key);
        return in_array($join_field, static::$fields);
    }

    /**
     * @param bool $with_prefix
     * @return array|string[]
     */
    public static function getJoinFields($with_prefix = false): array
    {
        if (!$with_prefix) {
            return static::$fields;
        }

        $join_fields = [];
        foreach (static::$fields as $key) {
            $join_fields[] = static::ATTRIBUTE_PREFIX . $key;
        }

        return $join_fields;
    }

    /**
     * @param array $data
     */
    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            if (static::isJoinField($key)) {
                $key = static::stripJoinPrefix($key);
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
     * @param $key
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
