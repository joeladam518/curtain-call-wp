<?php
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Support\Str;

$result = CastAndCrew::getPosts();

$alphabet = Str::alphabet();
$alpha_indexes = CastAndCrew::getAlphaIndexes($result);

$current_alpha_index = null;
$previous_alpha_index = null;

get_header();
?>

<div class="ccwp-main">
    <div class="ccwp-main-content-container">
        <h1 class="ccwp-page-heading">Cast and Crew</h1>
        <?php if (!$result->have_posts()) : ?>
            <div class="ccwp-container">
                <h2>Sorry!</h2>
                <p>There are currently no cast or crew members in our directory. Please check back soon!</p>
            </div>
        <?php else : ?>
            <div class="ccwp-alphabet-navigation">
                <?php foreach ($alphabet as $letter) : ?>
                    <?php if (in_array($letter, $alpha_indexes)) : ?>
                        <a href="#<?php echo $letter; ?>"><?php echo $letter; ?></a>
                    <?php else : ?>
                        <span><?php echo $letter; ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="ccwp-container ccwp-alphabet-container">
            <?php while ($result->have_posts()) :
                $result->the_post(); ?>
                <?php
                    $castcrew = CastAndCrew::make(get_post());
                    $castcrew_permalink = get_post_permalink($castcrew->getPost());
                ?>

                <?php if ($castcrew->post_status == 'publish' && isset($castcrew->name_last)) : ?>
                    <?php $current_alpha_index = Str::firstLetter($castcrew->name_last, 'upper') ?>

                    <?php if ($current_alpha_index != $previous_alpha_index) : ?>
                        <?php if ($previous_alpha_index !== null) : ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="ccwp-alphabet-header" id="<?php echo $current_alpha_index; ?>"><?php echo $current_alpha_index; ?></h3>
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
                            <h5 class="castcrew-self-title"><?php echo $castcrew->self_title; ?></h5>
                        </div>
                    </div>

                    <?php $previous_alpha_index = $current_alpha_index; ?>
                <?php endif; // content is visible ?>
            <?php endwhile; // while loop ?>
            </div>
        <?php endif; // have_posts ?>
    </div>
</div>

<?php get_footer(); ?>
