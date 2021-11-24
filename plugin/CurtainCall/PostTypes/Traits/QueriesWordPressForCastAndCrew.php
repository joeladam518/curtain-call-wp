<?php

namespace CurtainCall\PostTypes\Traits;

use WP_Query;

trait QueriesWordPressForCastAndCrew
{
    protected static $wp_query_args = [
        'post_type' => [
            'ccwp_cast_and_crew',
            'post',
        ],
        'post_status' => 'publish',
        'meta_key' => '_ccwp_cast_crew_name_last',
        'orderby' => 'meta_value',
        'order'   => 'ASC',
        'nopaging' => true,
    ];

    public static function getPosts(): WP_Query
    {
        return new WP_Query(static::$wp_query_args);
    }
}
