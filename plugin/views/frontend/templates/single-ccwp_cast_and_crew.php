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

get_header( 'single' );
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <?php
        $post_classes = []; // You can dynamically add classes to the article by adding to this array...

        $castcrew = CastAndCrew::make(get_post());
        $castcrew_name = "{$castcrew->name_first} {$castcrew->name_last}";
        
        $talent_birthplace = '';
        if (isset($castcrew->birthday)) {
            $talent_birthplace .= 'Born on ' . Carbon::parse($castcrew->birthday)->toFormattedDateString();
            if (isset($castcrew->hometown)) {
                $talent_birthplace .= ' in ' . $castcrew->hometown;
            }
            $talent_birthplace .= '.';
        } else if (isset($castcrew->hometown)) {
            $talent_birthplace .= 'Born in ' . $castcrew->hometown . '.';
        }
    ?>
    
    <?php if (get_post_status($castcrew->getPost()) == 'publish'): ?>
   
    <div id="main" class="sidebar-none sidebar-divider-off">	
        <div class="wf-wrap">
            <div class="wf-container-main">
                <div id="content" class="content talent-content-container" role="main">
                    <div class="ccwp-detail-page-breadcrumbs">
                        <a href="/">Home</a>&nbsp;&nbsp;/&nbsp;
                        <a href="/cast-and-crew">Cast &amp; Crew</a>&nbsp;&nbsp;/&nbsp;
                        <span><?php echo $castcrew_name; ?></span>
                    </div>
                    
                    <div id="post-<?php echo $castcrew->ID; ?>" <?php post_class( $post_classes ); ?>>
                        <div class="talent-bio-container">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="talent-profile-image">
                                    <?php the_post_thumbnail('full'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="talent-profile-details">
                                <h1><?php echo $castcrew_name ?></h1>
                                
                                <?php if (isset($castcrew->self_title)): ?>
                                    <h3><?php echo $castcrew->self_title; ?></h3>
                                <?php endif; ?>
                                
                                <?php if ($talent_birthplace != '') : ?>
                                    <p class="talent-birthplace"><?php echo $talent_birthplace; ?></p>
                                <?php endif; ?>
                                
                                <?php if (isset($castcrew->fun_fact)) : ?>
                                    <p class="talent-fun-fact"><?php echo $castcrew->fun_fact; ?></p>
                                <?php endif; ?>
                                
                                <div class="talent-bio-section">
                                    <?php if (!empty(get_the_content())) : ?>
                                        <?php the_content(); ?>
                                    <?php endif; ?>
                                </div>
                                                            
                                <?php if (isset($castcrew->website_link) || isset($castcrew->facebook_link) || isset($castcrew->instagram_link) || isset($castcrew->twitter_link)): ?>
                                    <h4 class="connect-with-talent-title">
                                        Connect with <?php echo $castcrew->name_first; ?>!
                                    </h4>
                                <?php endif; ?>
                                
                                <?php if (isset($castcrew->website_link)) :?>
                                    <p class="talent-website-link">
                                        <a href="http://<?php echo $castcrew->website_link; ?>">
                                            <?php echo $castcrew->website_link; ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (isset($castcrew->facebook_link) || isset($castcrew->instagram_link) || isset($castcrew->twitter_link)): ?>
                                    <div class="talent-social">
                                        <?php if (isset($castcrew->facebook_link)) : ?>
                                        <a href="http://<?php echo $castcrew->facebook_link; ?>">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if (isset($castcrew->instagram_link)) : ?>
                                        <a href="http://<?php echo $castcrew->instagram_link; ?>">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                        <?php endif; ?>
    
                                        <?php if (isset($castcrew->twitter_link)) : ?>
                                            <a href="http://<?php echo $castcrew->twitter_link; ?>">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php
                            $productions = $castcrew->getProductions();
                            $roles_by_pid = CastAndCrew::rolesByProductionId($productions);
                            $productions_shown = [];
                        ?>
                        
                        <?php if (!empty($productions)): ?>
                            <div class="ccwp-detail-page-cross-db-directory cast-prod-directory">
                                <h2>Productions</h2>
                                
                                <section class="cast-productions-section">
                                    <div class="cast-prod-flex-container">
                                        <?php foreach($productions as $production): ?>
                                            <?php
                                                if (in_array($production['ID'], $productions_shown)) {
                                                    continue;
                                                }
                                                $castcrew_prod_dates = ccwp_cast_prod_format_dates(
                                                    $production['post_meta']['_ccwp_production_date_start'],
                                                    $production['post_meta']['_ccwp_production_date_end']
                                                );
                                            ?>
                                            <div class="cast-prod-show">
                                                <?php if (has_post_thumbnail($production['ID'])): ?>
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
                                                        <p><?php echo $castcrew_prod_dates; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php 
	                                        $productions_shown[] = $production['ID'];
	                                        endforeach;
	                                    ?>
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
