<?php

declare(strict_types=1);

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
}

get_header('single');
?>

<?php if (have_posts()): ?>
    <?php while (have_posts()): ?>
        <?php

        the_post();

        $production = Production::make(get_post());
        $productionName = $production->name ?? get_the_title();
        $postStatus = get_post_status($production->getPost());
        $productionCastCrew = [
            'cast' => $production->getCastAndCrew('cast'),
            'crew' => $production->getCastAndCrew('crew'),
        ];
        $ticketLink = $production->getTicketUrl();
        // Production photo gallery
        $gallery = get_post_gallery();
        ?>
        <?php if ($postStatus === 'publish' || $postStatus === 'draft'): ?>
            <div class="ccwp-main">
                <div class="ccwp-main-content-container">
                    <div class="ccwp-breadcrumbs">
                        <a class="ccwp-breadcrumb" href="/">Home</a>
                        <span class="ccwp-breadcrumb-spacer">/</span>
                        <a class="ccwp-breadcrumb" href="/productions">Productions</a>
                        <span class="ccwp-breadcrumb-spacer">/</span>
                        <span class="ccwp-breadcrumb"><?php echo esc_html($productionName); ?></span>
                    </div>

                    <article id="post-<?php echo esc_attr($production->ID); ?>" <?php post_class(); ?>>
                        <section class="ccwp-section ccwp-production-info-section">
                            <!-- info about the production -->
                            <div class="ccwp-container">
                                <div class="ccwp-row mb-4">
                                    <?php if (has_post_thumbnail()): ?>
                                        <div class="ccwp-production-poster">
                                            <?php the_post_thumbnail('full'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="show-info">
                                        <h1 class="ccwp-page-heading">
                                            <?php echo esc_html($productionName); ?>
                                        </h1>
                                        <div class="show-dates-container">
                                            <?php if ($production->getChronologicalState() === 'current'): ?>
                                                <div class="now-showing-label">Now Showing</div>
                                            <?php endif; ?>
                                            <div class="ccwp-row">
                                                <div class="show-dates">
                                                    <?php echo esc_html($production->getFormattedShowDates()); ?>
                                                </div>
                                                <?php if ($ticketLink): ?>
                                                    <a class="ccwp-btn" href="<?php echo esc_attr($ticketLink); ?>" target="_blank">
                                                        Get Tickets
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (isset($production->show_times)): ?>
                                            <p class="show-times"><?php echo esc_html($production->show_times); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($production->venue)): ?>
                                            <p class="show-venue"><?php echo esc_html($production->venue); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ccwp-row mb-4">
                                    <?php if (!empty($production->post_content)): ?>
                                        <div class="ccwp-post-content show-summary">
                                            <?php
                                            // @mago-ignore lint:no-unescaped-output
                                            echo
                                                apply_filters(
                                                    'the_content',
                                                    ccwp_strip_short_code_gallery(get_the_content()),
                                                )
                                            ;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Production images -->
                            <?php if (!empty($gallery)): ?>
                                <div class="ccwp-post-photo-gallery ccwp-production-photo-gallery">
                                    <?php
                                    // @mago-ignore lint:no-unescaped-output
                                    echo $gallery;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </section>

                        <?php if (!empty($productionCastCrew['cast']) || !empty($productionCastCrew['crew'])): ?>
                            <section class="ccwp-section ccwp-directory-section">
                                <?php foreach ($productionCastCrew as $pccType => $pccArray): ?>
                                    <div class="ccwp-directory-list production-<?php echo esc_attr($pccType); ?>-list">
                                        <h2><?php echo esc_html(ucfirst($pccType)); ?></h2>
                                        <div class="ccwp-container">
                                            <?php /** @var CastAndCrew $castcrewMember */ ?>
                                            <?php foreach ($pccArray as $castcrewMember): ?>
                                                <div class="castcrew-wrapper">
                                                    <?php if (has_post_thumbnail($castcrewMember->ID)): ?>
                                                        <div class="castcrew-headshot">
                                                            <a href="<?php the_permalink($castcrewMember->ID); ?>">
                                                                <?php
                                                                // @mago-ignore lint:no-unescaped-output
                                                                echo
                                                                    get_the_post_thumbnail(
                                                                        $castcrewMember->ID,
                                                                        'thumbnail',
                                                                    )
                                                                ;
                                                                ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="castcrew-details">
                                                        <div class="castcrew-name">
                                                            <a href="<?php the_permalink($castcrewMember->ID); ?>">
                                                                <?php echo esc_html($castcrewMember->getFullName()); ?>
                                                            </a>
                                                        </div>
                                                        <div class="castcrew-role">
                                                            <p><?php echo esc_html($castcrewMember->ccwp_join->role); ?></p>
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
    <?php endwhile; // end of the loop ?>
<?php endif; // end have_posts() ?>

<?php

get_footer();
