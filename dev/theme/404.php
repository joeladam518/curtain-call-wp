<?php get_header(); ?>

<article class="error-404 not-found">
    <header>
        <h1><?php _e('Page not found', 'curtain-call-basic'); ?></h1>
    </header>
    <div class="page-content">
        <p><?php _e('It looks like nothing was found at this location.', 'curtain-call-basic'); ?></p>
        <p><a href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php _e('Back to home', 'curtain-call-basic'); ?></a></p>
    </div>
    </article>

<?php get_footer(); ?>
