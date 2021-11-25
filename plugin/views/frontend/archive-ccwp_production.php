<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

use CurtainCall\PostTypes\Production;
use CurtainCall\View;

get_header();
?>

<!-- Content -->
<div id="content" class="ccwp-productions-page" role="main">

    <?php echo '<h1 class="ccwp-archive-title">Productions</h1>'; ?>
    <?php //the_archive_title('<h1 class="ccwp-archive-title">', '</h1>'); ?>
    <?php //the_archive_description( '<h1 class="ccwp-archive-description">', '</h1>' ); ?>

    <?php if ( ! have_posts() ) : ?>

        <div class="ccwp-container">
            <h2>Sorry!</h2>
            <p>There are currently no productions in our directory. Please check back soon!</p>
        </div>

    <?php else: ?>

        <div class="ccwp-container">
            <?php
                $view_partial_path = 'frontend/partials/archive-production-section.php';

                View::make($view_partial_path, [
                    'wp_query' => Production::getCurrentPosts(),
                    'chronological_state' => 'current',
                ])->render();
                View::make($view_partial_path, [
                    'wp_query' => Production::getFuturePosts(),
                    'chronological_state' => 'future',
                ])->render();
                View::make($view_partial_path, [
                    'wp_query' => Production::getPastPosts(),
                    'chronological_state' => 'past',
                ])->render();
            ?>
        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
