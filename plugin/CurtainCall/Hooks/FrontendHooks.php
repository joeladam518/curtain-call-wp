<?php

namespace CurtainCall\Hooks;

use CurtainCall\CurtainCall;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Arr;
use CurtainCall\View;
use WP_Post;

class FrontendHooks
{
    protected string $assetsUrl;
    protected string $assetsPath;

    public function __construct()
    {
        $this->assetsUrl = ccwpPluginUrl('assets/frontend/');
        $this->assetsPath = ccwpPluginPath('assets/frontend/');
    }

    /**
     * Register the stylesheets for the public-facing side of the site
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        $fontawesomeSrc = $this->assetsUrl . 'fontawesomefree.css';
        $frontendSrc = $this->assetsUrl . 'curtain-call-wp-frontend.css';
        $version = CCWP_DEBUG ? rand() : CurtainCall::PLUGIN_VERSION;

        wp_enqueue_style('fontawesomefree', $fontawesomeSrc, [], $version);
        wp_enqueue_style(CurtainCall::PLUGIN_NAME, $frontendSrc, [], $version);
    }

    /**
     * Register the JavaScript for the public-facing side of the site
     *
     * @return void
     */
    public function enqueueScripts(): void
    {
        $src = $this->assetsUrl . 'curtain-call-wp-frontend.js';
        $version = CCWP_DEBUG ? rand() : CurtainCall::PLUGIN_VERSION;

        wp_enqueue_script(CurtainCall::PLUGIN_NAME, $src, ['jquery'], $version, false);
    }

    /**
     * Determine if the current theme has a ccwp post type template
     *
     * @param string $type
     * @param array|string[] $templates
     * @return bool
     */
    private function themeHasTemplate(string $type, array $templates): bool
    {
        $themeTemplate = locate_template(
            Arr::filter($templates, fn($item) => $item !== "{$type}.php"),
            false,
            false,
            []
        );

        return !empty($themeTemplate);
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
        global $post;

        if ($post->post_type === Production::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_production.php');
        }

        if ($post->post_type === CastAndCrew::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
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
}
