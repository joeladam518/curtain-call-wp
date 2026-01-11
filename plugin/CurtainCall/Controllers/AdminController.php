<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Data\CastCrewData;
use CurtainCall\Data\ProductionData;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\Str;
use Illuminate\Support\Collection;
use Throwable;
use WP_Post;

final class AdminController
{
    /**
     * Register the stylesheets for the admin area.
     * @return void
     */
    public function enqueueStyles(): void
    {
        $handle = CCWP_PLUGIN_NAME . '_admin_metaboxes';
        $src = ccwp_plugin_url('assets/admin/curtain-call-wp-admin.css');
        $version = $this->getVersion();

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

        $version = $this->getVersion();
        $sidebarHandle = CCWP_PLUGIN_NAME . '_editor_sidebar';
        $sidebarSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-sidebar.js');
        wp_enqueue_script(
            $sidebarHandle,
            $sidebarSrc,
            [
                'react',
                'react-dom',
                'wp-api-fetch',
                'wp-blocks',
                'wp-components',
                'wp-core-data',
                'wp-data',
                'wp-data-controls',
                'wp-dom-ready',
                'wp-edit-post',
                'wp-editor',
                'wp-element',
                'wp-hooks',
                'wp-i18n',
                'wp-plugins',
                'wp-primitives',
                'wp-rich-text',
            ],
            $version,
        );
        wp_set_script_translations($sidebarHandle, CCWP_TEXT_DOMAIN);

        // Provide REST base and nonce to the script
        wp_localize_script($sidebarHandle, 'CCWP_SETTINGS', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
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

        $version = $this->getVersion();
        $castcrewMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_castcrew_metaboxes';
        $castcrewMetaboxesSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-castcrew-metaboxes.js');
        wp_enqueue_script(
            $castcrewMetaboxesHandle,
            $castcrewMetaboxesSrc,
            [
                'react',
                'react-dom',
                'wp-api-fetch',
                'wp-blocks',
                'wp-components',
                'wp-core-data',
                'wp-data',
                'wp-data-controls',
                'wp-dom-ready',
                'wp-edit-post',
                'wp-editor',
                'wp-element',
                'wp-hooks',
                'wp-i18n',
                'wp-plugins',
                'wp-primitives',
                'wp-rich-text',
            ],
            $version,
        );
        wp_set_script_translations($castcrewMetaboxesHandle, CCWP_TEXT_DOMAIN);

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
                    'value' => (string) $production->ID,
                ])
                ->sortBy('label')
                ->values()
                ->all();
        } catch (Throwable) {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = [];
        }

        try {
            /** @var list<array<string, mixed>> $productions */
            $productions = collect($castCrew?->getProductions() ?? [])
                ->map(static fn(Production $production) => ProductionData::fromProduction($production))
                ->values()
                ->toArray();
        } catch (Throwable $e) {
            /** @var list<array<string, mixed>> $productions */
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
        $version = $this->getVersion();
        $productionMetaboxesHandle = CCWP_PLUGIN_NAME . '_admin_production_metaboxes';
        $productionMetaboxesSrc = ccwp_plugin_url('assets/admin/curtain-call-wp-production-metaboxes.js');
        wp_enqueue_script(
            $productionMetaboxesHandle,
            $productionMetaboxesSrc,
            [
                'react',
                'react-dom',
                'wp-api-fetch',
                'wp-blocks',
                'wp-components',
                'wp-core-data',
                'wp-data',
                'wp-data-controls',
                'wp-dom-ready',
                'wp-edit-post',
                'wp-editor',
                'wp-element',
                'wp-hooks',
                'wp-i18n',
                'wp-plugins',
                'wp-primitives',
                'wp-rich-text',
            ],
            $version,
        );
        wp_set_script_translations($productionMetaboxesHandle, CCWP_TEXT_DOMAIN);

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
                ->sortBy('label')
                ->values()
                ->all();
        } catch (Throwable) {
            /** @var array<int, array{label: string, value: string}> $options */
            $options = [];
        }

        try {
            /** @var array<string, array<string, mixed>> $members */
            $members = collect($production?->getCastAndCrew() ?? [])
                ->map(static fn(CastAndCrew $member) => CastCrewData::fromCastCrew($member))
                ->groupBy('type')
                ->map(static fn(Collection $group) => $group->values()->toArray())
                ->toArray();
        } catch (Throwable $exception) {
            /** @var array<string, array<string, mixed>> $members */
            $members = [];
        }

        wp_localize_script($productionMetaboxesHandle, 'CCWP_DATA', [
            'initialDetails' => $productionDetails,
            'options' => $options,
            'cast' => $members['cast'] ?? [],
            'crew' => $members['crew'] ?? [],
        ]);
    }

    /**
     * Generate a post-title for a cast/crew post.
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

    /**
     * Generate a post-title for a production post.
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
     * Get the post-type from the current screen or post.
     *
     * @return string|null
     */
    private function getPostType(): ?string
    {
        $screen = get_current_screen();

        if ($screen?->post_type) {
            return $screen?->post_type;
        }

        /** @var WP_Post|array{post_type: string|null}|null $post */
        $post = get_post();

        if ($post instanceof WP_Post && isset($post->post_type)) {
            return $post->post_type;
        }

        if (is_array($post) && isset($post['post_type'])) {
            return $post['post_type'];
        }

        if (isset($_GET['post_type'])) {
            return sanitize_text_field($_GET['post_type']);
        }

        if (isset($_GET['post'])) {
            return get_post_type((int) $_GET['post']);
        }

        return null;
    }

    /**
     * Get the version to use for enqueued assets. Debug mode will use a random version to force reloads.
     *
     * @return string
     */
    private function getVersion(): string
    {
        return defined('CCWP_DEBUG') && CCWP_DEBUG === true ? (string) rand() : CCWP_PLUGIN_VERSION;
    }
}
