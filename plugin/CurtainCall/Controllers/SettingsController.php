<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Support\View;
use Illuminate\Support\Arr;
use Throwable;

final class SettingsController
{
    /**
     * Create the admin settings page and register the admin settings
     *
     * @return void
     */
    public function registerSettings(): void
    {
        add_settings_section('ccwp_default_links', __('Links', CCWP_TEXT_DOMAIN), null, 'ccwp');

        register_setting('ccwp-settings', 'ccwp_default_ticket_url', [
            'type' => 'string',
            'description' => __(
                'The default tickets url to use if no production ticket url is defined.',
                CCWP_TEXT_DOMAIN,
            ),
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
                'input-help-text' => __(
                    'Used for the "Get Tickets" link when no specific production ticket url is provided.',
                    CCWP_TEXT_DOMAIN,
                ),
            ],
        );
    }

    /**
     * @return void
     */
    public function addPluginSettingsPage(): void
    {
        add_submenu_page(
            'options-general.php',
            __('Curtain Call WP', CCWP_TEXT_DOMAIN),
            __('Curtain Call WP', CCWP_TEXT_DOMAIN),
            'manage_options',
            'ccwp-settings',
            [$this, 'renderPluginSettingsPage'],
        );
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function renderPluginSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        View::make('admin/settings-page.php')->render();
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
}
