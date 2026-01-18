<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\View;
use Illuminate\Support\Arr;
use WP_Post;

final class FrontendController
{
    /**
     * Register the stylesheets for the public-facing side of the site
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        $fontawesomeSrc = ccwp_plugin_url('assets/frontend/fontawesomefree.css');
        $frontendSrc = ccwp_plugin_url('assets/frontend/curtain-call-wp-frontend.css');
        $version = $this->getVersion();

        wp_enqueue_style('fontawesomefree', $fontawesomeSrc, [], $version);
        wp_enqueue_style(CCWP_PLUGIN_NAME, $frontendSrc, [], $version);

        $customCss = $this->generateCustomColorCss();
        if (!empty($customCss)) {
            wp_add_inline_style(CCWP_PLUGIN_NAME, $customCss);
        }
    }

    /**
     * Generate custom CSS based on color settings
     *
     * @return string
     */
    private function generateCustomColorCss(): string
    {
        /** @var string $linkHighlight */
        $linkHighlight = get_option('ccwp_color_link_highlight', '');
        /** @var string $buttonBg */
        $buttonBg = get_option('ccwp_color_button_background', '');
        /** @var string $buttonText */
        $buttonText = get_option('ccwp_color_button_text', '');
        /** @var string[] $cssVars */
        $cssVars = [];

        if (!empty($linkHighlight)) {
            $cssVars[] = '--ccwp-link-highlight: ' . $linkHighlight;
        }

        if (!empty($buttonBg)) {
            $cssVars[] = '--ccwp-button-background-color: ' . $buttonBg;
        }

        if (!empty($buttonText)) {
            $cssVars[] = '--ccwp-button-text-color: ' . $buttonText;
        }

        if (empty($cssVars)) {
            return '';
        }

        return ':root { ' . implode('; ', $cssVars) . '; }';
    }

    /**
     * Determine if a hex color is dark based on relative luminance
     *
     * Uses the WCAG relative luminance formula to determine if a color
     * is dark (returns true) or light (returns false).
     *
     * @param string $hex The hex color to check
     * @return bool True if the color is dark, false if light
     */
    private function isColorDark(string $hex): bool
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Apply gamma correction (sRGB to linear)
        $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        // Calculate relative luminance
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        // Return true if dark (luminance < 0.5)
        return $luminance < 0.5;
    }

    /**
     * Determine if the current theme has a ccwp post-type template
     *
     * @param string $type
     * @param array|string[] $templates
     * @return bool
     */
    private function themeHasTemplate(string $type, array $templates): bool
    {
        $themeTemplate = locate_template(
            Arr::where($templates, static fn($item) => $item !== "{$type}.php"),
            false,
            false,
            [],
        );

        return (bool) $themeTemplate;
    }

    /**
     * Override the theme's single template with CurtainCall's custom template
     *
     * @param string $template
     * @param string $type
     * @param array|string[] $templates
     * @return string
     * @global WP_Post $post
     */
    public function loadSingleTemplates(string $template, string $type, array $templates): string
    {
        $postType = $this->getPostType();

        if ($postType === Production::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_production.php');
        }

        if ($postType === CastAndCrew::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_cast_and_crew.php');
        }

        return $template;
    }

    /**
     * Override the theme's archive template with CurtainCall's custom template
     *
     * @param string $template
     * @param string $type
     * @param array|string[] $templates
     * @return string
     */
    public function loadArchiveTemplates(string $template, string $type, array $templates): string
    {
        if (is_post_type_archive(Production::POST_TYPE) && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_production.php');
        }

        if (is_post_type_archive(CastAndCrew::POST_TYPE) && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_cast_and_crew.php');
        }

        return $template;
    }

    /**
     * Get the current post-type
     *
     * @return string|null
     */
    private function getPostType(): ?string
    {
        /** @var WP_Post|array{post_type: string|null}|null $post */
        $post = get_post();

        if ($post instanceof WP_Post && isset($post->post_type)) {
            return $post->post_type;
        }

        if (is_array($post) && isset($post['post_type'])) {
            return $post['post_type'];
        }

        return null;
    }

    /**
     * Get the version to use for enqueued assets. Debug mode will use a random version to force reloads.
     *
     * @return string
     */
    private function getVersion(): string
    {
        return defined('CCWP_DEBUG') && CCWP_DEBUG === true ? (string) rand() : CCWP_PLUGIN_VERSION;
    }
}
