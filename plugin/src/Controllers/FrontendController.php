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
    
    /**
     * Organize all CCWP's rewrite rules and put them near the top of the rules page
     * Also enter 2 new rules that make it easier to see posts
     *
     * @param $rules
     * @return array
     */
    public function register_ccwp_rewrite_rules($rules)
    {
        //$first_rules_part = [];
        //$last_rules_part  = [];
        //$productions_rules = [];
        //$cast_and_crew_rules = [];
        //$inserted_production_rule = false;
        //$inserted_cast_and_crew_rule = false;
        //$found_start_of_ccwp_post_types = false;
        //foreach($rules as $rule_key => $rule_value) {
        //    if (preg_match('~^production(?:s|-seasons)~', $rule_key)) {
        //        $productions_rules[$rule_key] = $rule_value;
        //        $found_start_of_ccwp_post_types = true;
        //        if (! $inserted_production_rule && preg_match('~^productions/page/~', $rule_key)) {
        //            $productions_rules['productions/(.+)/?$'] = 'index.php?ccwp_production=$matches[1]';
        //            $inserted_production_rule = true;
        //        }
        //        continue;
        //    }
        //
        //    if (preg_match('~^cast-and-crew(?:-productions)?~', $rule_key)) {
        //        $cast_and_crew_rules[$rule_key] = $rule_value;
        //        $found_start_of_ccwp_post_types = true;
        //        if (! $inserted_cast_and_crew_rule && preg_match('~^cast-and-crew/page/~', $rule_key)) {
        //            $cast_and_crew_rules['cast-and-crew/(.+)/?$'] = 'index.php?ccwp_cast_and_crew=$matches[1]';
        //            $inserted_cast_and_crew_rule = true;
        //        }
        //        continue;
        //    }
        //
        //    if (! $found_start_of_ccwp_post_types) {
        //        $first_rules_part[$rule_key] = $rule_value;
        //        continue;
        //    }
        //
        //    $last_rules_part[$rule_key] = $rule_value;
        //}
        //
        //$new_rules = array_merge($first_rules_part, $productions_rules, $cast_and_crew_rules, $last_rules_part);
        //
        //$new_rules['productions/?$'] = 'index.php?post_type=ccwp_production';
        
        pr($rules, true);
        return $rules;
    }
}
