<?php

namespace CurtainCall\PostTypes\Traits;

use Carbon\CarbonImmutable;
use WP_Query;

trait QueriesWordPressForProductions
{
    protected static array $wp_query_args = [
        'post_type' => [
            'ccwp_production',
            'post',
        ],
        'post_status' => 'publish',
        'meta_key' => '_ccwp_production_date_start',
        'orderby' => 'meta_value',
        'nopaging' => true,
    ];

    public static function getPastPosts(): WP_Query
    {
        return new WP_Query(static::getPastQueryArgs());
    }

    public static function getCurrentPosts(): WP_Query
    {
        return new WP_Query(static::getCurrentQueryArgs());
    }

    public static function getFuturePosts(): WP_Query
    {
        return new WP_Query(static::getFutureQueryArgs());
    }

    protected static function getPastQueryArgs(): array
    {
        $today = CarbonImmutable::now()->toDateString();
        return array_merge(static::$wp_query_args, [
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => $today,
                    'compare' => '<',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => $today,
                    'compare' => '<',
                ]
            ]
        ]);
    }

    protected static function getCurrentQueryArgs(): array
    {
        $today = CarbonImmutable::now()->toDateString();
        return array_merge(static::$wp_query_args, [
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => $today,
                    'compare' => '<=',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => $today,
                    'compare' => '>=',
                ]
            ]
        ]);
    }

    protected static function getFutureQueryArgs(): array
    {
        $today = CarbonImmutable::now()->toDateString();
        return array_merge(static::$wp_query_args, [
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => $today,
                    'compare' => '>',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => $today,
                    'compare' => '>',
                ]
            ]
        ]);
    }
}
