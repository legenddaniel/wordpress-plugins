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
 * @return null
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

add_action('wp_enqueue_scripts', 'add_my_script');
function add_my_script()
{
    wp_enqueue_script(
        'checkScript', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/js/checkScript.js', // this is the location of your script file
        array('jquery'), // this array lists the scripts upon which your script depends
        rand(111, 9999) // If on your side everything works great you can remove this anti-caching code, but I suggest adding this
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


add_action('get_header', function() {
    if(is_page('1489')) {

        function sp_enqueue_script() {
            wp_enqueue_script(
                'sp-custom-script',
                get_stylesheet_directory_uri() . '/searchCartIcon.js',
                array( 'jquery' ), '1.0', true
            );
        }

        add_action( 'wp_enqueue_scripts', 'sp_enqueue_script' );
    }
});


add_action('wp_enqueue_scripts', 'add_my_script4');
function add_my_script4()
{
    wp_enqueue_script(
        'timer', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/timer.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}


add_action('wp_enqueue_scripts', 'add_my_script5');
function add_my_script5()
{
    wp_enqueue_script(
        'whichRow', // name your script so that you can attach other scripts and de-register, etc.
        get_stylesheet_directory_uri() . '/js/check-which-row-is-selected.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}



