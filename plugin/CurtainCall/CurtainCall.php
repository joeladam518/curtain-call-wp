<?php

namespace CurtainCall;

use CurtainCall\Hooks\AdminHooks;
use CurtainCall\Hooks\FrontendHooks;
use CurtainCall\Hooks\GlobalHooks;
use CurtainCall\LifeCycle\Activator;
use CurtainCall\LifeCycle\Deactivator;
use CurtainCall\LifeCycle\Uninstaller;

class CurtainCall
{
    const PLUGIN_NAME = CCWP_PLUGIN_NAME;
    const PLUGIN_VERSION = CCWP_PLUGIN_VERSION;

    /**
     * The loader that's responsible for maintaining and
     * registering all hooks that power the plugin.
     *
     * @var Loader
     */
    protected Loader $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * Include the following files that make up the plugin:
     *
     * Loader         - Orchestrates the hooks of the plugin.
     * GlobalHooks    - Defines the hooks that happen for both Admin and Frontend
     * AdminHooks     - Defines the hooks for the admin area.
     * FrontendHooks  - Defines the hooks for the public side of the site.
     */
    public function __construct()
    {
        $this->loader = new Loader();

        $this->registerLocales();
        $this->registerGlobalHooks();
        $this->registerAdminHooks();
        $this->registerFrontendHooks();
    }

    /**
     * Register the plugin's life cycle hooks
     *
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
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @return self
     */
    public static function run(): self
    {
        $plugin = new static();
        $plugin->boot();

        return $plugin;
    }

    /**
     * Boot the plugin
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loader->run();
    }

    /**
     * Register all the hooks related to the admin area functionality of the plugin.
     *
     * @return void
     */
    protected function registerAdminHooks(): void
    {
        $controller = new AdminHooks();

        // All Actions and Filters on the Production custom post type
        $this->loader->addAction('add_meta_boxes', [$controller, 'addProductionPostMetaBoxes']);
        $this->loader->addAction('save_post_ccwp_production', [$controller, 'saveProductionPostDetails'], 2);
        $this->loader->addAction('save_post_ccwp_production', [$controller, 'saveProductionPostCastAndCrew'], 2);

        // All Actions and Filters on the Cast and Crew custom post type
        $this->loader->addAction('add_meta_boxes', [$controller, 'addCastAndCrewPostMetaBoxes'], 0);
        $this->loader->addAction('save_post_ccwp_cast_and_crew', [$controller, 'saveCastAndCrewPostDetails'], 2);

        // All Actions and Filters that concern both post types
        $this->loader->addFilter('wp_insert_post_data', [$controller, 'setTitleOnPostSave'], 2);

        // Scripts and style to be loaded for the admin area in the WordPress backend
        $this->loader->addAction('admin_enqueue_scripts', [$controller, 'enqueueStyles']);
        $this->loader->addAction('admin_enqueue_scripts', [$controller, 'enqueueScripts']);
    }

    /**
     * Register all hook related to bother the admin and frontend functionality
     *
     * @return void
     */
    protected function registerGlobalHooks(): void
    {
        $controller = new GlobalHooks();

        $this->loader->addAction('init', [$controller, 'createProductionPostType']);
        $this->loader->addAction('init', [$controller, 'createCastAndCrewPostType']);
        $this->loader->addAction('init', [$controller, 'createProductionSeasonsTaxonomy']);
        //$this->loader->addFilter('rewrite_rules_array', [$controller, 'filterRewriteRulesArray'], 1);
    }

    /**
     * Register all the hooks related to the public-facing functionality of the plugin.
     *
     * @return void
     */
    protected function registerFrontendHooks(): void
    {
        $controller = new FrontendHooks();

        $this->loader->addFilter('single_template', [$controller, 'loadSingleTemplates'], 3);
        $this->loader->addFilter('archive_template', [$controller, 'loadArchiveTemplates'], 3);

        $this->loader->addAction('wp_enqueue_scripts', [$controller, 'enqueueStyles']);
        //$this->loader->addAction('wp_enqueue_scripts', [$controller, 'enqueueScripts']);
    }

    /**
     * Define the locales for this plugin for internationalization.
     *
     * @return void
     */
    protected function registerLocales(): void
    {
        load_plugin_textdomain(
            static::PLUGIN_NAME,
            false,
            CCWP_PLUGIN_PATH.'languages/'
        );
    }
}
