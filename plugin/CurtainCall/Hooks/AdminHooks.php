<?php

declare(strict_types=1);

namespace CurtainCall\Hooks;

use CurtainCall\Data\ProductionData;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\Str;
use CurtainCall\Support\View;
use Illuminate\Support\Collection;
use Throwable;
use WP_Post;

final class AdminHooks
{
    private string $assetsUrl;
    private string $assetsPath;

    public function __construct()
    {
        $this->assetsUrl = ccwp_plugin_url('assets/admin/');
        $this->assetsPath = ccwp_plugin_path('assets/admin/');
    }

    private function getPostType(): ?string
    {
        $screen = get_current_screen();
        $postType = $screen?->post_type;

        if (!$postType && isset($_GET['post_type'])) {
            $postType = sanitize_text_field($_GET['post_type']);
        }

        if (!$postType && isset($_GET['post'])) {
            $postType = get_post_type((int) $_GET['post']);
        }

        return $postType ?: null;
    }

    /**
     * Register the stylesheets for the admin area.
     * @return void
     */
    public function enqueueStyles(): void
    {
        $handle = CCWP_PLUGIN_NAME . '_admin_metaboxes';
        $src = $this->assetsUrl . 'curtain-call-wp-admin.css';
        $version = defined('CCWP_DEBUG') && CCWP_DEBUG === true
            ? rand()
            : CCWP_PLUGIN_VERSION;

        wp_enqueue_style($handle, $src, [], $version);
    }

    /**
     * Register the JavaScript for the admin area.
     * @return void
     */
    public function enqueueScripts(): void
    {
        $postType = $this->getPostType();

        switch ($postType) {
            case CastAndCrew::POST_TYPE:
                $this->enqueueCastCrewPostScripts();
                break;
            case Production::POST_TYPE:
                $this->enqueueProductionPostScripts();
                break;
        }
    }

    /**
     * Enqueue assets for the block editor (Gutenberg)
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        $postType = $this->getPostType();

        if (!$postType || $postType !== Production::POST_TYPE && $postType !== CastAndCrew::POST_TYPE) {
            return;
        }

        $sidebarHandle = CCWP_PLUGIN_NAME . '_editor_sidebar';

        wp_enqueue_script($sidebarHandle);

        // Provide REST base and nonce to the script
        wp_localize_script($sidebarHandle, 'CCWP_SETTINGS', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function registerJavascript(): void
    {
        $version = defined('CCWP_DEBUG') && CCWP_DEBUG === true
            ? rand()
            : CCWP_PLUGIN_VERSION;

        $productionMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_production_metaboxes';
        $productionMetaboxesSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-production-metaboxes.js');
        wp_register_script($productionMetaboxesHandle, $productionMetaboxesSrc, [
            'react',
            'react-dom',
            'wp-api-fetch',
            'wp-components',
            'wp-data',
            'wp-edit-post',
            'wp-editor',
            'wp-element',
            'wp-i18n',
            'wp-plugins',
        ], $version);

        $castcrewMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_castcrew_metaboxes';
        $castcrewMetaboxesSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-castcrew-metaboxes.js');
        wp_register_script($castcrewMetaboxesHandle, $castcrewMetaboxesSrc, [
            'react',
            'react-dom',
            'wp-api-fetch',
            'wp-components',
            'wp-data',
            'wp-edit-post',
            'wp-editor',
            'wp-element',
            'wp-i18n',
            'wp-plugins',
        ], $version);

        $sidebarHandle = CCWP_PLUGIN_NAME . '_editor_sidebar';
        $sidebarSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-sidebar.js');
        wp_register_script($sidebarHandle, $sidebarSrc, [
            'react',
            'react-dom',
            'wp-api-fetch',
            'wp-components',
            'wp-data',
            'wp-edit-post',
            'wp-editor',
            'wp-element',
            'wp-i18n',
            'wp-plugins',
        ], $version);
    }

    /**
     * @return void
     */
    public function addPluginSettingsPage(): void
    {
        add_submenu_page(
            'options-general.php',
            'Curtain Call WP',
            __('Curtain Call WP', CCWP_TEXT_DOMAIN),
            'manage_options',
            'ccwp-settings',
            [$this, 'renderPluginSettingsPage'],
        );
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function renderPluginSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        View::make('admin/settings-page.php')->render();
    }

    /**
     * Set the post's title based on the information provided in the metaboxes.
     *
     * NOTE: This filter fires on every post save regardless of what type it is. It
     *       is extremely important that we return as early as possible if the data doesn't
     *       belong to a ccwp post.
     *
     * @param array $data
     * @param array $postArr
     * @return array
     */
    public function setTitleOnPostSave(array $data, array $postArr): array
    {
        if (!isset($postArr['action']) || $postArr['action'] !== 'editpost') {
            return $data;
        }

        $postType = $postArr['post_type'] ?? $this->getPostType() ?? null;

        switch ($postType) {
            case CastAndCrew::POST_TYPE:
                $title = $this->getCastCrewPostTitle($postArr);
                $data['post_name'] = sanitize_title($title);
                $data['post_title'] = $title;
                break;
            case Production::POST_TYPE:
                $title = $this->getProductionPostTitle($postArr);
                $data['post_name'] = sanitize_title($title);
                $data['post_title'] = $title;
                break;
        }

        return $data;
    }

    /**
     * Enqueue scripts and data for the cast/crew metaboxes.
     *
     * @return void
     */
    private function enqueueCastCrewPostScripts(): void
    {
        $post = get_post();

        $castcrewMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_castcrew_metaboxes';
        wp_enqueue_script($castcrewMetaboxesHandle);

        try {
            /** @var CastAndCrew|null $castCrew */
            $castCrew = $post ? CastAndCrew::make($post) : null;
        } catch (Throwable) {
            $castCrew = null;
        }

        if ($castCrew) {
            $birthday = ccwp_get_custom_field('_ccwp_cast_crew_birthday', $castCrew->ID);
            $castCrewDetails = [
                'ID' => $castCrew->ID,
                'name_first' => ccwp_get_custom_field('_ccwp_cast_crew_name_first', $castCrew->ID),
                'name_last' => ccwp_get_custom_field('_ccwp_cast_crew_name_last', $castCrew->ID),
                'self_title' => ccwp_get_custom_field('_ccwp_cast_crew_self_title', $castCrew->ID),
                'birthday' => $birthday ? Date::reformat($birthday, 'Y-m-d') : '',
                'hometown' => ccwp_get_custom_field('_ccwp_cast_crew_hometown', $castCrew->ID),
                'website_link' => ccwp_get_custom_field('_ccwp_cast_crew_website_link', $castCrew->ID),
                'facebook_link' => ccwp_get_custom_field('_ccwp_cast_crew_facebook_link', $castCrew->ID),
                'instagram_link' => ccwp_get_custom_field('_ccwp_cast_crew_instagram_link', $castCrew->ID),
                'twitter_link' => ccwp_get_custom_field('_ccwp_cast_crew_twitter_link', $castCrew->ID),
                'fun_fact' => ccwp_get_custom_field('_ccwp_cast_crew_fun_fact', $castCrew->ID),
            ];
        } else {
            $castCrewDetails = null;
        }

        try {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = collect(Production::getPosts()->posts)
                ->map(static fn(WP_Post $post) => Production::make($post))
                ->map(static fn(Production $production) => [
                    'label' => $production->name,
                    'value' => (string) $production->ID
                ])
                ->values()
                ->all();
        } catch (Throwable) {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = [];
        }

        try {
            /** @var Production[] $productions */
            $productions = collect(
                $castCrew?->getProductions() ?? [],
            )->map(static fn(Production $production) => ProductionData::fromArray([
                'ID' => $production->ID,
                'dateEnd' => $production->date_end,
                'dateStart' => $production->date_start,
                'name' => $production->name,
                'order' => $production->ccwp_join->custom_order ?? 0,
                'role' => $production->ccwp_join?->role,
                'type' => $production->ccwp_join?->type,
            ]))->toArray();
        } catch (Throwable) {
            /** @var Production[] $productions */
            $productions = [];
        }

        wp_localize_script($castcrewMetaboxesHandle, 'CCWP_DATA', [
            'initialDetails' => $castCrewDetails,
            'options' => $options,
            'productions' => $productions,
        ]);
    }

    /**
     * Enqueue scripts and data for the production metaboxes.
     *
     * @return void
     */
    private function enqueueProductionPostScripts(): void
    {
        $post = get_post();

        $productionMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_production_metaboxes';
        wp_enqueue_script($productionMetaboxesHandle);

        try {
            /** @var Production|null $production */
            $production = $post ? Production::make($post) : null;
        } catch (Throwable) {
            $production = null;
        }

        if ($production) {
            $dateStart = ccwp_get_custom_field('_ccwp_production_date_start', $production->ID);
            $dateEnd = ccwp_get_custom_field('_ccwp_production_date_end', $production->ID);
            $productionDetails = [
                'ID' => $production->ID,
                'name' => ccwp_get_custom_field('_ccwp_production_name', $production->ID),
                'date_start' => $dateStart ? Date::reformat($dateStart, 'Y-m-d') : '',
                'date_end' => $dateEnd ? Date::reformat($dateEnd, 'Y-m-d') : '',
                'show_times' => ccwp_get_custom_field('_ccwp_production_show_times', $production->ID),
                'ticket_url' => ccwp_get_custom_field('_ccwp_production_ticket_url', $production->ID),
                'venue' => ccwp_get_custom_field('_ccwp_production_venue', $production->ID),
            ];
        } else {
            $productionDetails = null;
        }

        try {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = collect($production?->getCastCrewNames() ?? [])
                ->map(static fn(string $name, string|int $id) => ['label' => $name, 'value' => (string) $id])
                ->values()
                ->all();
        } catch (Throwable) {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = [];
        }

        try {
            $members = collect($production?->getCastAndCrew() ?? [])
                ->groupBy('ccwp_join.type')
                ->map(static fn(Collection $members) => $members->sortBy(['ccwp_join.custom_order', 'name_last']))
                ->all();
        } catch (Throwable $e) {
            dump($e);
            $members = [];
        }

        wp_localize_script($productionMetaboxesHandle, 'CCWP_DATA', [
            'initialDetails' => $productionDetails,
            'options' => $options,
            'cast' => $members['cast']?->all() ?? [],
            'crew' => $members['crew']?->all() ?? [],
        ]);
    }

    /**
     * Generate a post title for a production post.
     *
     * @param array<string, mixed> $postArr
     * @return string
     */
    private function getProductionPostTitle(array $postArr): string
    {
        $titleParts = [];
        if (isset($postArr['ccwp_production_name'])) {
            $titleParts[] = $postArr['ccwp_production_name'];
            if (isset($postArr['ccwp_date_start'])) {
                $dateStart = Date::toCarbon($postArr['ccwp_date_start']);
                if ($dateStart) {
                    $titleParts[] = '-';
                    $titleParts[] = $dateStart->format('Y');
                }
            }
        }

        return empty($titleParts)
            ? "Untitled Curtain Call Production - {$postArr['ID']}"
            : Str::stripExtraSpaces(implode(' ', $titleParts));
    }

    /**
     * Generate a post title for a cast/crew post.
     *
     * @param array<string, mixed> $postArr
     * @return string
     */
    private function getCastCrewPostTitle(array $postArr): string
    {
        $titleParts = [];
        if (isset($postArr['ccwp_name_first'])) {
            $titleParts[] = $postArr['ccwp_name_first'];
        }

        if (isset($postArr['ccwp_name_last'])) {
            $titleParts[] = $postArr['ccwp_name_last'];
        }

        return empty($titleParts)
            ? "Untitled Curtain Call Cast/Crew - {$postArr['ID']}"
            : Str::stripExtraSpaces(implode(' ', $titleParts));
    }
}
