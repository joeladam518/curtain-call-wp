<?php get_header(); ?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="content">
                <?php the_content(); ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else : ?>
    <p><?php _e('No posts found.', 'curtain-call-basic'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
