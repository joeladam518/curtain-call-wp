<?php

namespace CurtainCallWP\PostTypes;

abstract class CurtainCallPostType
{
    protected static $meta = [];
    protected static $ccwp_join_table_name = 'ccwp_castandcrew_production';
    
    public static function getJoinTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . static::$ccwp_join_table_name;
    }
    
    abstract public static function getConfig(): array;
}