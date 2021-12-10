<?php
/**
 * @var string $chronological_state
 * @var Production $production
 */
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

use CurtainCall\Models\Production;

// Computed for this partial
$production_permalink = get_post_permalink($production->ID);
$production_thumbnail_html = get_the_post_thumbnail($production->getPost(), 'full');
$production_excerpt = get_the_excerpt($production->getPost());
$ticket_url = $production->getTicketUrl();
?>

<div class="productions-directory-row">
    <div class="production-poster">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php echo $production_permalink; ?>">
                <?php echo $production_thumbnail_html; ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="production-details">
        <div class="production-name-container">
            <h3 class="production-name">
                <a href="<?php echo $production_permalink; ?>">
                    <?php echo $production->name; ?>
                </a>
            </h3>
            <?php if (isset($ticket_url)) : ?>
                <a href="<?php echo $ticket_url; ?>" class="ccwp-btn" target="_blank">Get Tickets</a>
            <?php endif; ?>
        </div>

        <div class="ccwp-container">
            <?php if ($chronological_state == 'current') : ?>
                <div>
                    <span class="now-showing">Now Showing</span>
            <?php endif; ?>

            <span class="production-dates">
                <?php echo $production->getFormattedShowDates(); ?>
            </span>

            <?php if ($chronological_state == 'current') : ?>
                </div>
                <div>
            <?php endif; ?>

            <?php if (isset($production->show_times)) : ?>
                <span class="production-times">
                    <?php echo $production->show_times; ?>
                </span>
            <?php endif; ?>

            <?php if (isset($production->venue)) : ?>
                <span class="production-venue">
                    <?php echo $production->venue; ?>
                </span>
            <?php endif; ?>

            <?php if ($chronological_state == 'current') : ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($production_excerpt)) : ?>
            <div class="production-excerpt">
                <?php echo $production_excerpt; ?>
            </div>
        <?php endif;?>
    </div>
</div>
