<?php
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

get_header('single');
?>

<?php if (have_posts()) :
    while (have_posts()) :
        the_post(); ?>
            <?php
            $production = Production::make(get_post());
            $productionName = $production->name ?? get_the_title();
            $ticketLink = $production->getTicketUrl();

            // Production photo gallery
            $gallery = get_post_gallery();
            ?>

            <?php if (get_post_status($production->getPost()) == 'publish') : ?>
        <div class="ccwp-main">
            <div class="ccwp-main-content-container">
                <div class="ccwp-breadcrumbs">
                    <a class="ccwp-breadcrumb" href="/">Home</a>
                    <span class="ccwp-breadcrumb-spacer">/</span>
                    <a class="ccwp-breadcrumb" href="/productions">Productions</a>
                    <span class="ccwp-breadcrumb-spacer">/</span>
                    <span class="ccwp-breadcrumb"><?php echo $productionName; ?></span>
                </div>

                <article id="post-<?php echo $production->ID; ?>" <?php post_class(); ?>>
                    <section class="ccwp-section ccwp-production-info-section">
                        <!-- info about the production -->
                        <div class="ccwp-container">
                            <div class="ccwp-row mb-4">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="ccwp-production-poster">
                                        <?php the_post_thumbnail('full'); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="show-info">
                                    <h1 class="ccwp-page-heading">
                                        <?php echo $productionName; ?>
                                    </h1>

                                    <div class="show-dates-container">
                                        <?php if ($production->getChronologicalState() == 'current') : ?>
                                            <div class="now-showing-label">Now Showing</div>
                                        <?php endif; ?>
                                        <div class="ccwp-row">
                                            <div class="show-dates">
                                                <?php echo $production->getFormattedShowDates(); ?>
                                            </div>
                                            <?php if ($ticketLink) : ?>
                                                <a class="ccwp-btn" href="<?php echo $ticketLink ?>" target="_blank">
                                                    Get Tickets
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (isset($production->show_times)) : ?>
                                        <p class="show-times"><?php echo $production->show_times; ?></p>
                                    <?php endif; ?>

                                    <?php if (isset($production->venue)) : ?>
                                        <p class="show-venue"><?php echo $production->venue; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ccwp-row mb-4">
                                <?php if (!empty($production->post_content)) : ?>
                                    <div class="ccwp-post-content show-summary">
                                        <?php echo apply_filters('the_content', ccwpStripShortCodeGallery(get_the_content())); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Production images -->
                        <?php if (!empty($gallery)) : ?>
                            <div class="ccwp-post-photo-gallery ccwp-production-photo-gallery">
                                <?php echo $gallery; ?>
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
                    <?php if (!empty($production_castcrew['cast']) || !empty($production_castcrew['crew'])) : ?>
                        <section class="ccwp-section ccwp-directory-section">
                            <?php foreach ($production_castcrew as $pcc_type => $pcc_array) : ?>
                                <div class="ccwp-directory-list production-<?php echo $pcc_type; ?>-list">
                                    <h2><?php echo ucfirst($pcc_type); ?></h2>

                                    <div class="ccwp-container">
                                        <?php /** @var CastAndCrew $castcrew_member */ ?>
                                        <?php foreach ($pcc_array as $castcrew_member) : ?>
                                            <div class="castcrew-wrapper">
                                                <?php if (has_post_thumbnail($castcrew_member->ID)) : ?>
                                                    <div class="castcrew-headshot">
                                                        <a href="<?php the_permalink($castcrew_member->ID); ?>">
                                                            <?php echo get_the_post_thumbnail($castcrew_member->ID, 'thumbnail'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="castcrew-details">
                                                    <div class="castcrew-name">
                                                        <a href="<?php the_permalink($castcrew_member->ID); ?>">
                                                            <?php echo $castcrew_member->getFullName(); ?>
                                                        </a>
                                                    </div>

                                                    <div class="castcrew-role">
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
        </div>
            <?php endif; // content is visible ?>
    <?php endwhile;
endif; // end of the loop & end have_posts() ?>

<?php get_footer(); ?>
