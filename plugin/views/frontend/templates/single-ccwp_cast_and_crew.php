<?php if (!defined('ABSPATH')) die;

use CurtainCallWP\PostTypes\CastAndCrew;

get_header( 'single' );
?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php
        $post_classes = []; // You can dynamically add classes to the article by adding to this array...
        $castcrew = CastAndCrew::make(get_post());
        $full_name = $castcrew->getFullName();
        $birthplace = $castcrew->getBirthPlace();
    ?>

    <?php if (get_post_status($castcrew->getPost()) == 'publish'): ?>
        <main id="main" class="ccwp-main-content-area">
            <div id="content" class="ccwp-post-container">
                <div class="ccwp-post-breadcrumbs">
                    <a href="/">Home</a>&nbsp;&nbsp;/&nbsp;
                    <a href="/cast-and-crew">Cast &amp; Crew</a>&nbsp;&nbsp;/&nbsp;
                    <span><?php echo $full_name; ?></span>
                </div>
                
                <article id="post-<?php echo $castcrew->ID; ?>" <?php post_class($post_classes); ?>>
                    <div class="ccwp-post-details-container">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="talent-profile-image">
                                <?php the_post_thumbnail('full'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="talent-profile-details">
                            <h1><?php echo $full_name; ?></h1>
                            
                            <?php if (isset($castcrew->self_title)): ?>
                                <p class="talent-title"><?php echo $castcrew->self_title; ?></p>
                            <?php endif; ?>
                            
                            <?php if ($birthplace != ''): ?>
                                <p class="talent-birthplace"><?php echo $birthplace; ?></p>
                            <?php endif; ?>
                            
                            <?php if (isset($castcrew->fun_fact)) : ?>
                                <p class="talent-fun-fact"><?php echo $castcrew->fun_fact; ?></p>
                            <?php endif; ?>
                            
                            <div class="talent-bio">
                                <?php if (!empty($castcrew->post_content)) : ?>
                                    <?php the_content(); ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($castcrew->website_link) || $castcrew->hasSocialMedia()): ?>
                                <h4 class="connect-with-talent-title">
                                    Connect with <?php echo $castcrew->name_first; ?>!
                                </h4>
                            <?php endif; ?>
                            
                            <?php if (isset($castcrew->website_link)) :?>
                                <div class="talent-website-link">
                                    <a href="http://<?php echo $castcrew->website_link; ?>">
                                        <?php echo $castcrew->website_link; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($castcrew->hasSocialMedia()): ?>
                                <div class="talent-social">
                                    <?php if (isset($castcrew->facebook_link)): ?>
                                        <a href="http://<?php echo $castcrew->facebook_link; ?>">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($castcrew->instagram_link)): ?>
                                        <a href="http://<?php echo $castcrew->instagram_link; ?>">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($castcrew->twitter_link)): ?>
                                        <a href="http://<?php echo $castcrew->twitter_link; ?>">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php
                        $productions_shown = [];
                        $productions = $castcrew->getProductions();
                        $roles_by_pid = CastAndCrew::rolesByProductionId($productions);
                    ?>
                    
                    <?php if (!empty($productions)): ?>
                        <div class="ccwp-detail-page-cross-db-directory cast-prod-directory">
                            <h2>Productions</h2>
                            
                            <section class="cast-productions-section">
                                <div class="cast-prod-flex-container">
                                    <?php foreach($productions as $production): ?>
                                        <?php if (in_array($production->ID, $productions_shown)) continue; ?>
                                        
                                        <div class="cast-prod-show">
                                            <?php if (has_post_thumbnail($production->ID)): ?>
                                                <div class="cast-prod-show-poster">
                                                    <a href="<?php the_permalink($production->ID); ?>">
                                                        <?php echo get_the_post_thumbnail($production->ID, 'full'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="cast-prod-show-info">
                                                <div class="cast-prod-show-title">
                                                    <a href="<?php the_permalink($production->ID); ?>">
                                                        <?php echo $production->name; ?>
                                                    </a>
                                                </div>
                                                
                                                <div class="cast-prod-role">
                                                    <p><?php echo implode(', ', $roles_by_pid[$production->ID]); ?></p>
                                                </div>
                                                
                                                <div class="cast-prod-dates">
                                                    <p><?php echo $production->getFormattedShowDates(); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php $productions_shown[] = $production->ID; ?>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </div>
                    <?php endif; // productions array not empty ?>
                </article>
            </>
        </main>
    <?php endif; // content is visible ?>
<?php endwhile; endif; // end of the loop & end of have_posts() ?>

<?php get_footer(); ?>
