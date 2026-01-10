<?php get_header(); ?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <p><small><?php echo esc_html(get_the_date()); ?></small></p>
            </header>
            <div class="entry-content">
                <?php the_excerpt(); ?>
            </div>
        </article>
    <?php endwhile; ?>
<?php else : ?>
    <p><?php _e('No posts found.', 'curtain-call-basic'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
