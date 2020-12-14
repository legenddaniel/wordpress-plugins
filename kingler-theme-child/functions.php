<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load styles
 * @return null
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

    wp_enqueue_script(
        'screets-icon-style',
        get_stylesheet_directory_uri() . '/screets-icon-style.js',
        [],
        rand(111, 9999)
    );

    if (is_single(SINGULAR_ID)) {
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
 * Remove product image on single product pages
 * @return null
 */
function sz_remove_product_image()
{
    $pages_without_img = [SINGULAR_ID, PROMO_ID, VIP_PURCHASE_ID];
    foreach ($pages_without_img as $page) {
        if (is_single($page)) {
            remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
            return;
        }
    }
}
add_action('template_redirect', 'sz_remove_product_image');

/**
 * Remove 'Additional Information'
 * @return array
 */
function sz_remove_add_info($tabs)
{
    $pages_without_add_info = [PROMO_ID, VIP_PURCHASE_ID];
    foreach ($pages_without_add_info as $page) {
        if (is_single($page)) {
            unset($tabs['additional_information']);
            break;
        }
    }

    return $tabs;
}
add_filter('woocommerce_product_tabs', 'sz_remove_add_info');

/*add_filter('get_terms', 'ts_get_subcategory_terms', 10, 3);
function ts_get_subcategory_terms($terms, $taxonomies, $args)
{
$new_terms = array();
if (in_array('product_cat', $taxonomies) && !is_admin()) {
foreach ($terms as $key => $term) {
if (!in_array($term->slug, array('firearms'))) {
$new_terms[] = $term;
}}
$terms = $new_terms;
}
return $terms;
}*/
/*
function exclude_product_cat_children($wp_query)
{
if (isset($wp_query->query_vars['hidden']) && $wp_query->is_main_query()) {
$wp_query->set('tax_query', array(
array(
'taxonomy' => 'hidden',
'field' => 'slug',
'terms' => $wp_query->query_vars['hidden'],
'include_children' => false,
),
)
);
}
}
add_filter('pre_get_posts', 'exclude_product_cat_children');*/

//Do not touch please

add_action('wp_enqueue_scripts', 'add_my_script2');
function add_my_script2()
{
    wp_enqueue_script(
        'formScript', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/js/formScript.js', // this is the location of your script file
        array('jquery'), // this array lists the scripts upon which your script depends
        rand(111, 9999)
    );
}

add_action('get_header', function () {
    if (is_page('1489')) {

        function sp_enqueue_script()
        {
            wp_enqueue_script(
                'sp-custom-script',
                get_stylesheet_directory_uri() . '/searchCartIcon.js',
                array('jquery'), '1.0', true
            );
        }

        add_action('wp_enqueue_scripts', 'sp_enqueue_script');
    }
});

add_action('wp_enqueue_scripts', 'add_my_script4');
function add_my_script4()
{
    wp_enqueue_script(
        'timer', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/timer.js', // this is the location of your script file
        array('jquery'),
        rand(111, 9999) // this array lists the scripts upon which your script depends
    );
}

add_action('wp_enqueue_scripts', 'add_my_script5');
function add_my_script5()
{
    wp_enqueue_script(
        'whichRow', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/js/check-which-row-is-selected.js', // this is the location of your script file
        array('jquery'),
        rand(111, 9999) // this array lists the scripts upon which your script depends
    );
}

add_action('wp_enqueue_scripts', 'add_my_script6');
function add_my_script6()
{
    wp_enqueue_script(
        'searchButton', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/js/searchButtonDisabled.js', // this is the location of your script file
        array('jquery'),
        rand(111, 9999) // this array lists the scripts upon which your script depends
    );
}
