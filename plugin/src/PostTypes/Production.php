<?php

namespace CurtainCallWP\PostTypes;

use CurtainCallWP\PostTypes\Traits\HasCastAndCrew;

/**
 * Class Production
 * @package CurtainCallWP\PostTypes
 * @property string $name
 * @property string $date_start
 * @property string $date_end
 * @property string $show_times
 * @property string $venue
 * @property string $press
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
}
