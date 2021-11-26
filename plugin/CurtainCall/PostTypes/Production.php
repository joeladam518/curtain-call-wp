<?php

namespace CurtainCall\PostTypes;

use Carbon\CarbonImmutable as Carbon;
use CurtainCall\PostTypes\Traits\HasCastAndCrew;
use CurtainCall\PostTypes\Traits\QueriesWordPressForProductions;
use CurtainCall\Support\Date;
use Throwable;

/**
 * Class Production
 * @package CurtainCall\PostTypes
 * @property string $name
 * @property string $date_start
 * @property string $date_end
 * @property string $show_times
 * @property string $ticket_url
 * @property string $venue
 * @property string $press
 * @property string $chronological_state
 */
class Production extends CurtainCallPost
{
    use HasCastAndCrew;

    const POST_TYPE = 'ccwp_production';
    const META_PREFIX = '_ccwp_production_';

    /**
     * @var array
     */
    protected $ccwp_meta_keys = [
        'name',
        'date_start',
        'date_end',
        'show_times',
        'ticket_url',
        'venue',
        'press',
    ];

    /** @var array */
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
    public static function getConfig(): array
    {
        return [
            'description'   => 'Displays your theatre company\'s productions and their relevant data',
            'labels'        => [
                'name'               => __('Productions', CCWP_TEXT_DOMAIN),
                'singular_name'      => __('Production', CCWP_TEXT_DOMAIN),
                'add_new'            => __('Add New', CCWP_TEXT_DOMAIN),
                'add_new_item'       => __('Add New Production', CCWP_TEXT_DOMAIN),
                'edit_item'          => __('Edit Production', CCWP_TEXT_DOMAIN),
                'new_item'           => __('New Production', CCWP_TEXT_DOMAIN),
                'all_items'          => __('All Productions', CCWP_TEXT_DOMAIN),
                'view_item'          => __('View Production', CCWP_TEXT_DOMAIN),
                'search_items'       => __('Search productions', CCWP_TEXT_DOMAIN),
                'not_found'          => __('No productions found', CCWP_TEXT_DOMAIN),
                'not_found_in_trash' => __('No productions found in the Trash', CCWP_TEXT_DOMAIN),
                'parent_item_colon'  => '',
                'menu_name'          => 'Productions',
            ],
            'public'        => true,
            'menu_position' => 5,
            'show_in_nav_menus' => true,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_production_seasons'
            ],
            'has_archive' => true,
            'rewrite' => [
                'slug' => 'productions',
                'with_front' => true,
                'feeds' => false,
            ],
        ];
    }

    public static function getSeasonsTaxonomyConfig(): array
    {
        // Add new taxonomy, make it hierarchical (like categories)
        return [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => __('Seasons', CCWP_TEXT_DOMAIN),
                'singular_name'     => __('Season', CCWP_TEXT_DOMAIN),
                'search_items'      => __('Search Seasons', CCWP_TEXT_DOMAIN),
                'all_items'         => __('All Seasons', CCWP_TEXT_DOMAIN),
                'parent_item'       => __('Parent Season', CCWP_TEXT_DOMAIN),
                'parent_item_colon' => __('Parent Season:', CCWP_TEXT_DOMAIN),
                'edit_item'         => __('Edit Season', CCWP_TEXT_DOMAIN),
                'update_item'       => __('Update Season', CCWP_TEXT_DOMAIN),
                'add_new_item'      => __('Add New Season', CCWP_TEXT_DOMAIN),
                'new_item_name'     => __('New Season Title', CCWP_TEXT_DOMAIN),
                'menu_name'         => __('Seasons', CCWP_TEXT_DOMAIN),
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug' => 'seasons',
                'with_front' => true,
            ],
        ];
    }

    /**
     * @return WP_Query
     */
    public static function getPastPosts(): WP_Query
    {
        return new WP_Query(array_merge(static::$wp_query_args, [
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => Date::today(),
                    'compare' => '<',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => Date::today(),
                    'compare' => '<',
                ]
            ]
        ]));
    }

    /**
     * @return WP_Query
     */
    public static function getCurrentPosts(): WP_Query
    {
        return new WP_Query(array_merge(static::$wp_query_args, [
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => Date::today(),
                    'compare' => '<=',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => Date::today(),
                    'compare' => '>=',
                ]
            ]
        ]));
    }

    /**
     * @return WP_Query
     */
    public static function getFuturePosts(): WP_Query
    {
        return new WP_Query(array_merge(static::$wp_query_args, [
            'order' => 'ASC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ccwp_production_date_start',
                    'value' => Date::today(),
                    'compare' => '>',
                ],
                [
                    'key' => '_ccwp_production_date_end',
                    'value' => Date::today(),
                    'compare' => '>',
                ]
            ]
        ]));
    }

    /**
     * @return string
     */
    public function getChronologicalState(): string
    {
        if (isset($this->chronological_state)) {
            return $this->chronological_state;
        }

        $now = Carbon::now();
        $start_date = Date::toCarbon($this->date_start);
        $end_date = Date::toCarbon($this->date_end);

        if ($now->gt($end_date)) {
            $this->chronological_state = 'past';
        } else if ($now->lt($start_date)) {
            $this->chronological_state = 'future';
        } else {
            $this->chronological_state = 'current';
        }

        return $this->chronological_state;
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function getFormattedShowDates(): string
    {
        $chrono_state = $this->getChronologicalState();
        $start_date = Date::toCarbon($this->date_start);
        $end_date = Date::toCarbon($this->date_end);
        $now = Carbon::now();

        $start_date_format = 'F jS';
        $end_date_format   = '';

        if ($chrono_state == 'past' || $start_date->format('Y') != $now->format('Y')) {
            // Don't show start date year if both dates are in the same year
            if ($start_date->format('Y') != $end_date->format('Y')) {
                $start_date_format .= ', Y';
            }
        }

        // Don't show end date month if both dates are in the same month
        if ($start_date->format('F') != $end_date->format('F')) {
            $end_date_format .= 'F ';
        }

        $end_date_format .= 'jS';

        // End date only gets a year if it's in the past or doesn't match the current year
        if ($chrono_state == 'past' || $end_date->format('Y') != $now->format('Y')) {
            $end_date_format .= ', Y';
        }

        $formatted_dates = $start_date->format($start_date_format);
        $formatted_end_date = $end_date->format($end_date_format);

        // Only show one date if the dates are identical
        if ($formatted_dates != $formatted_end_date) {
            $formatted_dates .= ' - ' . $formatted_end_date;
        }

        return $formatted_dates;
    }

    public function getTicketUrl(): string
    {
        if ($this->getChronologicalState() == 'past') {
            return '';
        }

        if (isset($this->ticket_url)) {
            return $this->ticket_url;
        }

        // TODO: 2019-12-1: change ticket link to a plugin option
        return 'https://www.rutheckerdhall.com/events';
    }

    public function hasStartDate(): bool
    {
        return !empty($this->date_start);
    }
}
