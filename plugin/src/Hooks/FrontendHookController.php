<?php

namespace CurtainCallWP\Hooks;

use CurtainCallWP\CurtainCallView;

/**
 * Class FrontendController
 * @package CurtainCallWP\Controllers
 */
class FrontendHookController extends CurtainCallHookController
{
    /**
     *  Initialize the class and set its properties.
     */
    public function __construct()
    {
        parent::__construct();
    
        $this->assets_url = ccwpAssetsUrl() . 'frontend/';
        $this->assets_path = ccwp_assets_path() . 'frontend/';
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueStyles()
    {
        $frontend_css_url = $this->assets_url . 'curtain-call-wp-frontend.css';
        wp_enqueue_style($this->plugin_name, $frontend_css_url, array(), $this->plugin_version, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
    **/
    public function enqueueScripts()
    {
        $frontend_js_url = $this->assets_url . 'curtain-call-wp-frontend.js';
        wp_enqueue_script($this->plugin_name, $frontend_js_url, array('jquery'), $this->plugin_version, false);
    }
    
    /**
     * @global $post
     * @param $template
     * @return string
     */
    public function loadSingleTemplates($template)
    {
        global $post;
        
        if ($post->post_type == 'ccwp_production' && $template !== locate_template(['single-ccwp_production.php'])) {
            return CurtainCallView::dirPath('frontend/') . '/templates/single-ccwp_production.php';
        }
        
        if ($post->post_type == 'ccwp_cast_and_crew' && $template !== locate_template(['single-ccwp_cast_and_crew.php'])) {
            return CurtainCallView::dirPath('frontend') . '/templates/single-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
    
    /**
     * @global $post
     * @param $template
     * @return string
     */
    public function loadArchiveTemplates($template)
    {
        global $post;
        
        if (is_post_type_archive('ccwp_production') && $template !== locate_template(['archive-ccwp_production.php'])) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_production.php';
        }
        
        if (is_post_type_archive('ccwp_cast_and_crew') && $template !== locate_template(['archive-ccwp_cast_and_crew.php'])) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
}
