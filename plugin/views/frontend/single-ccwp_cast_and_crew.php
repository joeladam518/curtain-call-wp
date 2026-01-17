<?php

declare(strict_types=1);

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Str;

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
    $castcrew = CastAndCrew::make($post);
    $fullName = $castcrew->getFullName();
    $birthplace = $castcrew->getBirthPlace();
    ?>
    <div class="ccwp-main">
        <div class="ccwp-main-content-container">
            <div class="ccwp-breadcrumbs">
                <a class="ccwp-breadcrumb" href="/">
                    <?php esc_html_e('Home', CCWP_TEXT_DOMAIN); ?>
                </a>
                <span class="ccwp-breadcrumb-spacer">/</span>
                <a class="ccwp-breadcrumb" href="/cast-and-crew">
                    <?php esc_html_e('Cast &amp; Crew', CCWP_TEXT_DOMAIN); ?>
                </a>
                <span class="ccwp-breadcrumb-spacer">/</span>
                <span class="ccwp-breadcrumb">
                    <?php echo esc_html($fullName); ?>
                </span>
            </div>

            <article id="post-<?php echo esc_attr($castcrew->ID); ?>" <?php post_class(); ?>>
                <section class="ccwp-section castcrew-profile-section">
                    <?php if ($castcrew->hasImage()): ?>
                        <div class="castcrew-headshot">
                            <?php $castcrew->theImage('full'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="castcrew-profile">
                        <h1 class="ccwp-page-heading"
                            <?php echo esc_html($fullName); ?>
                        </h1>

                        <?php if (isset($castcrew->self_title)): ?>
                            <h3 class="castcrew-title">
                                <?php echo esc_html($castcrew->self_title); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($birthplace): ?>
                            <p class="castcrew-birthplace">
                                <?php echo esc_html($birthplace); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (isset($castcrew->fun_fact)): ?>
                            <p class="castcrew-fun-fact">
                                <?php echo esc_html($castcrew->fun_fact); ?>
                            </p>
                        <?php endif; ?>

                        <div class="castcrew-bio">
                            <?php if (!empty($castcrew->post_content)): ?>
                                <?php $castcrew->theContent(); ?>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($castcrew->website_link) || $castcrew->hasSocialMedia()): ?>
                            <h4 class="connect-with-castcrew"><?php printf(
                                __('Connect with %s!', CCWP_TEXT_DOMAIN),
                                esc_html($castcrew->name_first)
                            ); ?></h4>
                        <?php endif; ?>

                        <?php if (isset($castcrew->website_link)): ?>
                            <div class="castcrew-website">
                                <a href="<?php echo esc_url($castcrew->website_link); ?>">
                                    <?php echo esc_html(Str::stripHttp($castcrew->website_link)); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($castcrew->hasSocialMedia()): ?>
                            <div class="castcrew-social">
                                <?php if (isset($castcrew->facebook_link)): ?>
                                    <a href="<?php echo esc_url($castcrew->facebook_link); ?>">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (isset($castcrew->instagram_link)): ?>
                                    <a href="<?php echo esc_url($castcrew->instagram_link); ?>">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (isset($castcrew->twitter_link)): ?>
                                    <a href="<?php echo esc_url($castcrew->twitter_link); ?>">
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

                <?php if (!empty($productions)): ?>
                    <section class="ccwp-section ccwp-directory-section">
                        <div class="ccwp-directory-list castcrew-production-list">
                            <h2><?php esc_html_e('Productions', CCWP_TEXT_DOMAIN); ?></h2>

                            <div class="ccwp-container">
                                <?php foreach ($productions as $production): ?>
                                    <?php if (in_array($production->ID, $shownProductions, true)): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>

                                    <div class="production-wrapper">
                                        <div class="production-poster">
                                            <?php if ($production->hasImage()): ?>
                                                <a href="<?php $production->thePermalink(); ?>">
                                                    <?php $production->theImage('full'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="production-details">
                                            <div class="production-name">
                                                <a href="<?php $production->thePermalink(); ?>">
                                                    <?php echo esc_html($production->name); ?>
                                                </a>
                                            </div>

                                            <div class="castcrew-role">
                                                <p><?php
                                                echo esc_html(implode(', ', $rolesByPid[$production->ID] ?? []));
                                                ?></p>
                                            </div>

                                            <div class="production-dates">
                                                <p><?php echo esc_html($production->getFormattedShowDates()); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $shownProductions[] = $production->ID; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </article>
        </div>
    </div>
<?php endwhile; ?>

<?php
get_footer('single');
