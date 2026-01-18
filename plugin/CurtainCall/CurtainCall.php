<?php

declare(strict_types=1);

namespace CurtainCall;

use CurtainCall\Controllers\AdminCastCrewMetaboxController;
use CurtainCall\Controllers\AdminController;
use CurtainCall\Controllers\AdminProductionMetaboxController;
use CurtainCall\Controllers\FrontendController;
use CurtainCall\Controllers\InitController;
use CurtainCall\Controllers\RelationsRestController;
use CurtainCall\Controllers\SettingsController;
use CurtainCall\LifeCycle\Activator;
use CurtainCall\LifeCycle\Deactivator;
use CurtainCall\LifeCycle\Uninstaller;
use CurtainCall\Models\CurtainCallPost;

final class CurtainCall
{
    private function __construct()
    {
        CurtainCallPost::setPostAttributes();
    }

    /**
     * Register the plugin's life cycle hooks
     *
     * @param string $file
     * @return void
     */
    public static function registerLifeCycleHooks(string $file): void
    {
        register_activation_hook($file, [Activator::class, 'run']);
        register_deactivation_hook($file, [Deactivator::class, 'run']);
        register_uninstall_hook($file, [Uninstaller::class, 'run']);
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
        $plugin = new self();
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
        $this->registerLocales();
        $this->loadInitHooks();
        $this->loadAdminHooks();
        $this->loadFrontendHooks();
    }

    /**
     * Register all hook related to both the admin and frontend functionality
     *
     * @return void
     */
    private function loadInitHooks(): void
    {
        $initController = new InitController();
        add_action('init', [$initController, 'createProductionPostType'], 10, 0);
        add_action('init', [$initController, 'createProductionSeasonsTaxonomy'], 10, 0);
        add_action('init', [$initController, 'registerProductionMeta'], 10, 0);
        add_action('init', [$initController, 'createCastAndCrewPostType'], 10, 0);
        add_action('init', [$initController, 'registerCastCrewMeta'], 10, 0);
        add_action('rest_api_init', [RelationsRestController::class, 'registerRoutes'], 10, 0);
    }

    /**
     * Register all the hooks related to the admin area functionality of the plugin.
     *
     * @return void
     */
    private function loadAdminHooks(): void
    {
        $settingsController = new SettingsController();
        add_action('admin_init', [$settingsController, 'registerSettings'], 10, 0);
        add_action('admin_menu', [$settingsController, 'addPluginSettingsPage'], 10, 0);
        add_action('admin_enqueue_scripts', [$settingsController, 'enqueueStyles'], 10, 0);
        add_action('admin_enqueue_scripts', [$settingsController, 'enqueueScripts'], 10, 0);

        $adminCastCrewMetaboxController = new AdminCastCrewMetaboxController();
        add_action('add_meta_boxes', [$adminCastCrewMetaboxController, 'addMetaboxes'], 10, 0);
        add_action('save_post_ccwp_cast_and_crew', [$adminCastCrewMetaboxController, 'saveDetailsMeta'], 10, 3);
        add_action('save_post_ccwp_cast_and_crew', [$adminCastCrewMetaboxController, 'saveAddProductions'], 10, 3);

        $adminProductionMetaboxController = new AdminProductionMetaboxController();
        add_action('add_meta_boxes', [$adminProductionMetaboxController, 'addMetaboxes'], 10, 0);
        add_action('save_post_ccwp_production', [$adminProductionMetaboxController, 'saveDetailsMeta'], 10, 3);
        add_action('save_post_ccwp_production', [$adminProductionMetaboxController, 'saveAddCastCrew'], 10, 3);

        $adminController = new AdminController();
        add_filter('wp_insert_post_data', [$adminController, 'setTitleOnPostSave'], 10, 3);
        add_action('admin_enqueue_scripts', [$adminController, 'enqueueStyles'], 10, 0);
        add_action('admin_enqueue_scripts', [$adminController, 'enqueueScripts'], 10, 0);
        add_action('enqueue_block_editor_assets', [$adminController, 'enqueueEditorAssets'], 10, 0);
    }

    /**
     * Register all the hooks related to the public-facing functionality of the plugin.
     *
     * @return void
     */
    private function loadFrontendHooks(): void
    {
        $controller = new FrontendController();
        add_filter('single_template', [$controller, 'loadSingleTemplates'], 10, 3);
        add_filter('archive_template', [$controller, 'loadArchiveTemplates'], 10, 3);
        add_action('wp_enqueue_scripts', [$controller, 'enqueueStyles'], 10, 0);
    }

    /**
     * Define the locales for this plugin for internationalization.
     *
     * @return void
     */
    private function registerLocales(): void
    {
        load_plugin_textdomain(CCWP_PLUGIN_NAME, false, ccwp_plugin_path('languages'));
    }
}
