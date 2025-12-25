<?php

declare(strict_types=1);

namespace CurtainCall\Hooks;

use CurtainCall\CurtainCall;
use CurtainCall\Data\MemberData;
use CurtainCall\Data\ProductionData;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\Str;
use CurtainCall\Support\View;
use Illuminate\Support\Arr;
use Throwable;
use WP_Post;

final class AdminHooks
{
    protected string $assetsUrl;
    protected string $assetsPath;

    public function __construct()
    {
        $this->assetsUrl = ccwpPluginUrl('assets/admin/');
        $this->assetsPath = ccwpPluginPath('assets/admin/');
    }

    private function getPostType(): ?string
    {
        $screen = get_current_screen();
        $postType = $screen?->post_type;

        if (!$postType && isset($_GET['post_type'])) {
            $postType = sanitize_text_field($_GET['post_type']);
        }

        if (!$postType && isset($_GET['post'])) {
            $postType = get_post_type((int)$_GET['post']);
        }

        return $postType ?: null;
    }

    /**
     * Register the stylesheets for the admin area.
     * @return void
     */
    public function enqueueStyles(): void
    {
        $handle = CurtainCall::PLUGIN_NAME . '_admin_metaboxes';
        $src = $this->assetsUrl . 'curtain-call-wp-admin.css';
        $version = CCWP_DEBUG ? rand() : CurtainCall::PLUGIN_VERSION;

        wp_enqueue_style($handle, $src, [], $version);
    }

    /**
     * Register the JavaScript for the admin area.
     * @return void
     */
    public function enqueueScripts(): void
    {
        $postType = $this->getPostType();

        if ($postType === Production::POST_TYPE) {
            $post = get_post();

            $productionMetaboxesHandle = CurtainCall::PLUGIN_NAME . '_admin_production_metaboxes';
            wp_enqueue_script($productionMetaboxesHandle);

            try {
                /** @var Production|null $production */
                $production = $post ? Production::make($post) : null;
            } catch (Throwable) {
                $production = null;
            }

            if ($production) {
                $dateStart = getCustomField('_ccwp_production_date_start', $production->ID);
                $dateEnd = getCustomField('_ccwp_production_date_end', $production->ID);
                $productionDetails = [
                    'ID' => $production->ID,
                    'name' => getCustomField('_ccwp_production_name', $production->ID),
                    'date_start' => $dateStart ? Date::reformat($dateStart, 'Y-m-d') : '',
                    'date_end' => $dateEnd ? Date::reformat($dateEnd, 'Y-m-d') : '',
                    'show_times' => getCustomField('_ccwp_production_show_times', $production->ID),
                    'ticket_url' => getCustomField('_ccwp_production_ticket_url', $production->ID),
                    'venue' => getCustomField('_ccwp_production_venue', $production->ID),
                ];
            } else {
                $productionDetails = null;
            }

            try {
                /** @var array<int, array{label: string, value: string}> $options */
                $options = Arr::map(
                    $production?->getCastCrewNames() ?? [],
                    static fn(string $name, string|int $id) => ['label' => $name, 'value' => (string) $id]
                );
            } catch (Throwable) {
                /** @var array<int, array{label: string, value: string}> $options */
                $options = [];
            }

            try {
                /** @var array<int, MemberData> $members */
                $members = collect($production?->getCastAndCrew() ?? [])
                    ->map(fn(CastAndCrew $member) => MemberData::fromArray([
                        'ID' => $member->ID,
                        'firstName' => $member->name_first,
                        'lastName' => $member->name_last,
                        'name' => $member->getFullName(),
                        'order' => $member->ccwp_join?->custom_order ?? 0,
                        'role' => $member->ccwp_join?->role ?? '',
                        'type' => $member->ccwp_join?->type,
                    ]))
                    ->groupBy('type')
                    ->map(fn(array $members) => collect($members)->sortBy(['order', 'nameLast']))
                    ->toArray();
            } catch (Throwable) {
                /** @var array<int, array{ID: int, name_first: string, name_last: string, full_name: string, role: string|null, type: string, order: int}> $members */
                $members = [];
            }

            wp_localize_script($productionMetaboxesHandle, 'CCWP_DATA', [
                'initialDetails' => $productionDetails,
                'options' => $options,
                'cast' => $members['cast'] ?? [],
                'crew' => $members['crew'] ?? [],
            ]);
        } elseif ($postType === CastAndCrew::POST_TYPE) {
            $post = get_post();

            $castcrewMetaboxesHandle = CurtainCall::PLUGIN_NAME . '_admin_castcrew_metaboxes';
            wp_enqueue_script($castcrewMetaboxesHandle);

            try {
                /** @var CastAndCrew|null $castCrew */
                $castCrew = $post ? CastAndCrew::make($post) : null;
            } catch (Throwable) {
                $castCrew = null;
            }

            if ($castCrew) {
                $birthday = getCustomField('_ccwp_cast_crew_birthday', $castCrew->ID);
                $castCrewDetails = [
                    'ID' => $castCrew->ID,
                    'name_first' => getCustomField('_ccwp_cast_crew_name_first', $castCrew->ID),
                    'name_last' => getCustomField('_ccwp_cast_crew_name_last', $castCrew->ID),
                    'self_title' => getCustomField('_ccwp_cast_crew_self_title', $castCrew->ID),
                    'birthday' => $birthday ? Date::reformat($birthday, 'Y-m-d') : '',
                    'hometown' => getCustomField('_ccwp_cast_crew_hometown', $castCrew->ID),
                    'website_link' => getCustomField('_ccwp_cast_crew_website_link', $castCrew->ID),
                    'facebook_link' => getCustomField('_ccwp_cast_crew_facebook_link', $castCrew->ID),
                    'instagram_link' => getCustomField('_ccwp_cast_crew_instagram_link', $castCrew->ID),
                    'twitter_link' => getCustomField('_ccwp_cast_crew_twitter_link', $castCrew->ID),
                    'fun_fact' => getCustomField('_ccwp_cast_crew_fun_fact', $castCrew->ID),
                ];
            } else {
                $castCrewDetails = null;
            }

            try {
                /** @var array<int, array{label: string, value: string}> $options */
                $options = collect(Production::getPosts()->posts)
                    ->map(fn(WP_Post $post) => Production::make($post))
                    ->map(fn(Production $production) => ['label' => $production->name, 'value' => (string) $production->ID])
                    ->values();
            } catch (Throwable) {
                /** @var array<int, array{label: string, value: string}> $options */
                $options = [];
            }

            try {
                /** @var Production[] $productions */
                $productions  = collect($castCrew?->getProductions() ?? [])
                    ->map(fn(Production $production) => ProductionData::fromArray([
                        'ID' => $production->ID,
                        'dateEnd' => $production->date_end,
                        'dateStart' => $production->date_start,
                        'name' => $production->name,
                        'order' => $production->ccwp_join?->custom_order ?? 0,
                        'role' => $production->ccwp_join?->role,
                        'type' => $production->ccwp_join?->type,
                    ]))
                    ->toArray();
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
    }

    /**
     * Enqueue assets for the block editor (Gutenberg)
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        $postType = $this->getPostType();

        if (!$postType || ($postType !== Production::POST_TYPE && $postType !== CastAndCrew::POST_TYPE)) {
            return;
        }

        $sidebarHandle = CurtainCall::PLUGIN_NAME . '_editor_sidebar';

        wp_enqueue_script($sidebarHandle);

        // Provide REST base and nonce to the script
        wp_localize_script($sidebarHandle, 'CCWP_SETTINGS', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function registerJavascript(): void
    {
        $version = CCWP_DEBUG ? rand() : CurtainCall::PLUGIN_VERSION;

        $productionMetaboxesHandle = CurtainCall::PLUGIN_NAME . '_admin_production_metaboxes';
        $productionMetaboxesSrc = ccwpPluginUrl('assets/admin/curtain-call-wp-production-metaboxes.js');
        wp_register_script(
            $productionMetaboxesHandle,
            $productionMetaboxesSrc,
            [
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
            ],
        );

        $castcrewMetaboxesHandle = CurtainCall::PLUGIN_NAME . '_admin_castcrew_metaboxes';
        $castcrewMetaboxesSrc = ccwpPluginUrl('assets/admin/curtain-call-wp-castcrew-metaboxes.js');
        wp_register_script(
            $castcrewMetaboxesHandle,
            $castcrewMetaboxesSrc,
            [
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
            ],
        );

        $sidebarHandle = CurtainCall::PLUGIN_NAME . '_editor_sidebar';
        $sidebarSrc = ccwpPluginUrl('assets/admin/curtain-call-wp-sidebar.js');
        wp_register_script(
            $sidebarHandle,
            $sidebarSrc,
            [
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
            ],
        );
    }

    //  ----------------------------------------------------------------------------------------------------------------
    //  Other Functions
    //  ----------------------------------------------------------------------------------------------------------------

    /**
     * Set the post title based on the information provided in the metaboxes.
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

        $postType = $postArr['post_type'] ?? null;
        if ($postType !== Production::POST_TYPE && $postType !== CastAndCrew::POST_TYPE) {
            return $data;
        }

        switch ($postType) {
            case Production::POST_TYPE:
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

                $title = empty($titleParts)
                    ? "Untitled Curtain Call Production - {$postArr['ID']}"
                    : Str::stripExtraSpaces(implode(' ', $titleParts));
                break;
            case CastAndCrew::POST_TYPE:
                $titleParts = [];
                if (isset($postArr['ccwp_name_first'])) {
                    $titleParts[] = $postArr['ccwp_name_first'];
                }

                if (isset($postArr['ccwp_name_last'])) {
                    $titleParts[] = $postArr['ccwp_name_last'];
                }

                $title = empty($titleParts)
                    ? "Untitled Curtain Call Cast/Crew - {$postArr['ID']}"
                    : Str::stripExtraSpaces(implode(' ', $titleParts));
                break;
            default:
                $title = "Untitled Curtain Call Post - {$postArr['ID']}";
                break;
        }

        $data['post_name']  = sanitize_title($title);
        $data['post_title'] = $title;

        return $data;
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

    //  ----------------------------------------------------------------------------------------------------------------
    //  Production Functions
    //  ----------------------------------------------------------------------------------------------------------------

    /**
     * Add the production metaboxes
     *
     * @return void
     */
    public function addProductionPostMetaBoxes(): void
    {
        //$screen = get_current_screen();

        add_meta_box(
            'ccwp_production_details', // Unique ID
            __('Production Details', CCWP_TEXT_DOMAIN), // Box title
            [$this, 'renderProductionDetailsMetabox'], // Content callback
            Production::POST_TYPE, // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
        );

        //if ($screen && method_exists($screen, 'is_block_editor') && !$screen->is_block_editor()) {
            add_meta_box(
                'ccwp_add_cast_and_crew_to_production', // Unique ID
                __('Add Cast And Crew to Production', CCWP_TEXT_DOMAIN), // Box title
                [$this, 'renderAddCastAndCrewMetabox'], // Content callback
                Production::POST_TYPE, // Post type
                'normal', // Context: (normal, side, advanced)
                'high' // Priority: (high, low)
            );
        //}
    }

    /**
     * Render the Cast and Crew metabox
     *
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderAddCastAndCrewMetabox(WP_Post $post, array $metabox): void
    {
        $production = Production::make($post);

        // Get all castcrew by id and name
        $all_cast_crew_names = $production->getCastCrewNames();

        // Get all related cast and crew members to this production
        $production_cast_and_crew_members = $production->getCastAndCrew();
        $cast_members = [];
        $crew_members = [];

        // Sort them into cast then crew members
        if (!empty($production_cast_and_crew_members) && is_array($production_cast_and_crew_members)) {
            $cast_members = array_filter($production_cast_and_crew_members, function ($castcrew_member) {
                return ($castcrew_member->ccwp_join->type == 'cast');
            });
            $cast_members = array_values($cast_members);

            $crew_members = array_filter($production_cast_and_crew_members, function ($castcrew_member) {
                return ($castcrew_member->ccwp_join->type == 'crew');
            });
            $crew_members = array_values($crew_members);
        }

        View::make('admin/production-add-castcrew-metabox.php', [
            'wp_nonce' => wp_nonce_field(basename(__FILE__), 'ccwp_add_cast_and_crew_to_production_box_nonce', true, false),
            'post' => $post,
            'metabox' => $metabox,
            'all_cast_crew_names' => $all_cast_crew_names,
            'cast_members' => $cast_members,
            'crew_members' => $crew_members,
        ])->render();
    }

    /**
     * Render the Production metabox
     *
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderProductionDetailsMetabox(WP_Post $post, array $metabox): void
    {
        $dateStart = getCustomField('_ccwp_production_date_start', $post->ID);
        $dateStart = Date::reformat($dateStart, 'm/d/Y', '');
        $dateEnd = getCustomField('_ccwp_production_date_end', $post->ID);
        $dateEnd = Date::reformat($dateEnd, 'm/d/Y', '');

        View::make('admin/production-details-metabox.php', [
            'wp_nonce'   => wp_nonce_field(basename(__FILE__), 'ccwp_production_details_box_nonce', true, false),
            'post'       => $post,
            'metabox'    => $metabox,
            'name'       => getCustomField('_ccwp_production_name', $post->ID),
            'date_start' => $dateStart,
            'date_end'   => $dateEnd,
            'show_times' => getCustomField('_ccwp_production_show_times', $post->ID),
            'ticket_url' => getCustomField('_ccwp_production_ticket_url', $post->ID),
            'venue'      => getCustomField('_ccwp_production_venue', $post->ID),
        ])->render();
    }

    /**
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     * @return void
     * @throws Throwable
     */
    public function saveProductionPostCastAndCrew(int $postId, WP_Post $post, bool $update): void
    {
        # Verify meta box nonce
        if (! isset($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'])
        ||  ! wp_verify_nonce($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'], basename(__FILE__))
        ) {
            return;
        }

        # Return if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        # Check the user's permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        # Store custom fields values
        $production_cast = ! empty($_POST['ccwp_add_cast_to_production']) ? $_POST['ccwp_add_cast_to_production'] : [];
        $production_crew = ! empty($_POST['ccwp_add_crew_to_production']) ? $_POST['ccwp_add_crew_to_production'] : [];

        $production = Production::make($post);
        $production->saveCastAndCrew('cast', $production_cast);
        $production->saveCastAndCrew('crew', $production_crew);
    }

    /**
     * Save the Production Details metabox fields
     *
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     * @return void
     */
    public function saveProductionPostDetails(int $postId, WP_Post $post, bool $update): void
    {
        if ( # Verify meta box nonce
            !isset($_POST['ccwp_production_details_box_nonce'])
        ||  !wp_verify_nonce($_POST['ccwp_production_details_box_nonce'], basename(__FILE__))
        ) {
            return;
        }

        // Don't auto save these fields
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Store custom field values

        if (!empty($_POST['ccwp_production_name'])) {
            update_post_meta($postId, '_ccwp_production_name', sanitize_text_field($_POST['ccwp_production_name']));
        } else {
            delete_post_meta($postId, '_ccwp_production_name');
        }

        if (!empty($_POST['ccwp_date_start'])) {
            $ccwp_date_start = sanitize_text_field($_POST['ccwp_date_start']);
            $ccwp_date_start = Date::reformat($ccwp_date_start, 'Y-m-d');
            update_post_meta($postId, '_ccwp_production_date_start', $ccwp_date_start);
        } else {
            delete_post_meta($postId, '_ccwp_production_date_start');
        }

        if (!empty($_POST['ccwp_date_end'])) {
            $ccwp_date_end = sanitize_text_field($_POST['ccwp_date_end']);
            $ccwp_date_end = Date::reformat($ccwp_date_end, 'Y-m-d');
            update_post_meta($postId, '_ccwp_production_date_end', $ccwp_date_end);
        } else {
            delete_post_meta($postId, '_ccwp_production_date_end');
        }

        if (!empty($_POST['ccwp_show_times'])) {
            update_post_meta($postId, '_ccwp_production_show_times', sanitize_text_field($_POST['ccwp_show_times']));
        } else {
            delete_post_meta($postId, '_ccwp_production_show_times');
        }

        if (!empty($_POST['ccwp_ticket_url'])) {
            update_post_meta(
                $postId,
                '_ccwp_production_ticket_url',
                esc_url_raw($_POST['ccwp_ticket_url'], ['http', 'https'])
            );
        } else {
            delete_post_meta($postId, '_ccwp_production_ticket_url');
        }

        if (!empty($_POST['ccwp_venue'])) {
            update_post_meta($postId, '_ccwp_production_venue', sanitize_text_field($_POST['ccwp_venue']));
        } else {
            delete_post_meta($postId, '_ccwp_production_venue');
        }
    }

    //----------------------------------------------------------------------------------------------------------------
    //  Cast And Crew Functions
    //----------------------------------------------------------------------------------------------------------------

    /**
     * Add the Cast and crew met boxes
     *
     * @return void
     */
    public function addCastAndCrewPostMetaBoxes(): void
    {
        add_meta_box(
            'ccwp_cast_and_cast_details', // Unique ID
            __('Cast and Crew Details', CCWP_TEXT_DOMAIN), // Box title
            [$this, 'renderCastAndCrewDetailsMetabox'], // Content callback
            CastAndCrew::POST_TYPE, // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
        );
    }

    /**
     * @param WP_Post $post
     * @param array metabox
     * @return void
     * @throws Throwable
     */
    public function renderCastAndCrewDetailsMetabox(WP_Post $post, array $metabox): void
    {
        $birthday = getCustomField('_ccwp_cast_crew_birthday', $post->ID);
        $birthday = Date::reformat($birthday, 'm/d/Y', '');

        View::make('admin/castcrew-details-metabox.php', [
            'wp_nonce' => wp_nonce_field(basename(__FILE__), 'ccwp_cast_and_crew_details_box_nonce', true, false),
            'post' => $post,
            'metabox' => $metabox,
            'name_first' => getCustomField('_ccwp_cast_crew_name_first', $post->ID),
            'name_last' => getCustomField('_ccwp_cast_crew_name_last', $post->ID),
            'self_title' => getCustomField('_ccwp_cast_crew_self_title', $post->ID),
            'birthday' => $birthday,
            'hometown' => getCustomField('_ccwp_cast_crew_hometown', $post->ID),
            'website_link' => getCustomField('_ccwp_cast_crew_website_link', $post->ID),
            'facebook_link' => getCustomField('_ccwp_cast_crew_facebook_link', $post->ID),
            'instagram_link' => getCustomField('_ccwp_cast_crew_instagram_link', $post->ID),
            'twitter_link' => getCustomField('_ccwp_cast_crew_twitter_link', $post->ID),
            'fun_fact' => getCustomField('_ccwp_cast_crew_fun_fact', $post->ID),
        ])->render();
    }

    /**
     * Save the cast and crew metabox fields
     *
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     * @return void
     */
    public function saveCastAndCrewPostDetails(int $postId, WP_Post $post, bool $update): void
    {
        if ( # Verify meta box nonce
            !isset($_POST['ccwp_cast_and_crew_details_box_nonce'])
        ||  !wp_verify_nonce($_POST['ccwp_cast_and_crew_details_box_nonce'], basename(__FILE__))
        ) {
            return;
        }

        // Don't auto save these fields
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Store custom field values

        if (!empty($_POST['ccwp_name_first'])) {
            update_post_meta($postId, '_ccwp_cast_crew_name_first', sanitize_text_field($_POST['ccwp_name_first']));
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_name_first');
        }

        if (!empty($_POST['ccwp_name_last'])) {
            update_post_meta($postId, '_ccwp_cast_crew_name_last', sanitize_text_field($_POST['ccwp_name_last']));
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_name_last');
        }

        if (!empty($_POST['ccwp_self_title'])) {
            update_post_meta($postId, '_ccwp_cast_crew_self_title', sanitize_text_field($_POST['ccwp_self_title']));
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_self_title');
        }

        if (!empty($_POST['ccwp_birthday'])) {
            $ccwp_birthday = sanitize_text_field($_POST['ccwp_birthday']);
            $ccwp_birthday = Date::reformat($ccwp_birthday, 'Y-m-d');
            update_post_meta($postId, '_ccwp_cast_crew_birthday', $ccwp_birthday);
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_birthday');
        }

        if (!empty($_POST['ccwp_hometown'])) {
            update_post_meta($postId, '_ccwp_cast_crew_hometown', sanitize_text_field($_POST['ccwp_hometown']));
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_hometown');
        }

        if (!empty($_POST['ccwp_website_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_website_link',
                esc_url_raw($_POST['ccwp_website_link'], ['http', 'https'])
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_website_link');
        }

        if (!empty($_POST['ccwp_facebook_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_facebook_link',
                esc_url_raw($_POST['ccwp_facebook_link'], ['http', 'https'])
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_facebook_link');
        }

        if (!empty($_POST['ccwp_twitter_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_twitter_link',
                esc_url_raw($_POST['ccwp_twitter_link'], ['http', 'https'])
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_twitter_link');
        }

        if (!empty($_POST['ccwp_instagram_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_instagram_link',
                esc_url_raw($_POST['ccwp_instagram_link'], ['http', 'https'])
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_instagram_link');
        }

        if (!empty($_POST['ccwp_fun_fact'])) {
            update_post_meta($postId, '_ccwp_cast_crew_fun_fact', sanitize_text_field($_POST['ccwp_fun_fact']));
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_fun_fact');
        }
    }
}
