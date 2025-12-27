<?php

declare(strict_types=1);

namespace CurtainCall;

use CurtainCall\Blocks\ArchiveBlocks;
use CurtainCall\Hooks\AdminHooks;
use CurtainCall\Hooks\FrontendHooks;
use CurtainCall\Hooks\GlobalHooks;
use CurtainCall\LifeCycle\Activator;
use CurtainCall\LifeCycle\Deactivator;
use CurtainCall\LifeCycle\Uninstaller;
use CurtainCall\Rest\RelationsController;

final class CurtainCall
{
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
        $this->loadGlobalHooks();
        $this->loadAdminHooks();
        $this->loadFrontendHooks();
    }

    /**
     * Register all hook related to both the admin and frontend functionality
     *
     * @return void
     */
    private function loadGlobalHooks(): void
    {
        $controller = new GlobalHooks();

        add_action('admin_init', [$controller, 'addPluginSettings'], 10, 0);
        add_action('init', [$controller, 'createProductionPostType'], 10, 0);
        add_action('init', [$controller, 'createProductionSeasonsTaxonomy'], 10, 0);
        add_action('init', [$controller, 'createCastAndCrewPostType'], 10, 0);
        add_action('init', [$controller, 'registerPostMeta'], 10, 0);

        add_action('init', [ArchiveBlocks::class, 'register'], 10, 0);
        add_action('rest_api_init', [RelationsController::class, 'registerRoutes'], 10, 0);
    }

    /**
     * Register all the hooks related to the admin area functionality of the plugin.
     *
     * @return void
     */
    private function loadAdminHooks(): void
    {
        $controller = new AdminHooks();

        add_action('init', [$controller, 'registerJavascript'], 10, 0);
        add_action('admin_menu', [$controller, 'addPluginSettingsPage'], 10, 0);

        // Production custom post-type meta-boxes
        add_action('add_meta_boxes', [$controller, 'addProductionPostMetaBoxes'], 10, 0);
        add_action('save_post_ccwp_production', [$controller, 'saveProductionPostDetails'], 10, 3);
        add_action('save_post_ccwp_production', [$controller, 'saveProductionPostCastAndCrew'], 10, 3);

        // CastCrew custom post-type meta-boxes
        add_action('add_meta_boxes', [$controller, 'addCastAndCrewPostMetaBoxes'], 10, 0);
        add_action('save_post_ccwp_cast_and_crew', [$controller, 'saveCastAndCrewPostDetails'], 10, 3);

        // Set the title on save
        add_filter('wp_insert_post_data', [$controller, 'setTitleOnPostSave'], 10, 3);

        // Enqueue scripts and styles for the admin
        add_action('admin_enqueue_scripts', [$controller, 'enqueueStyles'], 10, 0);
        add_action('admin_enqueue_scripts', [$controller, 'enqueueScripts'], 10, 0);

        // Block editor-only assets
        add_action('enqueue_block_editor_assets', [$controller, 'enqueueEditorAssets'], 10, 0);
    }

    /**
     * Register all the hooks related to the public-facing functionality of the plugin.
     *
     * @return void
     */
    private function loadFrontendHooks(): void
    {
        $controller = new FrontendHooks();

        add_filter('single_template', [$controller, 'loadSingleTemplates'], 10, 3);
        add_filter('archive_template', [$controller, 'loadArchiveTemplates'], 10, 3);

        // Enqueue scripts and styles for the frontend
        add_action('wp_enqueue_scripts', [$controller, 'enqueueStyles'], 10, 0);
    }

    /**
     * Define the locales for this plugin for internationalization.
     *
     * @return void
     */
    private function registerLocales(): void
    {
        load_plugin_textdomain(CCWP_PLUGIN_NAME, false, CCWP_PLUGIN_PATH . 'languages/');
    }
}
