<?php

namespace CurtainCallWP\PostTypes;

class CastAndCrew extends CurtainCallPostType
{
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
            'menu_position' => 2.6,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_cast_crew_productions',
            ],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'cast-and-crew',
            ],
        ];
    }
}