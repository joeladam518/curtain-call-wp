<?php

declare(strict_types=1);

namespace CurtainCall\Hooks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Date;
use CurtainCall\Support\View;
use Throwable;
use WP_Post;

final class AdminCastCrewMetaboxHooks
{
    /**
     * Add the Cast and crew met boxes
     *
     * @return void
     */
    public function addMetaboxes(): void
    {
        add_meta_box(
            'ccwp_cast_and_cast_details', // Unique ID
            __('Cast and Crew Details', CCWP_TEXT_DOMAIN), // Box title
            [$this, 'renderDetailsMetabox'], // Content callback
            CastAndCrew::POST_TYPE, // Post type
            'normal', // Context: (normal, side, advanced)
            'high', // Priority: (high, low)
        );

        // Only show the productions metabox in classic editor
        $screen = get_current_screen();
        if ($screen && method_exists($screen, 'is_block_editor') && !$screen->is_block_editor()) {
            add_meta_box(
                'ccwp_add_productions_to_cast_crew', // Unique ID
                __('Add Productions', CCWP_TEXT_DOMAIN), // Box title
                [$this, 'renderAddProductionsMetabox'], // Content callback
                CastAndCrew::POST_TYPE, // Post type
                'normal', // Context: (normal, side, advanced)
                'high', // Priority: (high, low)
            );
        }
    }

    /**
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderDetailsMetabox(WP_Post $post, array $metabox): void
    {
        View::make('admin/castcrew-details-metabox.php', [
            'wp_nonce' => wp_nonce_field(basename(__FILE__), 'ccwp_cast_and_crew_details_box_nonce', true, false),
            'post' => $post,
            'metabox' => $metabox,
            'name_first' => ccwp_get_custom_field('_ccwp_cast_crew_name_first', $post->ID),
            'name_last' => ccwp_get_custom_field('_ccwp_cast_crew_name_last', $post->ID),
            'self_title' => ccwp_get_custom_field('_ccwp_cast_crew_self_title', $post->ID),
            'birthday' => ccwp_get_custom_field('_ccwp_cast_crew_birthday', $post->ID),
            'hometown' => ccwp_get_custom_field('_ccwp_cast_crew_hometown', $post->ID),
            'website_link' => ccwp_get_custom_field('_ccwp_cast_crew_website_link', $post->ID),
            'facebook_link' => ccwp_get_custom_field('_ccwp_cast_crew_facebook_link', $post->ID),
            'instagram_link' => ccwp_get_custom_field('_ccwp_cast_crew_instagram_link', $post->ID),
            'twitter_link' => ccwp_get_custom_field('_ccwp_cast_crew_twitter_link', $post->ID),
            'fun_fact' => ccwp_get_custom_field('_ccwp_cast_crew_fun_fact', $post->ID),
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
    public function saveDetailsMeta(int $postId, WP_Post $post, bool $update): void
    {
        // Verify meta box nonce
        if (
            !isset($_POST['ccwp_cast_and_crew_details_box_nonce'])
            || !wp_verify_nonce($_POST['ccwp_cast_and_crew_details_box_nonce'], basename(__FILE__))
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
                esc_url_raw($_POST['ccwp_website_link'], ['http', 'https']),
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_website_link');
        }

        if (!empty($_POST['ccwp_facebook_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_facebook_link',
                esc_url_raw($_POST['ccwp_facebook_link'], ['http', 'https']),
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_facebook_link');
        }

        if (!empty($_POST['ccwp_twitter_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_twitter_link',
                esc_url_raw($_POST['ccwp_twitter_link'], ['http', 'https']),
            );
        } else {
            delete_post_meta($postId, '_ccwp_cast_crew_twitter_link');
        }

        if (!empty($_POST['ccwp_instagram_link'])) {
            update_post_meta(
                $postId,
                '_ccwp_cast_crew_instagram_link',
                esc_url_raw($_POST['ccwp_instagram_link'], ['http', 'https']),
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

    /**
     * Render the add productions metabox
     *
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderAddProductionsMetabox(WP_Post $post, array $metabox): void
    {
        $castcrew = CastAndCrew::make($post);

        View::make('admin/castcrew-productions-metabox.php', [
            'wp_nonce' => wp_nonce_field(basename(__FILE__), 'ccwp_add_productions_to_cast_crew_box_nonce', true, false),
            'post' => $post,
            'metabox' => $metabox,
            'castcrew' => $castcrew,
        ])->render();
    }

    /**
     * Save the add productions metabox
     *
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     * @return void
     * @throws Throwable
     */
    public function saveAddProductions(int $postId, WP_Post $post, bool $update): void
    {
        // Verify meta box nonce
        if (
            !isset($_POST['ccwp_add_productions_to_cast_crew_box_nonce'])
            || !wp_verify_nonce($_POST['ccwp_add_productions_to_cast_crew_box_nonce'], basename(__FILE__))
        ) {
            return;
        }

        // Don't auto save these fields
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        $castcrew = CastAndCrew::make($post);

        $castData = $_POST['ccwp_add_productions_to_cast'] ?? [];
        $crewData = $_POST['ccwp_add_productions_to_crew'] ?? [];

        $castcrew->saveProductions('cast', $castData);
        $castcrew->saveProductions('crew', $crewData);
    }
}
