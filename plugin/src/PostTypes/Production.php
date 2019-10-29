<?php

namespace CurtainCallWP\PostTypes;

use CurtainCallWP\PostTypes\Traits\ProductionHelpers;

/**
 * Class Production
 * @package CurtainCallWP\PostTypes
 */
class Production extends CurtainCallPostType
{
    use ProductionHelpers;
    
    public static function getConfig(): array
    {
        return [
            'description'   => 'Displays your theatre company\'s productions and their relevant data',
            'labels'        => [
                'name'               => __('Productions'),
                'singular_name'      => __('Production'),
                //'add_new'            => __('Add New'),
                //'add_new_item'       => __('Add New Production'),
                //'edit_item'          => __('Edit Production'),
                //'new_item'           => __('New Production'),
                //'all_items'          => __('All Productions'),
                //'view_item'          => __('View Production'),
                //'search_items'       => __('Search productions'),
                //'not_found'          => __('No productions found'),
                //'not_found_in_trash' => __('No productions found in the Trash'),
                //'parent_item_colon'  => '',
                //'menu_name'          => 'Productions',
            ],
            'public'        => true,
            //'menu_position' => 5,
            //'supports'      => [
            //    'title',
            //    'editor',
            //    'thumbnail',
            //],
            //'taxonomies'    => [
            //    'ccwp_production_seasons'
            //],
            'has_archive' => true,
            'rewrite'       => [
                'slug' => 'shows',
                'with_front' => true
            ],
        ];
    }
    
    public static function getSeasonsTaxonomyConfig(): array
    {
        // Add new taxonomy, make it hierarchical (like categories)
        return [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => _x('Seasons', 'taxonomy general name', 'curtain-call-wp'),
                'singular_name'     => _x('Season', 'taxonomy singular name', 'curtain-call-wp'),
                'search_items'      => __('Search Seasons', 'curtain-call-wp'),
                'all_items'         => __('All Seasons', 'curtain-call-wp'),
                'parent_item'       => __('Parent Season', 'curtain-call-wp'),
                'parent_item_colon' => __('Parent Season:', 'curtain-call-wp'),
                'edit_item'         => __('Edit Season', 'curtain-call-wp'),
                'update_item'       => __('Update Season', 'curtain-call-wp'),
                'add_new_item'      => __('Add New Season', 'curtain-call-wp'),
                'new_item_name'     => __('New Season Title', 'curtain-call-wp'),
                'menu_name'         => __('Seasons', 'curtain-call-wp'),
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
