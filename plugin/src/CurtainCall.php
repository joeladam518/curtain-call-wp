<?php

namespace CurtainCallWP;

use CurtainCallWP\Hooks\AdminHooks;
use CurtainCallWP\Hooks\FrontendHooks;
use CurtainCallWP\Hooks\CurtainCallHooks;
use CurtainCallWP\LifeCycle\Activator;
use CurtainCallWP\LifeCycle\Deactivator;
use CurtainCallWP\LifeCycle\Uninstaller;

/**
 * Class CurtainCall
 * @package CurtainCallWP
 */
class CurtainCall
{
    const PLUGIN_NAME = CCWP_PLUGIN_NAME;
    const PLUGIN_VERSION = CCWP_PLUGIN_VERSION;
    
    /**
     * The loader that's responsible for maintaining and registering all
     * hooks that power the plugin.
     *
     * @var CurtainCallLoader
     */
    protected $loader;
    
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
     *
     * GlobalHookController   - Define the hooks that happen for both Admin and Frontend
     * AdminHookController    - Defines all hooks for the admin area.
     * FrontendHookController - Defines all hooks for the public side of the site.
     *
     * @return void
     */
    public function __construct()
    {
        $this->initPluginLoader();
        $this->initPluginLocale();
        $this->defineGlobalHooks();
        $this->defineAdminHooks();
        $this->defineFrontendHooks();
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
        load_plugin_textdomain(static::PLUGIN_NAME, false, plugin_dir_path(__FILE__) . 'languages/');
    }
    
    /**
     * Register all hook related to bother the admin and frontend functionality
     */
    protected function defineGlobalHooks()
    {
        $controller = new CurtainCallHooks();
        
        $this->loader->add_action('init', $controller, 'createProductionPostType', 10, 0);
        $this->loader->add_action('init', $controller, 'createProductionSeasonsTaxonomy', 10, 0);
        $this->loader->add_action('init', $controller, 'createCastAndCrewPostType', 10, 0);
    }
    
    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     * @return void
     */
    protected function defineAdminHooks()
    {
        $controller = new AdminHooks();
        
        // All Actions and Filters on the Production custom post type
        $this->loader->add_action('add_meta_boxes', $controller, 'addProductionPostMetaBoxes', 10, 0);
        $this->loader->add_action('save_post_ccwp_production', $controller, 'saveProductionPostDetails', 10, 2);
        $this->loader->add_action('save_post_ccwp_production', $controller, 'saveProductionPostCastAndCrew', 10, 2);

        // All Actions and Filters on the Cast and Crew custom post type
        $this->loader->add_action('add_meta_boxes', $controller, 'addCastAndCrewPostMetaBoxes', 10, 0);
        $this->loader->add_action('save_post_ccwp_cast_and_crew', $controller, 'saveCastAndCrewPostDetails', 10, 2);
        
        // All Actions and Filters that concern both post types
        $this->loader->add_filter('wp_insert_post_data', $controller, 'setPostTitleOnPostSave', 10, 2);
        
        // Scripts and style to be loaded for the admin area in the WordPress backend
        $this->loader->add_action('admin_enqueue_scripts', $controller, 'enqueueStyles');
        $this->loader->add_action('admin_enqueue_scripts', $controller, 'enqueueScripts');
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     * @return void
     */
    protected function defineFrontendHooks()
    {
        $controller = new FrontendHooks();
        
        $this->loader->add_filter('single_template', $controller, 'loadSingleTemplates', 10, 3);
        $this->loader->add_filter('archive_template', $controller, 'loadArchiveTemplates', 10, 3);
        
        $this->loader->add_action('wp_enqueue_scripts', $controller, 'enqueueStyles');
        $this->loader->add_action('wp_enqueue_scripts', $controller, 'enqueueScripts');
    }
}
