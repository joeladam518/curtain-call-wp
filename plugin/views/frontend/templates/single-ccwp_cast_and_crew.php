<?php if (!defined('ABSPATH')) die;

use CurtainCallWP\PostTypes\CastAndCrew;
use Carbon\CarbonImmutable as Carbon;

function ccwp_cast_prod_format_dates($prod_start_date, $prod_end_date) {
    $show_start_date = new Carbon($prod_start_date);
    $show_end_date = new Carbon($prod_end_date);
    $show_start_date_format = 'F jS';
    $show_end_date_format = '';
    // don't show end date month if both dates are in the same month
    if ($show_start_date->format('F') != $show_end_date->format('F')) {
        $show_end_date_format .= 'F ';
    }
    $show_end_date_format .= 'jS';
    // don't show start date year if both dates are in the same year
    if ($show_start_date->format('Y') != $show_end_date->format('Y')) {
        $show_start_date_format .= ', Y';
    }
    $show_end_date_format .= ', Y';
    $show_start_date_formatted = $show_start_date->format($show_start_date_format);
    $show_end_date_formatted = $show_end_date->format($show_end_date_format);
    $show_dates_formatted = $show_start_date_formatted;
    // only show one date if the dates are identical
    if ($show_start_date_formatted != $show_end_date_formatted) {
        $show_dates_formatted .= ' - ' . $show_end_date_formatted;
    }
    return $show_dates_formatted;
}

?>

<?php get_header( 'single' ); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <?php
        $castcrew = CastAndCrew::make(get_post());
        
        //$current_post = get_post();
        //$current_post_custom_fields = get_post_meta($current_post->ID);

        // You can dynamically add classes to the article by adding to this array...
        $post_classes = [];
        
        // required
        $cast_crew_name_first = get_custom_field('_ccwp_cast_crew_name_first');
        $cast_crew_name_last = get_custom_field('_ccwp_cast_crew_name_last');
        $cast_crew_self_title = get_custom_field('_ccwp_cast_crew_self_title');
        // optional
        $cast_crew_birthday = get_custom_field('_ccwp_cast_crew_birthday');
        $cast_crew_hometown = get_custom_field('_ccwp_cast_crew_hometown');
        $cast_crew_website_link = get_custom_field('_ccwp_cast_crew_website_link');
        $cast_crew_facebook_link = get_custom_field('_ccwp_cast_crew_facebook_link');
        $cast_crew_twitter_link = get_custom_field('_ccwp_cast_crew_twitter_link');
        $cast_crew_instagram_link = get_custom_field('_ccwp_cast_crew_instagram_link');
        $cast_crew_fun_fact = get_custom_field('_ccwp_cast_crew_fun_fact');
        
        $talent_birthplace = '';
        if (!empty($cast_crew_birthday)) {
            $talent_birthplace .= 'Born on ' . Carbon::parse($cast_crew_birthday)->toFormattedDateString();
            if (!empty($cast_crew_hometown)) {
                $talent_birthplace .= ' in ' . $cast_crew_hometown;
            }
            $talent_birthplace .= '.';
        } elseif (!empty($cast_crew_hometown)) {
            $talent_birthplace .= 'Born in ' . $cast_crew_hometown . '.';
        }
    ?>
    
    <?php if (get_post_status() == 'publish'): ?>
   
    <div id="main" class="sidebar-none sidebar-divider-off">	
        <div class="wf-wrap">
            <div class="wf-container-main">
                <div id="content" class="content talent-content-container" role="main">
                    
                    <div class="ccwp-detail-page-breadcrumbs">
                        <a href="/">Home</a>&nbsp;&nbsp;/&nbsp;
                        <a href="/cast-and-crew">Cast &amp; Crew</a>&nbsp;&nbsp;/&nbsp;
                        <span>
                            <?php echo $cast_crew_name_first . ' ' . $cast_crew_name_last; ?>
                        </span>
                    </div>
                    
                    <div id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
                        
                        <div class="talent-bio-container">
                            
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="talent-profile-image">
                                    <?php the_post_thumbnail('full'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="talent-profile-details">
                                
                                <h1><?php echo $cast_crew_name_first . ' ' . $cast_crew_name_last; ?></h1>
                                
                                <?php if (!empty($cast_crew_self_title)) : ?>
                                    <h3><?php echo $cast_crew_self_title; ?></h3>
                                <?php endif; ?>
                                
                                <?php if ($talent_birthplace != '') : ?>
                                    <p class="talent-birthplace"><?php echo $talent_birthplace; ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($cast_crew_fun_fact)) : ?>
                                    <p class="talent-fun-fact"><?php echo $cast_crew_fun_fact; ?></p>
                                <?php endif; ?>
                                
                                <div class="talent-bio-section">
                                    <?php if (!empty(get_the_content())) : ?>
                                        <?php the_content(); ?>
                                    <?php else : ?>
                                        <p>No bio has been written for this cast or crew member yet.</p>
                                    <?php endif; ?>
                                </div>
                                                            
                                <?php if (!empty($cast_crew_website_link) || !empty($cast_crew_facebook_link) || !empty($cast_crew_twitter_link) || !empty($cast_crew_instagram_link)) : ?>
                                    <h4 class="connect-with-talent-title">
                                        Connect with <?php echo $cast_crew_name_first; ?>!
                                    </h4>
                                <?php endif; ?>
                                
                                <?php if (!empty($cast_crew_website_link)) :?>
                                    <p class="talent-website-link">
                                        <a href="http://<?php echo $cast_crew_website_link; ?>">
                                            <?php echo $cast_crew_website_link; ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($cast_crew_facebook_link) || !empty($cast_crew_twitter_link) || !empty($cast_crew_instagram_link)) : ?>
                                    <div class="talent-social">
                                        <?php if (!empty($cast_crew_facebook_link)) : ?>
                                        <a href="http://<?php echo $cast_crew_facebook_link; ?>">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($cast_crew_twitter_link)) : ?>
                                        <a href="http://<?php echo $cast_crew_twitter_link; ?>">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($cast_crew_instagram_link)) : ?>
                                        <a href="http://<?php echo $cast_crew_instagram_link; ?>">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
         
                        </div>
                        
                        <?php
                            $productions = $castcrew->getProductions();
                            $roles_by_pid = [];
                            foreach($productions as $p){
	                            if(!isset($roles_by_pid[$p['ID']])){ $roles_by_pid[$p['ID']] = []; }
	                            $roles_by_pid[$p['ID']][] = $p['ccwp_role'];
                            }
                            $productions_shown = [];
                        ?>
                        <?php if (!empty($productions)): ?>
                            <div class="ccwp-detail-page-cross-db-directory cast-prod-directory">
                                <h2>Productions</h2>
                                
                                <section class="cast-productions-section">
                                    <div class="cast-prod-flex-container">
                                        <?php foreach($productions as $production) : ?>
                                        <?php if(in_array($production['ID'], $productions_shown)){ continue; } ?>
                                            <?php
                                                $cast_prod_dates = ccwp_cast_prod_format_dates($production['post_meta']['_ccwp_production_date_start'], $production['post_meta']['_ccwp_production_date_end']);
                                            ?>
                                            <div class="cast-prod-show">
                                                <?php if (has_post_thumbnail($production['ID'])) : ?>
                                                    <div class="cast-prod-show-poster">
                                                        <a href="<?php the_permalink($production['ID']); ?>">
                                                            <?php echo get_the_post_thumbnail($production['ID'], 'full'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="cast-prod-show-info">
                                                    <div class="cast-prod-show-title">
                                                        <a href="<?php the_permalink($production['ID']); ?>">
                                                            <?php echo $production['post_meta']['_ccwp_production_name']; ?>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="cast-prod-role">
                                                        <p><?php echo implode(', ', $roles_by_pid[$production['ID']]); ?></p>
                                                    </div>
                                                    
                                                    <div class="cast-prod-dates">
                                                        <p><?php echo $cast_prod_dates; ?></p>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        <?php 
	                                        $productions_shown[] = $production['ID'];
	                                        endforeach; ?>
                                    </div>
                                    
                                </section>
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
