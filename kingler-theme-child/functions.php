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
    if (is_single(SINGULAR_ID)) {
        // Remove product image
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
    }
    if (is_single(PROMO_ID)) {
        // Remove product image
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
    }
}
add_action('template_redirect', 'remake_layout');

 /**
 * Remove 'Additional Information' in Promo Passes 10 + 1 page
 * @return array
 */
function remove_add_info($tabs)
{
    if (!is_single(PROMO_ID)) {
        return $tabs;
    }
    unset($tabs['additional_information']);
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'remove_add_info');





//Do not touch please

add_action('wp_enqueue_scripts', 'add_my_script');
function add_my_script()
{
    wp_enqueue_script(
        'checkScript', // name your script so that you can attach other scripts and de-register, etc.
       get_stylesheet_directory_uri() . '/js/checkScript.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}

add_action('wp_enqueue_scripts', 'add_my_script2');
function add_my_script2()
{
    wp_enqueue_script(
        'formScript', // name your script so that you can attach other scripts and de-register, etc.
       get_stylesheet_directory_uri() . '/js/formScript.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}

add_action('wp_enqueue_scripts', 'add_my_script3');
function add_my_script3()
{
    wp_enqueue_script(
        'passesTestScript', // name your script so that you can attach other scripts and de-register, etc.
       get_stylesheet_directory_uri() . '/passesTestScript.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}
