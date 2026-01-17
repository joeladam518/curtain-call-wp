<?php

declare(strict_types=1);

use CurtainCall\Models\Production;
use CurtainCall\Support\View;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

get_header();
?>

<div class="ccwp-main">
    <div class="ccwp-main-content-container">
        <h1 class="ccwp-page-heading"><?php esc_html_e('Productions', CCWP_TEXT_DOMAIN); ?></h1>
        <?php if (!have_posts()): ?>
            <div class="ccwp-container">
                <h2><?php esc_html_e('Sorry!', CCWP_TEXT_DOMAIN); ?></h2>
                <p><?php esc_html_e(
                    'There are currently no productions in our directory. Please check back soon!',
                    CCWP_TEXT_DOMAIN,
                ); ?></p>
            </div>
        <?php else: ?>
            <div class="ccwp-container">
                <?php
                $partialPath = 'frontend/archive-production-section.php';

                View::make($partialPath, [
                    'wp_query' => Production::getCurrentPosts(),
                    'chronological_state' => 'current',
                ])->render();

                View::make($partialPath, [
                    'wp_query' => Production::getFuturePosts(),
                    'chronological_state' => 'future',
                ])->render();

                View::make($partialPath, [
                    'wp_query' => Production::getPastPosts(),
                    'chronological_state' => 'past',
                ])->render();
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
