<?php

namespace CurtainCallWP;

use CurtainCallWP\includes\CurtainCallLoader;
use CurtainCallWP\includes\CurtainCall_i18n;
use CurtainCallWP\admin\CurtainCallAdmin;
use CurtainCallWP\frontend\CurtainCallPublic;

/**
 *  The file that defines the core plugin class
 *
 *  A class definition that includes attributes and functions used across both the
 *  public-facing side of the site and the admin area.
 *
 *  @link       http://greenbar.co
 *  @since      0.0.1
 *
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
**/

/**
 *  The core plugin class.
 *
 *  This is used to define internationalization, admin-specific hooks, and
 *  public-facing site hooks.
 *
 *  Also maintains the unique identifier of this plugin as well as the current
 *  version of the plugin.
 *
 *  @since      0.0.1
 *  @package    CurtainCallWP
 *  @subpackage CurtainCallWP/includes
 *  @author     Joel Haker <joel@greenbar.co>
**/
class CurtainCall 
{
    /**
     *  The loader that's responsible for maintaining and registering all hooks that power
     *  the plugin.
     *
     *  @since    0.0.1
     *  @access   protected
     *  @var      CurtainCallLoader    $loader    Maintains and registers all hooks for the plugin.
    **/
    protected $loader;

    /**
     *  The unique identifier of this plugin.
     *
     *  @since    0.0.1
     *  @access   protected
     *  @var      string    $plugin_name    The string used to uniquely identify this plugin.
    **/
    protected $plugin_name;

    /**
     *  The current version of the plugin.
     *
     *  @since    0.0.1
     *  @access   protected
     *  @var      string    $version    The current version of the plugin.
    **/
    protected $version;

    /**
     *  Define the core functionality of the plugin.
     *
     *  Set the plugin name and the plugin version that can be used throughout the plugin.
     *  Load the dependencies, define the locale, and set the hooks for the admin area and
     *  the public-facing side of the site.
     *
     *  @since    0.0.1
    **/
    public function __construct() 
    {
        $this->version = defined('CCWP_VERSION') ? CCWP_VERSION : '0.0.1';
        $this->plugin_name = defined('CCWP_PLUGIN_NAME') ? CCWP_PLUGIN_NAME : 'CurtainCallWP';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     *  Load the required dependencies for this plugin.
     *
     *  Include the following files that make up the plugin:
     *
     *  CurtainCallLoader - Orchestrates the hooks of the plugin.
     *  CurtainCall_i18n  - Defines internationalization functionality.
     *  CurtainCallAdmin  - Defines all hooks for the admin area.
     *  CurtainCallPublic - Defines all hooks for the public side of the site.
     *
     *  Create an instance of the loader which will be used to register the hooks
     *  with WordPress.
     *
     *  @since    0.0.1
     *  @access   private
    **/
    private function load_dependencies() 
    {
        $this->loader = new CurtainCallLoader();
    }

    /**
     *  Define the locale for this plugin for internationalization.
     *
     *  Uses the CurtainCall_i18n class in order to set the domain and to register the hook
     *  with WordPress.
     *
     *  @since    1.0.0
     *  @access   private
    **/
    private function set_locale() 
    {
        $plugin_i18n = new CurtainCall_i18n();
        
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     *  Register all of the hooks related to the admin area functionality
     *  of the plugin.
     *
     *  @since    0.0.1
     *  @access   private
    **/
    private function define_admin_hooks() 
    {
        $plugin_admin = new CurtainCallAdmin($this->get_plugin_name(), $this->get_version());
        
        # All Actions and Filters on the Production custom post type
        $this->loader->add_action('init', $plugin_admin, 'create_production_custom_post_type');
        $this->loader->add_action('init', $plugin_admin, 'create_production_custom_taxonomies');
        $this->loader->add_action('wp_insert_post', $plugin_admin, 'on_insert_production_post', 10, 3);
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_custom_meta_boxes_for_production_post_type', 10, 3);
        $this->loader->add_action('save_post_ccwp_production', $plugin_admin, 'ccwp_production_details_save', 10, 2);
        $this->loader->add_action('save_post_ccwp_production', $plugin_admin, 'ccwp_add_cast_and_crew_to_production_save', 10, 2);
        
        # All Actions and Filters on the Cast and Crew custom post type
        $this->loader->add_action('init', $plugin_admin, 'create_cast_crew_custom_post_type');
        $this->loader->add_action('init', $plugin_admin, 'create_cast_crew_custom_taxonomies');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_custom_meta_box_for_cast_and_crew_post_type', 10, 3);
        $this->loader->add_action('save_post_ccwp_cast_and_crew', $plugin_admin, 'ccwp_cast_and_crew_details_save', 10, 2);
        
        # All Actions and Filters that concern both post types
        $this->loader->add_filter('wp_insert_post_data', $plugin_admin, 'ccwp_set_post_type_title_on_post_submit', 99, 1);
        
        # Scripts and style to be loaded for the admin area in the WordPress backend
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     *  Register all of the hooks related to the public-facing functionality
     *  of the plugin.
     *
     *  @since    0.0.1
     *  @access   private
    **/
    private function define_public_hooks() 
    {
        $plugin_public = new CurtainCallPublic($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_filter('single_template', $plugin_public, 'load_ccwp_page_templates');
        $this->loader->add_filter('archive_template', $plugin_public, 'load_ccwp_archive_templates');

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     *  Run the loader to execute all of the hooks with WordPress.
     *
     *  @since    0.0.1
     *  @access   public
    **/
    public function run() 
    {
        $this->loader->run();
    }

    /**
     *  The name of the plugin used to uniquely identify it within the context of
     *  WordPress and to define internationalization functionality.
     *
     *  @since     0.0.1
     *  @return    string    The name of the plugin.
    **/
    public function get_plugin_name() 
    {
        return $this->plugin_name;
    }

    /**
     *  The reference to the class that orchestrates the hooks with the plugin.
     *
     *  @since     0.0.1
     *  @return    CurtainCallLoader    Orchestrates the hooks of the plugin.
    **/
    public function get_loader() 
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.0.1
     * @return    string    The version number of the plugin.
    **/
    public function get_version() 
    {
        return $this->version;
    }
}
