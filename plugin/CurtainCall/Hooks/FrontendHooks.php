<?php

namespace CurtainCallWP\Hooks;

use CurtainCallWP\CurtainCall;
use CurtainCallWP\View;
use WP_Post;

class FrontendHooks
{
    protected string $assetsUrl;
    protected string $assetsPath;

    /**
     * FrontendHookController constructor.
     */
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
     * @param array  $templates
     * @return bool
     */
    private function themeHasTemplate(string $type, array $templates): bool
    {
        $theme_template = locate_template(array_filter($templates, function($item) use($type) {
            return $item !== $type.'.php';
        }));

        return !empty($theme_template);
    }

    /**
     * @global WP_Post $post
     * @param  string  $template
     * @param  string  $type
     * @param  array   $templates
     * @return string
     */
    public function loadSingleTemplates($template, $type, $templates): string
    {
        global $post;

        if ($post->post_type === 'ccwp_production' && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_production.php');
        }

        if ($post->post_type === 'ccwp_cast_and_crew' && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_cast_and_crew.php');
        }

        return $template;
    }

    /**
     * @param  string  $template
     * @param  string  $type
     * @param  array   $templates
     * @return string
     */
    public function loadArchiveTemplates($template, $type, $templates): string
    {
        if (is_post_type_archive('ccwp_production') && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_production.php');
        }

        if (is_post_type_archive('ccwp_cast_and_crew') && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_cast_and_crew.php');
        }

        return $template;
    }
}
