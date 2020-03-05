<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

use CurtainCallWP\Helpers\CurtainCallHelper;
use CurtainCallWP\PostTypes\CastAndCrew;

/** @var WP_Query $result */
$result = CastAndCrew::getPosts();

$alphabet = CurtainCallHelper::getAlphabet();
$alpha_indexes = CastAndCrew::getAlphaIndexes($result);

$current_alpha_index = null;
$previous_alpha_index = null;

get_header();
?>

<div id="content" class="ccwp-cast-and-crew-page" role="main">
    <h1>Cast and Crew</h1>
    
    <?php if (!$result->have_posts()) : ?>
        <p>Sorry! There are currently no cast or crew members in our directory. Please check back soon!</p>
    <?php else: ?>
        <div class="ccwp-alphabet-navigation">
            <?php foreach ($alphabet as $letter): ?>
                <?php if (in_array($letter, $alpha_indexes)): ?>
                    <a href="#<?php echo $letter; ?>"><?php echo $letter; ?></a>
                <?php else: ?>
                    <span><?php echo $letter; ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    
        <div class="ccwp-container">
        <?php while ($result->have_posts()): $result->the_post(); ?>
            <?php
                /** @var WP_Post $current_post */
                $current_post = get_post();
                /** @var CastAndCrew $castcrew */
                $castcrew = CastAndCrew::make($current_post);
                $castcrew_permalink = get_post_permalink($castcrew->getPost());
            ?>

            <?php if ($castcrew->post_status == 'publish' && isset($castcrew->name_last)): ?>
                <?php $current_alpha_index = strtoupper(substr($castcrew->name_last, 0, 1)); ?>
            
                <?php if ($current_alpha_index != $previous_alpha_index): ?>
                    <?php if ($previous_alpha_index !== null): ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="ccwp-alphabet-header" id="<?php echo $current_alpha_index; ?>"><?php echo $current_alpha_index; ?></h3>
                    <div class="ccwp-row">
                <?php endif; ?>
                    
                <div class="castcrew-wrapper">
                    <?php if (has_post_thumbnail($castcrew->getPost())): ?>
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
    <?php endif; // if / else is there content?? ?>
</div>

<?php get_footer(); ?>
