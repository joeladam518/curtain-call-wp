<?php

use CurtainCallWP\PostTypes\Production;
use Carbon\CarbonImmutable as Carbon;

/**
 * Expected global variables for this partial
 * @var Production $production
 * @var string     $chronological_state
**/

// computed for this partial
$production_permalink = get_post_permalink($production->ID);
$production_thumbnail_html = get_the_post_thumbnail($production->getPost(), 'full');
$production_excerpt = get_the_excerpt($production->getPost());
$ticket_url = $production->getTicketUrl();
?>

<div class="productions-directory-show-container">
    <?php if (has_post_thumbnail()): ?>
        <div class="show-poster">';
            <a href="<?php echo $production_permalink; ?>">
                <?php echo $production_thumbnail_html; ?>
            </a>
        </div>
    <?php endif; ?>
        
    <div class="show-content-container">
        <div class="show-title-container">
            <h3 class="show-title">
                <a href="<?php echo $production_permalink; ?>">
                    <?php echo $production->name; ?>
                </a>
            </h3>
            <?php if (!empty($ticket_url)): ?>
                <a href="<?php echo $ticket_url; ?>" class="show-tickets" target="_blank">Get Tickets</a>
            <?php endif; ?>
        </div>
        
        <div class="show-info-container">';
            <?php if ($chronological_state == 'current'): ?>
                <div>
                    <span class="now-showing">Now Showing</span>
            <?php endif; ?>
                
            <span class="show-dates"><?php echo $production->getFormattedShowDates(); ?></span>
    
            <?php if ($chronological_state == 'current'): ?>
                </div>
                <div>
            <?php endif; ?>
                
            <?php if (isset($production->show_times)): ?>
                <span class="show-times"><?php echo $production->show_times; ?></span>
            <?php endif; ?>
            
            <?php if (isset($production->venue)): ?>
                <span class="show-venue"><?php echo $production->venue; ?></span>
            <?php endif; ?>
    
            <?php if ($chronological_state == 'current'): ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($production_excerpt)): ?>
            <div class="show-summary"><?php echo $production_excerpt; ?></div>
        <?php endif;?>
    </div>
</div>