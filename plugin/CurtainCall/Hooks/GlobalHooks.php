<?php

declare(strict_types=1);

namespace CurtainCall\Hooks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\View;
use Illuminate\Support\Arr;
use Throwable;

final class GlobalHooks
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
     * Create the admin settings page and register the admin settings
     *
     * @return void
     */
    public function addPluginSettings(): void
    {
        add_settings_section('ccwp_default_links', __('Links', CCWP_TEXT_DOMAIN), null, 'ccwp');

        register_setting('ccwp-settings', 'ccwp_default_ticket_url', [
            'type' => 'string',
            'description' => 'The default tickets url to use if no production ticket url is defined.',
            'sanitize_callback' => static fn($value) => esc_url_raw($value, ['http', 'https']),
            'show_in_rest' => false,
            'default' => null,
        ]);

        add_settings_field(
            'ccwp_default_ticket_url',
            __('Default Tickets Url', CCWP_TEXT_DOMAIN),
            [$this, 'renderTextField'],
            'ccwp',
            'ccwp_default_links',
            [
                'label_for' => 'ccwp_default_ticket_url',
                'input-name' => 'ccwp_default_ticket_url',
                'input-placeholder' => 'https://www.rutheckerdhall.com/events',
                'input-classes' => ['regular-text', 'ltr'],
                'input-help-text' => 'Used for the "Get Tickets" link when no specific production ticket url is provided.',
            ],
        );
    }

    /**
     * Generic function to render a simple text field on the settings page
     *
     * @param array $args
     * @return void
     * @throws Throwable
     */
    public function renderTextField(array $args): void
    {
        $name = Arr::get($args, 'input-name');
        $optionValue = get_option($name, null);

        View::make('admin/text-field.php', [
            'name' => $name,
            'id' => Arr::get($args, 'label_for'),
            'classes' => Arr::get($args, 'input-classes', []),
            'placeholder' => Arr::get($args, 'input-placeholder'),
            'required' => Arr::get($args, 'input-required', false),
            'readonly' => Arr::get($args, 'input-readonly', false),
            'helpText' => Arr::get($args, 'input-help-text'),
            'value' => $optionValue,
        ])->render();
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
        // Production meta
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
