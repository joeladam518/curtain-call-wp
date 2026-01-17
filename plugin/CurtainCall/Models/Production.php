<?php

declare(strict_types=1);

namespace CurtainCall\Models;

use Carbon\CarbonImmutable as Carbon;
use CurtainCall\Models\Traits\HasCastAndCrew;
use CurtainCall\Support\Date;
use Throwable;
use WP_Query;

/**
 * @property string $name
 * @property string $date_start
 * @property string $date_end
 * @property string $show_times
 * @property string $ticket_url
 * @property string $venue
 * @property string $chronological_state
 */
class Production extends CurtainCallPost
{
    use HasCastAndCrew;

    /** @var string */
    public const POST_TYPE = 'ccwp_production';
    /** @var string */
    public const META_PREFIX = '_ccwp_production_';
    /** @var string */
    public const SEASONS_TAXONOMY = 'ccwp_production_seasons';

    /**
     * @var list<string>
     */
    protected array $ccwp_meta = [
        'name',
        'date_start',
        'date_end',
        'show_times',
        'ticket_url',
        'venue',
    ];

    /**
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'description' => __(
                'Displays your theatre company\'s productions and their relevant data',
                CCWP_TEXT_DOMAIN,
            ),
            'labels' => [
                'name' => __('Productions', CCWP_TEXT_DOMAIN),
                'singular_name' => __('Production', CCWP_TEXT_DOMAIN),
                'add_new' => __('Add New', CCWP_TEXT_DOMAIN),
                'add_new_item' => __('Add New Production', CCWP_TEXT_DOMAIN),
                'edit_item' => __('Edit Production', CCWP_TEXT_DOMAIN),
                'new_item' => __('New Production', CCWP_TEXT_DOMAIN),
                'all_items' => __('All Productions', CCWP_TEXT_DOMAIN),
                'view_item' => __('View Production', CCWP_TEXT_DOMAIN),
                'search_items' => __('Search productions', CCWP_TEXT_DOMAIN),
                'not_found' => __('No productions found', CCWP_TEXT_DOMAIN),
                'not_found_in_trash' => __('No productions found in the Trash', CCWP_TEXT_DOMAIN),
                'parent_item_colon' => '',
                'menu_name' => __('Productions', CCWP_TEXT_DOMAIN),
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_position' => 5,
            'show_in_nav_menus' => true,
            'has_archive' => true,
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'custom-fields',
            ],
            'taxonomies' => [
                'ccwp_production_seasons',
            ],
            'rewrite' => [
                'slug' => 'productions',
                'with_front' => true,
                'feeds' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getSeasonsTaxonomyConfig(): array
    {
        // Add new taxonomy, make it hierarchical (like categories)
        return [
            'hierarchical' => true,
            'show_in_rest' => true,
            'labels' => [
                'name' => __('Seasons', CCWP_TEXT_DOMAIN),
                'singular_name' => __('Season', CCWP_TEXT_DOMAIN),
                'search_items' => __('Search Seasons', CCWP_TEXT_DOMAIN),
                'all_items' => __('All Seasons', CCWP_TEXT_DOMAIN),
                'parent_item' => __('Parent Season', CCWP_TEXT_DOMAIN),
                'parent_item_colon' => __('Parent Season:', CCWP_TEXT_DOMAIN),
                'edit_item' => __('Edit Season', CCWP_TEXT_DOMAIN),
                'update_item' => __('Update Season', CCWP_TEXT_DOMAIN),
                'add_new_item' => __('Add New Season', CCWP_TEXT_DOMAIN),
                'new_item_name' => __('New Season Title', CCWP_TEXT_DOMAIN),
                'menu_name' => __('Seasons', CCWP_TEXT_DOMAIN),
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'seasons',
                'with_front' => true,
            ],
        ];
    }

    /**
     * Query for Production Posts
     *
     * @param array $additionalArgs
     * @return WP_Query
     */
    public static function getPosts(array $additionalArgs = []): WP_Query
    {
        return new WP_Query(array_merge([
            'post_type' => [
                'ccwp_production',
                'post',
            ],
            'post_status' => 'publish',
            'meta_key' => '_ccwp_production_date_start',
            'orderby' => 'meta_value',
            'nopaging' => true,
        ], $additionalArgs));
    }

    /**
     * Query for Past Production Posts
     *
     * @return WP_Query
     */
    public static function getPastPosts(): WP_Query
    {
        return static::getPosts([
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
                ],
            ],
        ]);
    }

    /**
     * Query for Present Production Posts
     *
     * @return WP_Query
     */
    public static function getCurrentPosts(): WP_Query
    {
        return static::getPosts([
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
                ],
            ],
        ]);
    }

    /**
     * Query for Future Production Posts
     *
     * @return WP_Query
     */
    public static function getFuturePosts(): WP_Query
    {
        return static::getPosts([
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
                ],
            ],
        ]);
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
        $startDate = Date::toCarbon($this->date_start);
        $endDate = Date::toCarbon($this->date_end);

        if ($now->gt($endDate)) {
            $this->chronological_state = 'past';
        } elseif ($now->lt($startDate)) {
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
        $now = Carbon::now();
        $chronologicalState = $this->getChronologicalState();
        $startDate = Date::toCarbon($this->date_start);
        $endDate = Date::toCarbon($this->date_end);

        $startDateFormat = 'F jS';
        $endDateFormat = '';

        if ($chronologicalState === 'past' || $startDate?->format('Y') !== $now->format('Y')) {
            // Don't show the start date year if both dates are in the same year
            if ($startDate?->format('Y') !== $endDate?->format('Y')) {
                $startDateFormat .= ', Y';
            }
        }

        // Don't show the end date month if both dates are in the same month
        if ($startDate?->format('F') !== $endDate?->format('F')) {
            $endDateFormat .= 'F ';
        }

        $endDateFormat .= 'jS';

        // End date only gets a year if it's in the past or doesn't match the current year
        if ($chronologicalState === 'past' || $endDate?->format('Y') !== $now?->format('Y')) {
            $endDateFormat .= ', Y';
        }

        /** @var string $formattedDates */
        $formattedDates = $startDate?->format($startDateFormat);
        /** @var string $formattedEndDate */
        $formattedEndDate = $endDate?->format($endDateFormat);

        // Only show one date if the dates are identical
        if ($formattedDates && $formattedEndDate && $formattedDates !== $formattedEndDate) {
            $formattedDates .= ' - ' . $formattedEndDate;
        }

        return $formattedDates;
    }

    /**
     * @return string|null
     */
    public function getTicketUrl(): ?string
    {
        if ($this->getChronologicalState() === 'past') {
            return null;
        }

        if ($this->ticket_url) {
            return $this->ticket_url;
        }

        // Get the default ticket url specified in the settings page
        /** @var string|false|null $url */
        $url = get_option('ccwp_default_ticket_url', null);

        return $url ?: null;
    }

    /**
     * @return bool
     */
    public function hasStartDate(): bool
    {
        return (bool) $this->date_start;
    }
}
