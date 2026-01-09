<?php

declare(strict_types=1);

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Str;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
}

$query = CastAndCrew::getPosts();
$alphaIndexes = CastAndCrew::getAlphaIndexes($query);
$currentAlphaIndex = null;
$previousAlphaIndex = null;

get_header();
?>

<div class="ccwp-main">
    <div class="ccwp-main-content-container">
        <h1 class="ccwp-page-heading">
            <?php esc_html_e('Cast and Crew', CCWP_TEXT_DOMAIN); ?>
        </h1>
        <?php if (!$query->have_posts()): ?>
            <div class="ccwp-container">
                <h2><?php esc_html_e('Sorry!', CCWP_TEXT_DOMAIN); ?></h2>
                <p>
                    <?php esc_html_e(
                        'There are currently no cast or crew members in our directory. ',
                        CCWP_TEXT_DOMAIN,
                    ); ?>
                    <?php esc_html_e('Please check back soon!', CCWP_TEXT_DOMAIN); ?></p>
                </p>
            </div>
        <?php else: ?>
            <div class="ccwp-alphabet-navigation">
                <?php foreach (Str::alphabet() as $letter): ?>
                    <?php if (in_array($letter, $alphaIndexes, true)): ?>
                        <a href="#<?php echo esc_url($letter); ?>"><?php echo esc_html($letter); ?></a>
                    <?php else: ?>
                        <span><?php echo esc_html($letter); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="ccwp-container ccwp-alphabet-container">
                <?php while ($query->have_posts()): ?>
                    <?php
                    $query->the_post();
                    /** @var WP_Post $post */
                    $post = get_post();
                    /** @var CastAndCrew $castcrew */
                    $castcrew = CastAndCrew::make($post);
                    $castcrew_permalink = get_post_permalink($castcrew->getPost()) ?: '';
                    ?>
                    <?php if ($castcrew->post_status === 'publish' && isset($castcrew->name_last)): ?>
                        <?php $currentAlphaIndex = Str::firstLetter($castcrew->name_last, 'upper'); ?>
                        <?php if ($currentAlphaIndex !== $previousAlphaIndex): ?>
                            <?php if ($previousAlphaIndex !== null): ?>
                                </div>
                            <?php endif; ?>
                            <h3 class="ccwp-alphabet-header" id="<?php echo esc_attr($currentAlphaIndex); ?>">
                                <?php echo esc_html($currentAlphaIndex); ?>
                            </h3>
                            <div class="ccwp-row mb-5">
                        <?php endif; ?>

                        <div class="castcrew-wrapper">
                            <?php if (has_post_thumbnail($castcrew->getPost())): ?>
                                <div class="castcrew-headshot">
                                    <a href="<?php echo esc_url($castcrew_permalink); ?>">
                                    <?php
                                    // @mago-ignore lint:no-unescaped-output
                                    echo get_the_post_thumbnail($castcrew->getPost(), 'thumbnail');
                                    ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="castcrew-details">
                                <h3 class="castcrew-name">
                                    <a href="<?php echo esc_url($castcrew_permalink); ?>">
                                        <?php echo esc_html($castcrew->getFullName()); ?>
                                    </a>
                                </h3>
                                <h5 class="castcrew-self-title">
                                    <?php echo esc_html($castcrew->self_title); ?>
                                </h5>
                            </div>
                        </div>

                        <?php $previousAlphaIndex = $currentAlphaIndex; ?>
                    <?php endif; // content is visible ?>
                <?php endwhile; // while loop ?>
            </div>
        <?php endif; // have_posts ?>
    </div>
</div>

<?php

get_footer();
