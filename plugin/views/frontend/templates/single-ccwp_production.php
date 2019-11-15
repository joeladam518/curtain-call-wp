<?php if (!defined('ABSPATH')) die;

use CurtainCallWP\PostTypes\Production;
use Carbon\CarbonImmutable as Carbon;

function  strip_shortcode_gallery( $content ) {
    preg_match_all( '/'. get_shortcode_regex() .'/s', $content, $matches, PREG_SET_ORDER );
    if ( ! empty( $matches ) ) {
        foreach ( $matches as $shortcode ) {
            if ( 'gallery' === $shortcode[2] ) {
                $pos = strpos( $content, $shortcode[0] );
                if ($pos !== false)
                    return substr_replace( $content, '', $pos, strlen($shortcode[0]) );
            }
        }
    }
    return $content;
}

function calculate_temporal_state($start_date, $end_date) {
    $today = Carbon::now();
    if ($today > $end_date) {
        return 'past';
    } else if ($today < $start_date) {
        return 'future';
    } else {
        return 'current';
    }
}

get_header( 'single' );
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <?php 
        $production = Production::make(get_post());

        //$current_post = get_post();
        //$current_post_custom_fields = get_post_meta($current_post->ID);
        
        // You can dynamically add classes to the article by adding to this array...
        $post_classes = [];
        
        $show_start_date = new Carbon(get_custom_field('_ccwp_production_date_start'));
        $show_end_date = new Carbon(get_custom_field('_ccwp_production_date_end'));
        $show_temporal_state = calculate_temporal_state($show_start_date, $show_end_date);
        
        // date formatting
        $show_start_date_format = 'F jS';
        $show_end_date_format = $show_start_date_format;
        $today = Carbon::now();
        // if the show is in the current year don't show the year
        if ($today->lt($show_end_date) || $show_start_date->format('Y') != $today->format('Y')) {
            // only show end date year if both dates are in the same year
            if ($show_start_date->format('Y') != $show_end_date->format('Y')) {
                $show_start_date_format .= ' Y';
            }
        }
        if ($today->lt($show_end_date) || $show_end_date->format('Y') != $today->format('Y')) {
            $show_end_date_format .= ' Y';
        }
        $show_start_date_formatted = $show_start_date->format($show_start_date_format);
        $show_end_date_formatted = $show_end_date->format($show_end_date_format);
        $show_dates_formatted = $show_start_date_formatted;
        if ($show_start_date_formatted != $show_end_date_formatted) {
            $show_dates_formatted .= ' - ' . $show_end_date_formatted;
        }
        
        $ticket_link = 'https://www.rutheckerdhall.com/events';
        if (!empty(get_custom_field('_ccwp_production_ticket_url'))) {
            $ticket_link = get_custom_field('_ccwp_production_ticket_url');
        }
        
        $post_gallery = false;
        if ( get_post_gallery() ) :
	        $post_gallery = get_post_gallery();
	    endif; 
        
    ?>

    <?php if (get_post_status() == 'publish'): ?>
        
    <div id="main" class="sidebar-none sidebar-divider-off">	
        <div class="wf-wrap">
            <div class="wf-container-main">
                <div id="content" class="content productions-content-container" role="main">
                    
                    <div class="ccwp-detail-page-breadcrumbs">
                        <a href="/">Home</a>&nbsp;&nbsp;/&nbsp;
                        <a href="/productions">Productions</a>&nbsp;&nbsp;/&nbsp;
                        <span>
                            <?php 
                                if (!empty(get_custom_field('_ccwp_production_name'))) {
                                    echo get_custom_field('_ccwp_production_name');
                                } else {
                                    the_title();
                                }
                            ?>
                        </span>
                    </div>
                    
                    <div id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
                                                
                        <div class="productions-directory-show-container">
                            
                            <div class="production-single-flex-container">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="show-poster">
                                        <?php the_post_thumbnail('full'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="production-single-flex-info-column">
                                    <h1 class="ccwp-single-header">
                                        <?php 
                                            if (!empty(get_custom_field('_ccwp_production_name'))) {
                                                echo get_custom_field('_ccwp_production_name');
                                            } else {
                                                the_title();
                                            }
                                        ?>
                                    </h1>
                                    <div class="show-title-container">
                                        <span class="show-title-content">
                                            <?php if ($show_temporal_state == 'current') : ?>
                                                <span class="now-showing">Now Showing</span>
                                            <?php endif; ?>
                                            <span class="show-dates">
                                                <?php echo $show_dates_formatted; ?>
                                            </span>
                                        </span>
                                        <?php if ($show_temporal_state != 'past') : ?>
                                        <a href="<?php echo $ticket_link; ?>" class="show-tickets" target="_blank">
                                            Get Tickets
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="show-info-container">
                                        <?php if (!empty(get_custom_field('_ccwp_production_show_times'))) : ?>
                                            <div class="show-times">
                                                <?php echo get_custom_field('_ccwp_production_show_times') ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty(get_custom_field('_ccwp_production_venue'))) : ?>
                                            <div class="show-venue">
                                                <?php echo get_custom_field('_ccwp_production_venue'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="show-content-container">
                                <?php if (!empty(get_the_content())) : ?>
                                <div class="show-summary">
                                    <?php echo apply_filters( 'the_content', strip_shortcode_gallery(get_the_content())); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty(get_custom_field('_ccwp_production_press'))) : ?>
                                <div class="show-press-quotes">
                                    <h4>Press Highlights</h4>
                                    <p><?php echo get_custom_field('_ccwp_production_press'); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($post_gallery != false): ?>
                            <div class="ccwp-detail-page-production-gallery">                                            
                                <h2>Gallery</h2>
                                <div class="production-gallery-flex-container">
                                    <?php echo $post_gallery; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                            $cast = $production->getCastAndCrew('cast');
                            $crew = $production->getCastAndCrew('crew');
                        ?>
                        <?php if (!empty($cast) || !empty($crew)) : ?>
                            <div class="ccwp-detail-page-cross-db-directory production-cc-directory">
                                
                                <?php if (!empty($cast)): ?>
                                    <section class="production-cast-section">
                                        
                                        <h2>Cast</h2>
                                        
                                        <div class="production-cc-flex-container">
                                            <?php foreach ($cast as $cast_member): ?>
                                                <div class="production-cc-member">
                                                    
                                                    <?php if (has_post_thumbnail($cast_member['ID'])): ?>
                                                        <div class="prod-cc-photo">
                                                            <a href="<?php the_permalink($cast_member['ID']); ?>">
                                                                <?php echo get_the_post_thumbnail($cast_member['ID'], 'thumbnail'); ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?> 
                                                    
                                                    <div class="prod-cc-info">
                                                        <div class="prod-cc-name">
                                                            <a href="<?php the_permalink($cast_member['ID']); ?>">
                                                                <?php echo $cast_member['post_meta']['_ccwp_cast_crew_name_first'] . ' ' . $cast_member['post_meta']['_ccwp_cast_crew_name_last']; ?>
                                                            </a>
                                                        </div>
                                                        
                                                        <div class="prod-cc-role">
                                                            <p><?php echo $cast_member['ccwp_role']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            <?php endforeach; ?> 
                                        </div>
                                        
                                    </section>
                                <?php endif;?>
                            
                                <?php if (!empty($crew)): ?>
                                    <section class="production-crew-section">
                                        
                                        <h2>Crew</h2>
                                        
                                        <div class="production-cc-flex-container">
                                            <?php foreach ($crew as $crew_member): ?>
                                                <div class="production-cc-member">
                                                    
                                                    <?php if (has_post_thumbnail($crew_member['ID'])): ?>
                                                        <div class="prod-cc-photo">
                                                            <a href="<?php the_permalink($crew_member['ID']); ?>">
                                                                <?php echo get_the_post_thumbnail($crew_member['ID'], 'thumbnail'); ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?> 
                                                    
                                                    <div class="prod-cc-info">
                                                        <div class="prod-cc-name">
                                                            <a href="<?php the_permalink($crew_member['ID']); ?>">
                                                                <?php echo $crew_member['post_meta']['_ccwp_cast_crew_name_first'] . ' ' . $crew_member['post_meta']['_ccwp_cast_crew_name_last']; ?>
                                                            </a>
                                                        </div>
                                                        
                                                        <div class="prod-cc-role">
                                                            <p><?php echo $crew_member['ccwp_role']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php endif; // content is visible ?>

<?php endwhile; endif; // end of the loop. ?>

<?php get_footer(); ?>
