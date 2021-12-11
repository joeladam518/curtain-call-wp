<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Str;

get_header('single');
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : ?>
        <?php
            the_post();

            $castcrew = CastAndCrew::make(get_post());
            $fullName = $castcrew->getFullName();
            $birthplace = $castcrew->getBirthPlace();
        ?>

        <?php if (get_post_status($castcrew->getPost()) == 'publish') : ?>
            <div class="ccwp-main">
                <div class="ccwp-main-content-container">
                    <div class="ccwp-breadcrumbs">
                        <a class="ccwp-breadcrumb" href="/">Home</a>
                        <span class="ccwp-breadcrumb-spacer">/</span>
                        <a class="ccwp-breadcrumb" href="/cast-and-crew">Cast &amp; Crew</a>
                        <span class="ccwp-breadcrumb-spacer">/</span>
                        <span class="ccwp-breadcrumb"><?php echo $fullName; ?></span>
                    </div>

                    <article id="post-<?php echo $castcrew->ID; ?>" <?php post_class(); ?>>
                        <section class="ccwp-section castcrew-profile-section">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="castcrew-headshot">
                                    <?php the_post_thumbnail('full'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="castcrew-profile">
                                <h1 class="ccwp-page-heading">
                                    <?php echo $fullName; ?>
                                </h1>

                                <?php if (isset($castcrew->self_title)) : ?>
                                    <h3 class="castcrew-title">
                                        <?php echo $castcrew->self_title; ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if ($birthplace != '') : ?>
                                    <p class="castcrew-birthplace">
                                        <?php echo $birthplace; ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (isset($castcrew->fun_fact)) : ?>
                                    <p class="castcrew-fun-fact">
                                        <?php echo $castcrew->fun_fact; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="castcrew-bio">
                                    <?php if (!empty($castcrew->post_content)) : ?>
                                        <?php the_content(); ?>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($castcrew->website_link) || $castcrew->hasSocialMedia()) : ?>
                                    <h4 class="connect-with-castcrew">
                                        Connect with <?php echo $castcrew->name_first; ?>!
                                    </h4>
                                <?php endif; ?>

                                <?php if (isset($castcrew->website_link)) :?>
                                    <div class="castcrew-website">
                                        <a href="<?php echo $castcrew->website_link; ?>">
                                            <?php echo Str::stripHttp($castcrew->website_link); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($castcrew->hasSocialMedia()) : ?>
                                    <div class="castcrew-social">
                                        <?php if (isset($castcrew->facebook_link)) : ?>
                                            <a href="<?php echo $castcrew->facebook_link; ?>">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (isset($castcrew->instagram_link)) : ?>
                                            <a href="<?php echo $castcrew->instagram_link; ?>">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (isset($castcrew->twitter_link)) : ?>
                                            <a href="<?php echo $castcrew->twitter_link; ?>">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <?php
                            $shownProductions = [];
                            $productions = $castcrew->getProductions();
                            $rolesByPid = CastAndCrew::rolesByProductionId($productions);
                        ?>

                        <?php if (!empty($productions)) : ?>
                            <section class="ccwp-section ccwp-directory-section">
                                <div class="ccwp-directory-list castcrew-production-list">
                                    <h2>Productions</h2>

                                    <div class="ccwp-container">
                                        <?php foreach ($productions as $production) : ?>
                                            <?php if (in_array($production->ID, $shownProductions)) : ?>
                                                <?php continue; ?>
                                            <?php endif; ?>

                                            <div class="production-wrapper">
                                                <?php if (has_post_thumbnail($production->ID)) : ?>
                                                    <div class="production-poster">
                                                        <a href="<?php the_permalink($production->ID); ?>">
                                                            <?php echo get_the_post_thumbnail($production->ID, 'full'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="production-details">
                                                    <div class="production-name">
                                                        <a href="<?php the_permalink($production->ID); ?>">
                                                            <?php echo $production->name; ?>
                                                        </a>
                                                    </div>

                                                    <div class="castcrew-role">
                                                        <p><?php echo implode(', ', $rolesByPid[$production->ID]); ?></p>
                                                    </div>

                                                    <div class="production-dates">
                                                        <p><?php echo $production->getFormattedShowDates(); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php $shownProductions[] = $production->ID; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>
                        <?php endif; // productions array not empty ?>
                    </article>
                </div>
            </div>
        <?php endif; // content is visible ?>
    <?php endwhile; // end of the loop ?>
<?php endif; // end of have_posts() ?>

<?php get_footer(); ?>
