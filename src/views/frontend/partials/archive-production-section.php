<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

use CurtainCallWP\PostTypes\Production;

/**
 * Expected Global variable for this partial
 * @var string $chronological_state
 * @var WP_Query $wp_query
**/

$wp_query->rewind_posts();
?>

<?php if ($wp_query->post_count > 0): ?>
    <div class="ccwp-section ccwp-<?php echo $chronological_state; ?>-productions-section">
        <?php
            switch($chronological_state) {
                case 'future':
                    echo '<h2>Upcoming Shows</h2>';
                    break;
                case 'past':
                    echo '<h2>Production History</h2>';
                    break;
            }
            
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                ccwpView('frontend/partials/archive-production-row.php', [
                    'production' => Production::make(get_post()),
                    'chronological_state' => $chronological_state,
                ])->render();
            }
    
            $wp_query->reset_postdata();
        ?>
    </div>
<?php endif; ?>
