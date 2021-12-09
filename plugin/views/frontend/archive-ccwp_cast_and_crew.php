<?php
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Str;

$query = CastAndCrew::getPosts();
$alphaIndexes = CastAndCrew::getAlphaIndexes($query);
$currentAlphaIndex = null;
$previousAlphaIndex = null;

get_header();
?>

<div class="ccwp-main">
    <div class="ccwp-main-content-container">
        <h1 class="ccwp-page-heading">Cast and Crew</h1>
        <?php if (!$query->have_posts()) : ?>
            <div class="ccwp-container">
                <h2>Sorry!</h2>
                <p>There are currently no cast or crew members in our directory. Please check back soon!</p>
            </div>
        <?php else : ?>
            <div class="ccwp-alphabet-navigation">
                <?php foreach (Str::alphabet() as $letter) : ?>
                    <?php if (in_array($letter, $alphaIndexes)) : ?>
                        <a href="#<?php echo $letter; ?>"><?php echo $letter; ?></a>
                    <?php else : ?>
                        <span><?php echo $letter; ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="ccwp-container ccwp-alphabet-container">
                <?php while ($query->have_posts()) : ?>
                    <?php
                        $query->the_post();
                        $castcrew = CastAndCrew::make(get_post());
                        $castcrew_permalink = get_post_permalink($castcrew->getPost());
                    ?>

                    <?php if ($castcrew->post_status == 'publish' && isset($castcrew->name_last)) : ?>
                        <?php $currentAlphaIndex = Str::firstLetter($castcrew->name_last, 'upper'); ?>
                        <?php if ($currentAlphaIndex !== $previousAlphaIndex) : ?>
                            <?php if ($previousAlphaIndex !== null) : ?>
                                </div>
                            <?php endif; ?>
                            <h3 class="ccwp-alphabet-header" id="<?php echo $currentAlphaIndex; ?>">
                                <?php echo $currentAlphaIndex; ?>
                            </h3>
                            <div class="ccwp-row mb-5">
                        <?php endif; ?>

                        <div class="castcrew-wrapper">
                            <?php if (has_post_thumbnail($castcrew->getPost())) : ?>
                                <div class="castcrew-headshot">
                                    <a href="<?php echo $castcrew_permalink; ?>">
                                        <?php echo get_the_post_thumbnail($castcrew->getPost(), 'thumbnail'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="castcrew-details">
                                <h3 class="castcrew-name">
                                    <a href="<?php echo $castcrew_permalink; ?>">
                                        <?php echo $castcrew->getFullName(); ?>
                                    </a>
                                </h3>
                                <h5 class="castcrew-self-title">
                                    <?php echo $castcrew->self_title; ?>
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

<?php get_footer(); ?>
