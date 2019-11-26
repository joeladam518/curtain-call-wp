<?php if (!defined('ABSPATH')) die;

use CurtainCallWP\PostTypes\CastAndCrew;
use CurtainCallWP\PostTypes\Production;

get_header( 'single' );
?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php
        // You can dynamically add classes to the article by adding to this array...
        $post_classes = [];
        
        /** @var Production $production */
        $production = Production::make(get_post());
        $production_name = isset($production->name) ? $production->name : get_the_title();
        
        // Production $ticket link
        if (isset($production->ticket_url) && $production->getChronologicalState() !== 'past') {
            $ticket_link = $production->ticket_url;
        } else {
            // TODO: 2019-11-15: change this to an option setting
            $ticket_link = 'https://www.rutheckerdhall.com/events';
        }
        
        // Production photo gallery
        $post_gallery = get_post_gallery();
    ?>

    <?php if (get_post_status($production->getPost()) == 'publish'): ?>
        <main id="main" class="ccwp-main">
            <div id="content" class="ccwp-main-content-container">
                <div class="ccwp-post-breadcrumbs">
                    <a href="/">Home</a>&nbsp;&nbsp;/&nbsp;
                    <a href="/productions">Productions</a>&nbsp;&nbsp;/&nbsp;
                    <span><?php echo $production_name; ?></span>
                </div>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class($post_classes); ?>>
                    <section class="ccwp-section ccwp-production-info-section">
                        <!-- info about the production -->
                        <div class="ccwp-container">
                            <div class="ccwp-row">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="ccwp-production-poster">
                                        <?php the_post_thumbnail('full'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="show-info">
                                    <h1 class="ccwp-post-header"><?php echo $production_name; ?></h1>
                                    
                                    <div class="ccwp-show-dates-container">
                                        <?php if ($production->getChronologicalState() == 'current'): ?>
                                            <span class="now-showing-label">Now Showing</span>
                                        <?php endif; ?>
                                        <span class="show-dates">
                                            <?php echo $production->getFormattedShowDates(); ?>
                                        </span>
                                        <?php if ($production->getChronologicalState() != 'past'): ?>
                                            <a class="ccwp-btn get-tickets-btn" href="<?php echo $ticket_link ?>" target="_blank">Get Tickets</a>
                                        <?php endif; ?>
                                    </div>
        
                                    <?php if (isset($production->show_times)): ?>
                                        <div class="show-times"><?php echo $production->show_times; ?></div>
                                    <?php endif; ?>
        
                                    <?php if (isset($production->venue)): ?>
                                        <div class="show-venue"><?php echo $production->venue; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="ccwp-row">
                                <?php if (!empty($production->post_content)): ?>
                                    <div class="ccwp-post-content show-summary">
                                        <?php echo apply_filters('the_content', ccwpStripShortCodeGallery(get_the_content())); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($production->press)): ?>
                                    <div class="show-press-quotes">
                                        <h4>Press Highlights</h4>
                                        <p><?php echo $production->press; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Production images -->
                        <?php if (empty($post_gallery)): ?>
                            <div class="ccwp-post-photo-gallery ccwp-production-photo-gallery">
                                <?php echo $post_gallery; ?>
                            </div>
                        <?php endif; ?>
                    </section>
    
                    <?php
                        $production_castcrew = [
                            'cast' => $production->getCastAndCrew('cast'),
                            'crew' => $production->getCastAndCrew('crew'),
                        ];
                    ?>
                    
                    <!-- the production's cast and crew -->
                    <?php if (!empty($production_castcrew['cast']) || !empty($production_castcrew['crew'])): ?>
                        <section class="ccwp-directory ccwp-production-castcrew-listing">
                            <?php foreach ($production_castcrew as $pcc_type => $pcc_array): ?>
                                <div class="ccwp-container production-<?php echo $pcc_type; ?>-list">
                                    <h2><?php echo ucfirst($pcc_type); ?></h2>
                                    
                                    <div class="ccwp-section-content">
                                        <?php /** @var CastAndCrew $castcrew_member */ ?>
                                        <?php foreach ($pcc_array as $castcrew_member): ?>
                                            <div class="ccwp-row production-talent-row">
                                                <?php if (has_post_thumbnail($castcrew_member->ID)): ?>
                                                    <div class="talent-headshot">
                                                        <a href="<?php the_permalink($castcrew_member->ID); ?>">
                                                            <?php echo get_the_post_thumbnail($castcrew_member->ID, 'thumbnail'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
        
                                                <div class="talent-details">
                                                    <div class="talent-name">
                                                        <a href="<?php the_permalink($castcrew_member->ID); ?>">
                                                            <?php echo $castcrew_member->getFullName(); ?>
                                                        </a>
                                                    </div>
            
                                                    <div class="talent-role">
                                                        <p><?php echo $castcrew_member->ccwp_join->role; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; // end is $cast and $crew not empty ?>
                </article>
            </div>
        </main>
    <?php endif; // content is visible ?>
<?php endwhile; endif; // end of the loop & end have_posts() ?>

<?php get_footer(); ?>