<?php

declare(strict_types=1);

namespace CurtainCall\Hooks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\Date;
use CurtainCall\Support\View;
use Throwable;
use WP_Post;

final class AdminProductionMetaboxHooks
{
    /**
     * Add the production metaboxes
     *
     * @return void
     */
    public function addMetaboxes(): void
    {
        //$screen = get_current_screen();

        add_meta_box(
            'ccwp_production_details', // Unique ID
            __('Production Details', CCWP_TEXT_DOMAIN), // Box title
            [$this, 'renderDetailsMetabox'], // Content callback
            Production::POST_TYPE, // Post type
            'normal', // Context: (normal, side, advanced)
            'high', // Priority: (high, low)
        );

        //if ($screen && method_exists($screen, 'is_block_editor') && !$screen->is_block_editor()) {
        add_meta_box(
            'ccwp_add_cast_and_crew_to_production', // Unique ID
            __('Add Cast And Crew to Production', CCWP_TEXT_DOMAIN), // Box title
            [$this, 'renderAddCastCrewMetabox'], // Content callback
            Production::POST_TYPE, // Post type
            'normal', // Context: (normal, side, advanced)
            'high', // Priority: (high, low)
        );
        //}
    }

    /**
     * Render the Production metabox
     *
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderDetailsMetabox(WP_Post $post, array $metabox): void
    {
        $dateStart = ccwp_get_custom_field('_ccwp_production_date_start', $post->ID);
        $dateStart = Date::reformat($dateStart, 'm/d/Y', '');
        $dateEnd = ccwp_get_custom_field('_ccwp_production_date_end', $post->ID);
        $dateEnd = Date::reformat($dateEnd, 'm/d/Y', '');

        View::make('admin/production-details-metabox.php', [
            'wp_nonce' => wp_nonce_field(basename(__FILE__), 'ccwp_production_details_box_nonce', true, false),
            'post' => $post,
            'metabox' => $metabox,
            'name' => ccwp_get_custom_field('_ccwp_production_name', $post->ID),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'show_times' => ccwp_get_custom_field('_ccwp_production_show_times', $post->ID),
            'ticket_url' => ccwp_get_custom_field('_ccwp_production_ticket_url', $post->ID),
            'venue' => ccwp_get_custom_field('_ccwp_production_venue', $post->ID),
        ])->render();
    }

    /**
     * Save the Production Details metabox fields
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
            !isset($_POST['ccwp_production_details_box_nonce'])
            || !wp_verify_nonce($_POST['ccwp_production_details_box_nonce'], basename(__FILE__))
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
                esc_url_raw($_POST['ccwp_ticket_url'], ['http', 'https']),
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

    /**
     * Render the Production add Cast/Crew metabox
     *
     * @param WP_Post $post
     * @param array $metabox
     * @return void
     * @throws Throwable
     */
    public function renderAddCastCrewMetabox(WP_Post $post, array $metabox): void
    {
        $production = Production::make($post);

        try {
            /** @var CastAndCrew[] $members */
            $members = collect($production->getCastAndCrew() ?? [])
                ->groupBy('ccwp_join.type')
                ->all();
        } catch (Throwable) {
            $members = [];
        }

        try {
            $options = $production->getCastCrewNames();
        } catch (Throwable) {
            $options = [];
        }

        View::make('admin/production-castcrew-metabox.php', [
            'wp_nonce' => wp_nonce_field(
                basename(__FILE__),
                'ccwp_add_cast_and_crew_to_production_box_nonce',
                true,
                false,
            ),
            'post' => $post,
            'metabox' => $metabox,
            'options' => $options,
            'cast_members' => $members['cast']?->all() ?? [],
            'crew_members' => $members['crew']?->all() ?? [],
        ])->render();
    }

    /**
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     * @return void
     * @throws Throwable
     */
    public function saveAddCastCrew(int $postId, WP_Post $post, bool $update): void
    {
        // Verify meta box nonce
        if (
            !isset($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'])
            || !wp_verify_nonce($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'], basename(__FILE__))
        ) {
            return;
        }

        // Return if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Store custom fields values
        $production_cast = !empty($_POST['ccwp_add_cast_to_production']) ? $_POST['ccwp_add_cast_to_production'] : [];
        $production_crew = !empty($_POST['ccwp_add_crew_to_production']) ? $_POST['ccwp_add_crew_to_production'] : [];

        $production = Production::make($post);
        $production->saveCastAndCrew('cast', $production_cast);
        $production->saveCastAndCrew('crew', $production_crew);
    }
}
