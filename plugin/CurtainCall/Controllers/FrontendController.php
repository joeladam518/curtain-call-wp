<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use CurtainCall\Support\View;
use Illuminate\Support\Arr;
use WP_Post;
use WP_Screen;

final class FrontendController
{
    /**
     * Register the stylesheets for the public-facing side of the site
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        $fontawesomeSrc = ccwp_plugin_url('assets/frontend/fontawesomefree.css');
        $frontendSrc = ccwp_plugin_url('assets/frontend/curtain-call-wp-frontend.css');
        $version = $this->getVersion();

        wp_enqueue_style('fontawesomefree', $fontawesomeSrc, [], $version);
        wp_enqueue_style(CCWP_PLUGIN_NAME, $frontendSrc, [], $version);
    }

    /**
     * Determine if the current theme has a ccwp post-type template
     *
     * @param string $type
     * @param array|string[] $templates
     * @return bool
     */
    private function themeHasTemplate(string $type, array $templates): bool
    {
        $themeTemplate = locate_template(
            Arr::where($templates, static fn($item) => $item !== "{$type}.php"),
            false,
            false,
            [],
        );

        return (bool) $themeTemplate;
    }

    /**
     * Override the theme's single template with CurtainCall's custom template
     *
     * @param string $template
     * @param string $type
     * @param array|string[] $templates
     * @return string
     * @global WP_Post $post
     */
    public function loadSingleTemplates(string $template, string $type, array $templates): string
    {
        $postType = $this->getPostType();

        if ($postType === Production::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_production.php');
        }

        if ($postType === CastAndCrew::POST_TYPE && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/single-ccwp_cast_and_crew.php');
        }

        return $template;
    }

    /**
     * Override the theme's archive template with CurtainCall's custom template
     *
     * @param string $template
     * @param string $type
     * @param array|string[] $templates
     * @return string
     */
    public function loadArchiveTemplates(string $template, string $type, array $templates): string
    {
        if (is_post_type_archive(Production::POST_TYPE) && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_production.php');
        }

        if (is_post_type_archive(CastAndCrew::POST_TYPE) && !$this->themeHasTemplate($type, $templates)) {
            return View::path('frontend/archive-ccwp_cast_and_crew.php');
        }

        return $template;
    }

    /**
     * Get the current post-type
     *
     * @return string|null
     */
    private function getPostType(): ?string
    {
        /** @var WP_Post|array{post_type: string|null}|null $post */
        $post = get_post();

        if ($post instanceof WP_Post && isset($post->post_type)) {
            return $post->post_type;
        }

        if (is_array($post) && isset($post['post_type'])) {
            return $post['post_type'];
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
