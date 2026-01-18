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
        add_settings_section(
            'ccwp_default_links',
            __('Links', CCWP_TEXT_DOMAIN),
            [$this, 'renderLinksSectionHeader'],
            'ccwp',
        );

        register_setting('ccwp-settings', 'ccwp_default_ticket_url', [
            'type' => 'string',
            'description' => __(
                'The default tickets url to use if no production ticket url is defined.',
                CCWP_TEXT_DOMAIN,
            ),
            'sanitize_callback' => static fn(string $value) => esc_url_raw($value, ['http', 'https']),
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

        // Colors Section
        add_settings_section(
            'ccwp_colors',
            __('Colors', CCWP_TEXT_DOMAIN),
            [$this, 'renderColorsSectionHeader'],
            'ccwp',
        );

        register_setting('ccwp-settings', 'ccwp_color_link_highlight', [
            'type' => 'string',
            'description' => __('The color used for link highlights on hover.', CCWP_TEXT_DOMAIN),
            'sanitize_callback' => 'sanitize_hex_color',
            'show_in_rest' => false,
            'default' => '',
        ]);

        add_settings_field(
            'ccwp_color_link_highlight',
            __('Link Highlight Color', CCWP_TEXT_DOMAIN),
            [$this, 'renderColorField'],
            'ccwp',
            'ccwp_colors',
            [
                'label_for' => 'ccwp_color_link_highlight',
                'input-name' => 'ccwp_color_link_highlight',
                'input-default-color' => '#e74c3c',
                'input-help-text' => __(
                    'Color used for link hover states. Leave empty to use the default (red).',
                    CCWP_TEXT_DOMAIN,
                ),
            ],
        );

        register_setting('ccwp-settings', 'ccwp_color_button_background', [
            'type' => 'string',
            'description' => __('The background color for buttons', CCWP_TEXT_DOMAIN),
            'sanitize_callback' => 'sanitize_hex_color',
            'show_in_rest' => false,
            'default' => '',
        ]);

        add_settings_field(
            'ccwp_color_button_background',
            __('Button Background Color', CCWP_TEXT_DOMAIN),
            [$this, 'renderColorField'],
            'ccwp',
            'ccwp_colors',
            [
                'label_for' => 'ccwp_color_button_background',
                'input-name' => 'ccwp_color_button_background',
                'input-default-color' => '#e74c3c',
                'input-help-text' => __(
                    'Button background color. Leave empty to use the default (red).',
                    CCWP_TEXT_DOMAIN,
                ),
            ],
        );

        register_setting('ccwp-settings', 'ccwp_color_button_text', [
            'type' => 'string',
            'description' => __('The text color for buttons.', CCWP_TEXT_DOMAIN),
            'sanitize_callback' => 'sanitize_hex_color',
            'show_in_rest' => false,
            'default' => '',
        ]);

        add_settings_field(
            'ccwp_color_button_text',
            __('Button Text Color', CCWP_TEXT_DOMAIN),
            [$this, 'renderColorField'],
            'ccwp',
            'ccwp_colors',
            [
                'label_for' => 'ccwp_color_button_text',
                'input-name' => 'ccwp_color_button_text',
                'input-default-color' => '#ffffff',
                'input-help-text' => __(
                    'Button text color. Leave empty to use the default (white).',
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
     * Enqueue styles for the settings page
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        if (!$this->isSettingsPage()) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Enqueue scripts for the settings page
     *
     * @return void
     */
    public function enqueueScripts(): void
    {
        if (!$this->isSettingsPage()) {
            return;
        }

        wp_enqueue_script('wp-color-picker');

        $inlineScript = <<<JS
        jQuery(function($) {
            $('.ccwp-color-picker').wpColorPicker();
        });
        JS;

        wp_add_inline_script('wp-color-picker', $inlineScript);
    }

    /**
     * Check if the current page is the plugin settings page
     *
     * @return bool
     */
    private function isSettingsPage(): bool
    {
        $screen = get_current_screen();

        return $screen !== null && $screen->id === 'settings_page_ccwp-settings';
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

    public function renderLinksSectionHeader(): void
    {
        // do nothing
    }

    public function renderColorsSectionHeader(): void
    {
        ?>
        <p><?php echo esc_html(__(
            'Customize the colors used throughout the plugin\'s frontend templates.',
            CCWP_TEXT_DOMAIN,
        )); ?></p>
        <?php
    }

    /**
     * Render a color picker field on the settings page
     *
     * @param array $args
     * @return void
     * @throws Throwable
     */
    public function renderColorField(array $args): void
    {
        /** @var string|null $name */
        $name = Arr::get($args, 'input-name');
        /** @var string|null $optionValue */
        $optionValue = $name ? get_option($name, '') : '';

        View::make('admin/color-field.php', [
            'name' => $name,
            'id' => Arr::get($args, 'label_for'),
            'defaultColor' => Arr::get($args, 'input-default-color', '#000000'),
            'helpText' => Arr::get($args, 'input-help-text'),
            'value' => $optionValue,
        ])->render();
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
        /** @var string|null $name */
        $name = Arr::get($args, 'input-name');
        /** @var string|null $optionValue */
        $optionValue = $name ? get_option($name, null) : null;

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
