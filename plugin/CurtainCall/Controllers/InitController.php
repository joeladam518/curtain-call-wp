<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

final class InitController
{
    /**
     * Register the cast/crew custom post-type
     *
     * @return void
     */
    public function createCastAndCrewPostType(): void
    {
        register_post_type(CastAndCrew::POST_TYPE, CastAndCrew::getConfig());
        flush_rewrite_rules();
    }

    /**
     * Register the production custom post-type
     *
     * @return void
     */
    public function createProductionPostType(): void
    {
        register_post_type(Production::POST_TYPE, Production::getConfig());
        flush_rewrite_rules();
    }

    /**
     * Create the production custom post-type taxonomies
     *
     * @return void
     */
    public function createProductionSeasonsTaxonomy(): void
    {
        register_taxonomy(
            Production::SEASONS_TAXONOMY,
            [Production::POST_TYPE],
            Production::getSeasonsTaxonomyConfig(),
        );
        flush_rewrite_rules();
    }

    /**
     * Register post-meta so it is available in the REST API / block editor
     *
     * @return void
     */
    public function registerCastCrewMeta(): void
    {
        $castCrewMeta = [
            'name_first' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'name_last' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'self_title' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'birthday' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'hometown' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'website_link' => ['type' => 'string', 'sanitize' => 'esc_url_raw'],
            'facebook_link' => ['type' => 'string', 'sanitize' => 'esc_url_raw'],
            'twitter_link' => ['type' => 'string', 'sanitize' => 'esc_url_raw'],
            'instagram_link' => ['type' => 'string', 'sanitize' => 'esc_url_raw'],
            'fun_fact' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        ];

        foreach ($castCrewMeta as $key => $schema) {
            register_post_meta('ccwp_cast_and_crew', "_ccwp_cast_crew_{$key}", [
                'type' => $schema['type'],
                'single' => true,
                'sanitize_callback' => $schema['sanitize'],
                'show_in_rest' => true,
                'auth_callback' => static fn() => current_user_can('edit_posts'),
            ]);
        }
    }

    /**
     * Register post-meta so it is available in the REST API / block editor
     *
     * @return void
     */
    public function registerProductionMeta(): void
    {
        $productionMeta = [
            'name' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'date_start' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'date_end' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'show_times' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
            'ticket_url' => ['type' => 'string', 'sanitize' => 'esc_url_raw'],
            'venue' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        ];

        foreach ($productionMeta as $key => $schema) {
            register_post_meta('ccwp_production', "_ccwp_production_{$key}", [
                'type' => $schema['type'],
                'single' => true,
                'sanitize_callback' => $schema['sanitize'],
                'show_in_rest' => true,
                'auth_callback' => static fn() => current_user_can('edit_posts'),
            ]);
        }
    }
}
