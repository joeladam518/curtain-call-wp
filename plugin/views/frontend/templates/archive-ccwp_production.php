<?php if (!defined('ABSPATH')) die;

use Carbon\CarbonImmutable as Carbon;

$args_asc = [
    'post_type' => [
        'ccwp_production', 
        'post',
    ],
    'post_status' => 'publish',
    'meta_key' => '_ccwp_production_date_start',
    'orderby' => 'meta_value',
    'order'   => 'ASC',
    'nopaging' => true,
];
$args_desc = [
    'post_type' => [
        'ccwp_production', 
        'post',
    ],
    'post_status' => 'publish',
    'meta_key' => '_ccwp_production_date_start',
    'orderby' => 'meta_value',
    'order'   => 'DESC',
    'nopaging' => true,
];

$meta_args = [
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_ccwp_production_date_start', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '<=',
        ],
        [
            'key' => '_ccwp_production_date_end', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '>=',
        ]
    ]
];
$current_args = array_merge($args_asc, $meta_args);

$meta_args = [
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_ccwp_production_date_start', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '>',
        ],
        [
            'key' => '_ccwp_production_date_end', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '>',
        ]
    ]
];
$future_args = array_merge($args_asc, $meta_args);

$meta_args = [
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_ccwp_production_date_start', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '<',
        ],
        [
            'key' => '_ccwp_production_date_end', 
            'value' => Carbon::now()->toDateString(),
            'compare' => '<',
        ]
    ]
];
$past_args = array_merge($args_desc, $meta_args);

if (!function_exists('ccwp_display_productions_of_type')) {
    function ccwp_display_productions_of_type($production_display_type, $args = null) 
    {
        $result = new WP_Query($args);
        
        if ($result->have_posts()) {    
            echo '<div class="'.$production_display_type.'-production-row production-directory-row">';
            
            $loop_count = 0;
            while ($result->have_posts()) {
                $result->the_post();
                
                // date formatting
                $show_start_date = Carbon::parse(getCustomField('_ccwp_production_date_start'));
                $show_end_date = Carbon::parse(getCustomField('_ccwp_production_date_end'));
                $show_start_date_format = 'F jS';
                $show_end_date_format = $show_start_date_format;
                $today = Carbon::now();
                // if the show is in the current year don't show the year
                if ($production_display_type == 'past' || $show_start_date->format('Y') != $today->format('Y')) {
                    // only show end date year if both dates are in the same year
                    if ($show_start_date->format('Y') != $show_end_date->format('Y')) {
                        $show_start_date_format .= ' Y';
                    }
                }
                if ($production_display_type == 'past' || $show_end_date->format('Y') != $today->format('Y')) {
                    $show_end_date_format .= ' Y';
                }
                $show_start_date_formatted = $show_start_date->format($show_start_date_format);
                $show_end_date_formatted = $show_end_date->format($show_end_date_format);
                $show_dates_formatted = $show_start_date_formatted;
                if ($show_start_date_formatted != $show_end_date_formatted) {
                    $show_dates_formatted .= ' - ' . $show_end_date_formatted;
                }
                
                if (!empty(get_the_title())) {
                     
                    $loop_count++;
                    if ($loop_count == 1) {
                        if ($production_display_type == 'future') {
                            echo '<h2 class="upcoming-show-header">Upcoming Shows</h2>';
                        }
                        if ($production_display_type == 'past') {
                            echo '<h2 class="production-archives-header">Production History</h2>';
                        }
                    }
                    
                    echo '<div class="productions-directory-show-container">';
                        
                    if (has_post_thumbnail()) {
                        echo '<div class="show-poster">';
                        echo '<a href="'.get_post_permalink().'">';
                        echo the_post_thumbnail('full');
                        echo '</a></div>';
                    }
                        
                    echo '<div class="show-content-container">';
                        
                    echo '<div class="show-title-container">';
                    
                    echo '<h3 class="show-title">';
                    echo '<a href="'.get_post_permalink().'">';
                    
                    if (!empty(getCustomField('_ccwp_production_name'))) {
                        echo getCustomField('_ccwp_production_name');
                    } else {
                        the_title();
                    }
                    
                    echo '</a>';
                    echo '</h3>';
                    
                    if ($production_display_type != 'past') {
                        $ticket_link = 'https://www.rutheckerdhall.com/events';
                        if (!empty(getCustomField('_ccwp_production_ticket_url'))) {
                            $ticket_link = getCustomField('_ccwp_production_ticket_url');
                        }
                        echo '<a href="'.$ticket_link.'" class="show-tickets" target="_blank">Get Tickets</a>';
                    }
                    
                    echo '</div>'; // <div class="show-title-container">
                            
                    echo '<div class="show-info-container">';
                    
                    if ($production_display_type == 'current') {
                        echo '<div><span class="now-showing">Now Showing</span>';
                    }
                    
                    echo '<span class="show-dates">' . $show_dates_formatted . '</span>';
                    
                    if ($production_display_type == 'current') {
                        echo '</div>'; 
                        echo '<div>';  
                    }
                    
                    if (!empty(getCustomField('_ccwp_production_show_times'))) {
                        echo '<span class="show-times">';
                        echo getCustomField('_ccwp_production_show_times');
                        echo '</span>';
                    }
                    
                    if (!empty(getCustomField('_ccwp_production_venue'))) {
                        echo '<span class="show-venue">';
                        echo getCustomField('_ccwp_production_venue');
                        echo '</span>';
                    }
                    
                    if ($production_display_type == 'current') {
                        echo '</div>'; // <div class="show-info-container">
                    }
                    
                    echo '</div>';
                            
                    if (!empty(get_the_excerpt())) {
                        echo '<div class="show-summary">';
                        the_excerpt();
                        echo '</div>';
                    }
                            
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }
        wp_reset_postdata();
    }
}
?>

<?php get_header(); ?>

<!-- Content -->
<div id="content" class="ccwp-productions" role="main">
    
    <?php echo '<h1 class="ccwp-archive-title">Productions</h1>'; ?>
    <?php //the_archive_title('<h1 class="ccwp-archive-title">', '</h1>'); ?>
    <?php //the_archive_description( '<h1 class="ccwp-archive-description">', '</h1>' ); ?>

    <?php if ( ! have_posts() ) : ?>
    
        <h2>Sorry!</h2>
        <p>There are currently no productions in our directory. Please check back soon!</p>
        
    <?php else: ?>
    
        <div class="productions-directory-container">
            <?php
                ccwp_display_productions_of_type('current', $current_args);
                ccwp_display_productions_of_type('future', $future_args);
                ccwp_display_productions_of_type('past', $past_args);
            ?>
        </div>
    
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>
