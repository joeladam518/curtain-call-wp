<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string $chronological_state
 * @var WP_Query $wp_query
 */

use CurtainCall\Models\Production;
use CurtainCall\Support\View;

$wp_query->rewind_posts();
?>

<?php if ($wp_query->post_count > 0) : ?>
    <div class="ccwp-section ccwp-<?php echo $chronological_state; ?>-productions-section">
        <?php if ($chronological_state === 'future') : ?>
            <h2>Upcoming Shows</h2>
        <?php elseif ($chronological_state === 'past') : ?>
            <h2>Production History</h2>
        <?php endif; ?>

        <?php
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                View::make('frontend/archive-production-row.php', [
                    'chronological_state' => $chronological_state,
                    'production' => Production::make(get_post()),
                ])->render();
            }

            $wp_query->reset_postdata();
        ?>
    </div>
<?php endif; ?>
