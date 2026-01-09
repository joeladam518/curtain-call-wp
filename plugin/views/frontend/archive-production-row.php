<?php

declare(strict_types=1);

use CurtainCall\Models\Production;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
}

/**
 * @var string $chronological_state
 * @var Production $production
 */

// Computed for this partial
$production_permalink = get_post_permalink($production->ID) ?: '';
$production_excerpt = get_the_excerpt($production->getPost());
$ticket_url = $production->getTicketUrl();
?>

<div class="productions-directory-row">
    <div class="production-poster">
        <?php if (has_post_thumbnail()): ?>
            <a href="<?php echo esc_url($production_permalink); ?>">
            <?php
            // @mago-ignore lint:no-unescaped-output
            echo get_the_post_thumbnail($production->getPost(), 'full');
            ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="production-details">
        <div class="production-name-container">
            <h3 class="production-name">
                <a href="<?php echo esc_url($production_permalink); ?>">
                    <?php echo esc_html($production->name); ?>
                </a>
            </h3>
            <?php if (isset($ticket_url)): ?>
                <a href="<?php echo esc_url($ticket_url); ?>" class="ccwp-btn" target="_blank">
                    <?php esc_html_e('Get Tickets', CCWP_TEXT_DOMAIN); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="ccwp-container">
            <?php if ($chronological_state === 'current'): ?>
                <div>
                    <span class="now-showing"><?php esc_html_e('Now Showing', CCWP_TEXT_DOMAIN); ?></span>
            <?php endif; ?>

            <span class="production-dates">
            <?php
            // @mago-ignore lint:no-unescaped-output
            echo $production->getFormattedShowDates();
            ?>
            </span>

            <?php if ($chronological_state === 'current'): ?>
                </div>
                <div>
            <?php endif; ?>

            <?php if (isset($production->show_times)): ?>
                <span class="production-times">
                    <?php echo esc_html($production->show_times); ?>
                </span>
            <?php endif; ?>

            <?php if (isset($production->venue)): ?>
                <span class="production-venue">
                    <?php echo esc_html($production->venue); ?>
                </span>
            <?php endif; ?>

            <?php if ($chronological_state === 'current'): ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($production_excerpt)): ?>
            <div class="production-excerpt">
                <?php echo esc_html($production_excerpt); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
