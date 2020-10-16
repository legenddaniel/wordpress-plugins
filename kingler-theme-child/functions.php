<?php

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Load styles
 * @return void
 */
function init_styles()
{
    $parenthandle = 'parent-style';
    $theme = wp_get_theme();

    wp_enqueue_style(
        $parenthandle,
        get_template_directory_uri() . '/style.css',
        array(),
        $theme->parent()->get('Version')
    );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_uri(),
        array($parenthandle),
        rand(111, 9999)
    );

    if (is_single(68051)) {
        wp_enqueue_script(
            'layout',
            get_stylesheet_directory_uri() . '/js/layout.js',
            array('jquery'),
            rand(111, 9999)
        );
    }
}
add_action('wp_enqueue_scripts', 'init_styles');

/**
 * Remove 'Singular Passes' product image
 * @return void
 */
function remake_layout()
{
    // Only apply to Singular Passes, should not hard code the ID
    if (!is_single(68051)) {
        return;
    }

    // Remove product image
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
}
add_action('template_redirect', 'remake_layout');
