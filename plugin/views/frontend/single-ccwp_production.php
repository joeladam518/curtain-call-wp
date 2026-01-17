<?php

declare(strict_types=1);

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;
use Illuminate\Support\Collection;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

get_header('single');
?>

<?php while (have_posts()): ?>
    <?php
    the_post();
    /** @var WP_Post $post */
    $post = get_post();
    $production = Production::make($post);
    $productionName = $production->name ?? get_the_title();
    /** @var array{cast: CastAndCrew[], crew: CastAndCrew[]} $productionCastCrew */
    $productionCastCrew = collect($production->getCastAndCrew())
        ->groupBy('ccwp_join.type')
        ->map(fn(Collection $grouped) => $grouped->values()->all())
        ->all();
    $ticketLink = $production->getTicketUrl();
    ?>
    <div class="ccwp-main">
        <div class="ccwp-main-content-container">
            <div class="ccwp-breadcrumbs">
                <a class="ccwp-breadcrumb" href="/">
                    <?php esc_html_e('Home', CCWP_TEXT_DOMAIN); ?>
                </a>
                <span class="ccwp-breadcrumb-spacer">/</span>
                <a class="ccwp-breadcrumb" href="/productions">
                    <?php esc_html_e('Productions', CCWP_TEXT_DOMAIN); ?>
                </a>
                <span class="ccwp-breadcrumb-spacer">/</span>
                <span class="ccwp-breadcrumb">
                    <?php echo esc_html($productionName); ?>
                </span>
            </div>

            <article id="post-<?php echo esc_attr($production->ID); ?>" <?php post_class(); ?>>
                <section class="ccwp-section ccwp-production-info-section">
                    <!-- info about the production -->
                    <div class="ccwp-container">
                        <div class="ccwp-row mb-4">
                            <?php if ($production->hasImage()): ?>
                                <div class="ccwp-production-poster">
                                    <?php $production->theImage('full'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="show-info">
                                <h1 class="ccwp-page-heading">
                                    <?php echo esc_html($productionName); ?>
                                </h1>
                                <div class="show-dates-container">
                                    <?php if ($production->getChronologicalState() === 'current'): ?>
                                        <div class="now-showing-label"><?php _e('Now Showing', CCWP_TEXT_DOMAIN); ?></div>
                                    <?php endif; ?>
                                    <div class="ccwp-row">
                                        <div class="show-dates">
                                            <?php echo esc_html($production->getFormattedShowDates()); ?>
                                        </div>
                                        <?php if ($ticketLink): ?>
                                            <a class="ccwp-btn" href="<?php echo esc_url($ticketLink); ?>" target="_blank">
                                                <?php _e('Get Tickets', CCWP_TEXT_DOMAIN); ?>
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
                                    <?php $production->theContentWithoutGallery(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Production images -->
                    <?php if (!empty($gallery)): ?>
                        <div class="ccwp-post-photo-gallery ccwp-production-photo-gallery">
                            <?php $production->theGallery(); ?>
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
                                            <?php if ($castcrewMember->hasImage()): ?>
                                                <div class="castcrew-headshot">
                                                    <a href="<?php $castcrewMember->thePermalink(); ?>">
                                                        <?php $castcrewMember->theImage(); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <div class="castcrew-details">
                                                <div class="castcrew-name">
                                                    <a href="<?php $castcrewMember->thePermalink(); ?>">
                                                        <?php echo esc_html($castcrewMember->getFullName()); ?>
                                                    </a>
                                                </div>
                                                <div class="castcrew-role">
                                                    <p>
                                                        <?php echo esc_html($castcrewMember->getJoinRole() ?? ''); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            </article>
        </div>
    </div>
<?php endwhile; ?>

<?php
get_footer('single');
