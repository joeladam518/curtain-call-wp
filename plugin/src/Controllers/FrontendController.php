<?php

namespace CurtainCallWP\Controllers;

class FrontendController extends CurtainCallController
{
    /**
     *  Initialize the class and set its properties.
     *
     * @param string $plugin_name    The name of this plugin.
     * @param string $plugin_version The version of this plugin.
     */
    public function __construct(string $plugin_name, string $plugin_version)
    {
        parent::__construct($plugin_name, $plugin_version);
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         **/
        
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/curtain-call-wp-public.css', array(), $this->plugin_version, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
    **/
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         **/
        
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/curtain-call-wp-public.js', array('jquery'), $this->plugin_version, false);
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
            return plugin_dir_path( __FILE__ ) . 'templates/single-ccwp_production.php';
        }
        
        if ($post->post_type == 'ccwp_cast_and_crew' && $template !== locate_template(['single-ccwp_cast_and_crew.php'])) {
            return plugin_dir_path( __FILE__ ) . 'templates/single-ccwp_cast_and_crew.php';
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
            return plugin_dir_path(__FILE__) . 'templates/archive-ccwp_production.php';
        }
        
        if (is_post_type_archive('ccwp_cast_and_crew') && $template !== locate_template(['single-ccwp_cast_and_crew.php'])) {
            return plugin_dir_path(__FILE__) . 'templates/archive-ccwp_cast_and_crew.php';
        }
        
        return $template;
    }
}
