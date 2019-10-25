<?php

namespace CurtainCallWP\Controllers;

use CurtainCallWP\CurtainCallView;

/**
 * Class FrontendController
 * @package CurtainCallWP\Controllers
 */
class FrontendController extends CurtainCallController
{
    /**
     *  Initialize the class and set its properties.
     */
    public function __construct()
    {
        parent::__construct();
    
        $this->assets_url = ccwp_assets_url() . 'frontend/';
        $this->assets_path = ccwp_assets_path() . 'frontend/';
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles()
    {
        $frontend_css_url = $this->assets_url . 'css/curtain-call-wp-public.css';
        wp_enqueue_style($this->plugin_name, $frontend_css_url, array(), $this->plugin_version, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
    **/
    public function enqueue_scripts()
    {
        $frontend_js_url = $this->assets_url . 'js/curtain-call-wp-public.js';
        wp_enqueue_script($this->plugin_name, $frontend_js_url, array('jquery'), $this->plugin_version, false);
    }
    
    /**
     * @global $post
     * @param $template
     * @return string
     */
    public function load_ccwp_page_templates($template)
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
    public function load_ccwp_archive_templates($template)
    {
        global $post;
        
        if (is_post_type_archive('ccwp_production') && $template !== locate_template(['single-ccwp_production.php'])) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_production.php';
        }
        
        if (is_post_type_archive('ccwp_cast_and_crew') && $template !== locate_template(['single-ccwp_cast_and_crew.php'])) {
            return CurtainCallView::dirPath('frontend/') . 'templates/archive-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
    
    public function register_ccwp_rewrite_rules($rules)
    {
        $new = [
            'productions/?$'        => 'index.php?post_type=ccwp_production',
            'productions/(.+)/?$'   => 'index.php?ccwp_production=$matches[1]',
            'cast-and-crew/?$'      => 'index.php?post_type=ccwp_cast_and_crew',
            'cast-and-crew/(.+)/?$' => 'index.php?ccwp_cast_and_crew=$matches[1]',
        ];
        
        return array_merge($new, $rules);
    }
}
