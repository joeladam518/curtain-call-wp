<?php

declare(strict_types=1);

namespace CurtainCall\Blocks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

class ArchiveBlocks
{
    public static function register(): void
    {
        register_block_type('ccwp/productions-archive', [
            'render_callback' => [static::class, 'renderProductionsArchive'],
            'api_version' => 2,
            'supports' => [ 'align' => [ 'wide', 'full' ] ],
        ]);

        register_block_type('ccwp/cast-crew-directory', [
            'render_callback' => [static::class, 'renderCastCrewDirectory'],
            'api_version' => 2,
            'supports' => [ 'align' => [ 'wide', 'full' ] ],
        ]);
    }

    public static function renderProductionsArchive(array $attributes = [], string $content = ''): string
    {
        // current, future, past
        $sections = [
            'current' => Production::getCurrentPosts(),
            'future'  => Production::getFuturePosts(),
            'past'    => Production::getPastPosts(),
        ];

        ob_start();
        echo '<div class="ccwp-productions-archive">';
        foreach ($sections as $label => $query) {
            if (!$query->have_posts()) { continue; }
            $heading = ucfirst($label);
            echo '<section class="ccwp-productions-section ccwp-productions-' . esc_attr($label) . '">';
            echo '<h2>' . esc_html($heading) . '</h2>';
            echo '<div class="ccwp-container">';
            while ($query->have_posts()) {
                $query->the_post();
                /** @var Production $prod */
                $prod = Production::make(get_post());
                echo '<article class="ccwp-production">';
                echo '<h3 class="ccwp-production-name"><a href="' . esc_url(get_permalink($prod->ID)) . '">' . esc_html($prod->name ?? get_the_title()) . '</a></h3>';
                echo '<div class="ccwp-production-dates">' . esc_html($prod->getFormattedShowDates()) . '</div>';
                echo '</article>';
            }
            wp_reset_postdata();
            echo '</div>';
            echo '</section>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    public static function renderCastCrewDirectory(array $attributes = [], string $content = ''): string
    {
        $query = CastAndCrew::getPosts();
        if (!$query->have_posts()) {
            return '<div class="ccwp-castcrew-directory"><p>' . esc_html__('No cast or crew found.', CCWP_TEXT_DOMAIN) . '</p></div>';
        }

        $alphaIndexes = CastAndCrew::getAlphaIndexes($query);
        $current = null; $previous = null;

        ob_start();
        echo '<div class="ccwp-castcrew-directory">';
        // Alphabet nav
        echo '<div class="ccwp-alphabet-navigation">';
        foreach (range('A','Z') as $letter) {
            if (in_array($letter, $alphaIndexes, true)) {
                echo '<a href="#' . esc_attr($letter) . '">' . esc_html($letter) . '</a>';
            } else {
                echo '<span>' . esc_html($letter) . '</span>';
            }
        }
        echo '</div>';

        // List
        echo '<div class="ccwp-container ccwp-alphabet-container">';
        while ($query->have_posts()) {
            $query->the_post();
            $castcrew = CastAndCrew::make(get_post());
            if ($castcrew->post_status !== 'publish' || !isset($castcrew->name_last)) { continue; }
            $current = strtoupper(substr($castcrew->name_last, 0, 1));
            if ($current !== $previous) {
                if ($previous !== null) {
                    echo '</div>';
                }
                echo '<h3 class="ccwp-alphabet-header" id="' . esc_attr($current) . '">' . esc_html($current) . '</h3>';
                echo '<div class="ccwp-row mb-5">';
                $previous = $current;
            }
            echo '<div class="castcrew-wrapper">';
            echo '<h4 class="castcrew-name"><a href="' . esc_url(get_permalink($castcrew->ID)) . '">' . esc_html($castcrew->getFullName()) . '</a></h4>';
            if (isset($castcrew->self_title)) {
                echo '<div class="castcrew-self-title">' . esc_html($castcrew->self_title) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>'; // last group
        wp_reset_postdata();
        echo '</div>';

        return (string) ob_get_clean();
    }
}
