<?php

namespace CurtainCall\Hooks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Arr;
use CurtainCall\Support\Str;
use CurtainCall\View;
use Throwable;

class GlobalHooks
{
    /**
     * Register the cast/crew custom post type
     *
     * @return void
     */
    public function createCastAndCrewPostType(): void
    {
        register_post_type(
            CastAndCrew::POST_TYPE,
            CastAndCrew::getConfig()
        );
        flush_rewrite_rules();
    }

    /**
     * Register the production custom post type
     *
     * @return void
     */
    public function createProductionPostType(): void
    {
        register_post_type(
            Production::POST_TYPE,
            Production::getConfig()
        );
        flush_rewrite_rules();
    }

    /**
     * Create the production custom post type taxonomies
     *
     * @return void
     */
    public function createProductionSeasonsTaxonomy(): void
    {
        register_taxonomy(
            Production::SEASONS_TAXONOMY,
            [Production::POST_TYPE],
            Production::getSeasonsTaxonomyConfig()
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
        add_settings_section(
            'ccwp_default_links',
            __('Links', CCWP_TEXT_DOMAIN),
            null,
            'ccwp'
        );

        register_setting('ccwp-settings', 'ccwp_default_ticket_url', [
            'type' => 'string',
            'description' => 'The default tickets url to use if no production ticket url is defined.',
            'sanitize_callback' => function ($value) {
                return esc_url_raw($value);
            },
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
                'input-help-text' => 'Used for the "Get Tickets" link when no specific production ticket url is provided.'
            ]
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

        View::make('admin/partials/text-field.php', [
            'name' => $name,
            'id' => Arr::get($args, 'label_for'),
            'classes' => Arr::get($args, 'input-classes', []),
            'placeholder' => Arr::get($args, 'input-placeholder'),
            'required' => Arr::get($args, 'input-required', false),
            'readonly' => Arr::get($args, 'input-readonly', false),
            'helpText' => Arr::get($args, 'input-help-text'),
            'value' => $optionValue
        ])->render();
    }
}

