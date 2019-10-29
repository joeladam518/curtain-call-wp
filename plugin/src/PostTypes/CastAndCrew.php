<?php

namespace CurtainCallWP\PostTypes;

use CurtainCallWP\PostTypes\Traits\CastAndCrewHelpers;

/**
 * Class CastAndCrew
 * @package CurtainCallWP\PostTypes
 */
class CastAndCrew extends CurtainCallPostType
{
    use CastAndCrewHelpers;
    
    public static function getConfig(): array
    {
        return [
            'description'   => 'The Cast and Crew for you productions',
            'labels'        => [
                'name'               => _x('Cast and Crew', 'post type general name'),
                'singular_name'      => _x('Cast or Crew', 'post type singular name'),
                'add_new'            => _x('Add New', 'Cast or Crew'),
                'add_new_item'       => __('Add New cast or crew'),
                'edit_item'          => __('Edit cast or crew'),
                'new_item'           => __('New cast or crew'),
                'all_items'          => __('All cast and crew'),
                'view_item'          => __('View cast and crew'),
                'search_items'       => __('Search cast and crew'),
                'not_found'          => __('No cast or crew found'),
                'not_found_in_trash' => __('No cast or crew found in the Trash'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Cast and Crew',
            ],
            'public'        => true,
            'menu_position' => 6,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            //'taxonomies'    => [
            //    'ccwp_cast_crew_productions',
            //],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'cast-and-crew',
                'with_front' => true,
            ],
        ];
    }
    
    public static function getProductionsTaxonomyConfig(): array
    {
        return [
            'hierarchical'          => false,
            'labels'                => [
                'name'                       => _x('C&C Productions', 'taxonomy general name', 'curtain-call-wp'),
                'singular_name'              => _x('C&C Production', 'taxonomy singular name', 'curtain-call-wp'),
                'search_items'               => __('Search Productions', 'curtain-call-wp'),
                'popular_items'              => __('Popular Productions', 'curtain-call-wp'),
                'all_items'                  => __('All Productions', 'curtain-call-wp'),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __('Edit Production', 'curtain-call-wp'),
                'update_item'                => __('Update Production', 'curtain-call-wp'),
                'add_new_item'               => __('Add New Production', 'curtain-call-wp'),
                'new_item_name'              => __('New Production Name', 'curtain-call-wp'),
                'separate_items_with_commas' => __('Separate productions with commas', 'curtain-call-wp'),
                'add_or_remove_items'        => __('Add or remove productions', 'curtain-call-wp'),
                'choose_from_most_used'      => __('Choose from the most used productions', 'curtain-call-wp'),
                'not_found'                  => __('No productions found.', 'curtain-call-wp'),
                'menu_name'                  => __('Productions', 'curtain-call-wp'),
            ],
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => [
                'slug' => 'cast-and-crew-productions',
                'with_front' => true,
            ],
        ];
    }
}