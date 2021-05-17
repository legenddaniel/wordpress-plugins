<?php

if (!defined('ABSPATH')) {
    exit;
}

class SZ_Foodsto
{
    // cat_ID => yith_ID
    private $map = [
        25 => 4681,
    ];

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'init_assets']);
        add_action('woocommerce_after_shop_loop', [$this, 'add_yith_form']);
    }

    public function init_assets()
    {
        $parenthandle = 'parent-style'; 
        $theme = wp_get_theme();
        wp_enqueue_style($parenthandle, get_template_directory_uri() . '/style.css',
            array(),
            $theme->parent()->get('Version')
        );
        wp_enqueue_style('child-style', get_stylesheet_uri(),
            array($parenthandle),
            $theme->get('Version')
        );
    }

    public function add_yith_form()
    {
        global $wp_query;
        $cat_obj = $wp_query->get_queried_object();
        $cat_ID = $cat_obj->term_id;
        $form = $this->map[$cat_ID];
        if ($form) {
            yith_quick_order_form($form);
        }
    }

}

new SZ_Foodsto();
