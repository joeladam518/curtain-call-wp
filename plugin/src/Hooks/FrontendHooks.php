<?php

namespace CurtainCallWP\Hooks;

use CurtainCallWP\CurtainCall;
use CurtainCallWP\CurtainCallView;
use WP_Post;

/**
 * Class FrontendController
 * @package CurtainCallWP\Controllers
 */
class FrontendHooks
{
    /** @var string  */
    protected $assets_url;
    
    /** @var string  */
    protected $assets_path;
    
    /**
     * FrontendHookController constructor.
     */
    public function __construct()
    {
        $this->assets_url = ccwpAssetsUrl() . 'frontend/';
        $this->assets_path = ccwpAssetsPath() . 'frontend/';
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     * @return void
     */
    public function enqueueStyles()
    {
        $frontend_css_url = $this->assets_url . 'curtain-call-wp-frontend.css';
        $fontawesome = $this->assets_url . 'fontawesomefree.css';
        $version = (CCWP_DEBUG) ? rand() : CurtainCall::PLUGIN_VERSION;
        wp_enqueue_style(CurtainCall::PLUGIN_NAME, $frontend_css_url, array(), $version, 'all');
        wp_enqueue_style('fontawesomefree', $fontawesome, array(), $version, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     * @return void
     */
    public function enqueueScripts()
    {
        $frontend_js_url = $this->assets_url . 'curtain-call-wp-frontend.js';
        $version = (CCWP_DEBUG) ? rand() : CurtainCall::PLUGIN_VERSION;
        wp_enqueue_script(CurtainCall::PLUGIN_NAME, $frontend_js_url, array('jquery'), $version, false);
    }
    
    /**
     * Determine if the current theme has a ccwp post type template
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
     * @param  string  $template
     * @param  string  $type
     * @param  array   $templates
     * @return string
     * @global WP_Post $post
     */
    public function loadSingleTemplates($template, $type, $templates)
    {
        global $post;
        
        if ($post->post_type === 'ccwp_production' && !$this->themeHasTemplate($type, $templates)) {
            return CurtainCallView::dirPath('frontend/') . '/templates/single-ccwp_production.php';
        }
        
        if ($post->post_type === 'ccwp_cast_and_crew' && !$this->themeHasTemplate($type, $templates)) {
            return CurtainCallView::dirPath('frontend') . '/templates/single-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
    
    /**
     * @param  string  $template
     * @param  string  $type
     * @param  array   $templates
     * @return string
     */
    public function loadArchiveTemplates($template, $type, $templates)
    {
        if (is_post_type_archive('ccwp_production') && !$this->themeHasTemplate($type, $templates)) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_production.php';
        }
        
        if (is_post_type_archive('ccwp_cast_and_crew') && !$this->themeHasTemplate($type, $templates)) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
}
