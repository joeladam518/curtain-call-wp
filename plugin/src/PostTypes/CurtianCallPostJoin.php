<?php

namespace CurtainCallWP\PostTypes;

use CurtainCallWP\PostTypes\Interfaces\Arrayable;
use CurtainCallWP\PostTypes\Traits\HasAttributes;

/**
 * Class CurtainCallPostJoin
 * @package CurtainCallWP\PostTypes
 * @property int $production_id
 * @property int $cast_and_crew_id
 * @property string $type
 * @property string $role
 * @property int custom_order
 */
class CurtainCallPostJoin implements Arrayable
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
    
    public static function stripJoinPrefix($key): string
    {
        return preg_replace('~^'. static::ATTRIBUTE_PREFIX .'~', '', $key);
    }
    
    public static function isJoinField($key): bool
    {
        $join_field = static::stripJoinPrefix($key);
        return in_array($join_field, static::$fields);
    }
    
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
    
    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            if (static::isJoinField($key)) {
                $key = static::stripJoinPrefix($key);
                $this->setAttribute($key, $value);
            }
        }
    }
    
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
    
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
    
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
    
    public function toArray(): array
    {
        return $this->attributes;
    }
}