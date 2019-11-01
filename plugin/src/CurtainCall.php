<?php

namespace CurtainCallWP;

use CurtainCallWP\Controllers\AdminHookController;
use CurtainCallWP\Controllers\FrontendHookController;
use CurtainCallWP\LifeCycle\Activator;
use CurtainCallWP\LifeCycle\Deactivator;
use CurtainCallWP\LifeCycle\Uninstaller;

/**
 * Class CurtainCall
 * @package CurtainCallWP
 */
class CurtainCall
{
    /**
     * The loader that's responsible for maintaining and registering all
     * hooks that power the plugin.
     *
     * @var CurtainCallLoader
     */
    protected $loader;
    
    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;
    
    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $plugin_version;
    
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * Include the following files that make up the plugin:
     *
     * CurtainCallLoader - Orchestrates the hooks of the plugin.
     * CurtainCall_i18n  - Defines internationalization functionality.
     * CurtainCallAdmin  - Defines all hooks for the admin area.
     * CurtainCallPublic - Defines all hooks for the public side of the site.
     *
     * @return void
     */
    public function __construct()
    {
        $this->plugin_name    = CCWP_PLUGIN_NAME;
        $this->plugin_version = CCWP_PLUGIN_VERSION;
        
        $this->initPluginLoader();
        $this->initPluginLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();
    }
    
    /**
     * Register the plugin's life cycle hooks
     * @param string $file
     * @return void
     */
    public static function registerLifeCycleHooks(string $file): void
    {
        register_activation_hook($file, array(Activator::class, 'run'));
        register_deactivation_hook($file, array(Deactivator::class, 'run'));
        register_uninstall_hook($file, array(Uninstaller::class, 'run'));
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     * @return void
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * Set the loader that manager the hooks
     * @return void
     */
    protected function initPluginLoader()
    {
        $this->loader = new CurtainCallLoader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     * @return void
     */
    protected function initPluginLocale()
    {
        load_plugin_textdomain(CCWP_PLUGIN_NAME, false, plugin_dir_path(__FILE__) . 'languages/');
    }
    
    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     * @return void
     */
    protected function defineAdminHooks()
    {
        $plugin_admin = new AdminHookController();
        
        // All Actions and Filters on the Production custom post type
        $this->loader->add_action('init', $plugin_admin, 'create_production_custom_post_type');
        $this->loader->add_action('init', $plugin_admin, 'create_production_custom_taxonomies');
        $this->loader->add_action('wp_insert_post', $plugin_admin, 'on_insert_production_post', 10, 3);
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_custom_meta_boxes_for_production_post_type', 10, 3);
        $this->loader->add_action('save_post_ccwp_production', $plugin_admin, 'ccwp_production_details_save', 10, 2);
        $this->loader->add_action('save_post_ccwp_production', $plugin_admin, 'ccwp_add_cast_and_crew_to_production_save', 10, 2);
        
        //All Actions and Filters on the Cast and Crew custom post type
        $this->loader->add_action('init', $plugin_admin, 'create_cast_crew_custom_post_type');
        $this->loader->add_action('init', $plugin_admin, 'create_cast_crew_custom_taxonomies');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_custom_meta_box_for_cast_and_crew_post_type', 10, 3);
        $this->loader->add_action('save_post_ccwp_cast_and_crew', $plugin_admin, 'ccwp_cast_and_crew_details_save', 10, 2);
        
        //All Actions and Filters that concern both post types
        $this->loader->add_filter('wp_insert_post_data', $plugin_admin, 'ccwp_set_post_type_title_on_post_submit', 10, 1);
        
        // Scripts and style to be loaded for the admin area in the WordPress backend
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     * @return void
     */
    protected function definePublicHooks()
    {
        $plugin_public = new FrontendHookController();
        
        $this->loader->add_filter('single_template', $plugin_public, 'load_ccwp_page_templates');
        $this->loader->add_filter('archive_template', $plugin_public, 'load_ccwp_archive_templates');
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }
}
