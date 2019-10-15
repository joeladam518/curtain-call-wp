<?php

namespace CurtainCallWP\frontend;

/**
 * The public-facing functionality of the plugin.
 *
 *  @link       http://example.com
 *  @since      0.0.1
 *
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/public
**/

/**
 *  The public-facing functionality of the plugin.
 *
 *  Defines the plugin name, version, and two examples hooks for how to
 *  enqueue the public-facing stylesheet and JavaScript.
 *
 *  @since      0.0.1
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/public
 *  @author     Joel Haker <joel@greenbar.co>
**/
class CurtainCallPublic 
{
    /**
     *  The ID of this plugin.
     *
     *  @since      0.0.1
     *  @access   private
     *  @var      string    $plugin_name    The ID of this plugin.
    **/
    private $plugin_name;

    /**
     *  The version of this plugin.
     *
     *  @since    0.0.1
     *  @access   private
     *  @var      string    $version    The current version of this plugin.
    **/
    private $version;

    /**
     *  Initialize the class and set its properties.
     *
     *  @since      0.0.1
     *  @param      string    $plugin_name       The name of the plugin.
     *  @param      string    $version    The version of this plugin.
    **/
    public function __construct($plugin_name, $version) 
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since      0.0.1
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/curtain-call-wp-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since      0.0.1
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

        wp_enqueue_script($this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/curtain-call-wp-public.js', array('jquery'), $this->version, false);
    }
    
    
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
