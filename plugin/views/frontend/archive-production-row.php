<?php

declare(strict_types=1);

use CurtainCall\Models\Production;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

/**
 * @var string $chronological_state
 * @var Production $production
 */

// Computed for this partial
$productionExcerpt = $production->getExcerpt();
$ticketUrl = $production->getTicketUrl();
?>

<div class="productions-directory-row">
    <div class="production-poster">
        <?php if ($production->hasImage()): ?>
            <a href="<?php $production->thePermalink(); ?>">
                <?php $production->theImage('full'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="production-details">
        <div class="production-name-container">
            <h3 class="production-name">
                <a href="<?php $production->thePermalink(); ?>">
                    <?php echo esc_html($production->name); ?>
                </a>
            </h3>
            <?php if ($chronological_state !== 'past' && !empty($ticketUrl)): ?>
                <a href="<?php echo esc_url($ticketUrl); ?>" class="ccwp-btn" target="_blank">
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

        <?php if (!empty($productionExcerpt)): ?>
            <div class="production-excerpt">
                <?php echo esc_html($productionExcerpt); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
