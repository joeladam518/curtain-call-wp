<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

use CurtainCall\Models\Production;
use CurtainCall\View;

get_header();
?>

<div class="ccwp-main">
    <div class="ccwp-main-content-container">
        <h1 class="ccwp-page-heading">Productions</h1>
        <?php if (!have_posts()) : ?>
            <div class="ccwp-container">
                <h2>Sorry!</h2>
                <p>There are currently no productions in our directory. Please check back soon!</p>
            </div>
        <?php else : ?>
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

<?php get_footer(); ?>
