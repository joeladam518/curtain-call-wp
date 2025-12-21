<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header" role="banner">
    <div class="container">
        <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></p>
        <?php if (get_bloginfo('description')) : ?>
            <p class="site-description"><?php bloginfo('description'); ?></p>
        <?php endif; ?>
        <?php if (has_nav_menu('primary')) : ?>
            <nav class="primary-nav" aria-label="<?php esc_attr_e('Primary', 'curtain-call-basic'); ?>">
                <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false]); ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
<main class="site-main" role="main">
