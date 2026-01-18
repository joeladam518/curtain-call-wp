<?php
// Theme setup
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus([
        'primary' => __('Primary Menu', 'curtain-call-basic'),
    ]);
});

// Enqueue styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('curtain-call-basic-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
});
