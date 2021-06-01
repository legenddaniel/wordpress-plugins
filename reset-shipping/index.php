<?php
/**
 * Plugin Name:       SZ Reset Shipping
 * Description:       Reset Shipping Method After Input Change
 * Version:           1.0.0
 * Author:            Daniel Siyuan Zuo
 * Text Domain:       reset-shipping
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'sz_enqueue_scripts');
function sz_enqueue_scripts()
{
    wp_enqueue_script(
        'reset-shipping',
        plugin_dir_url(__FILE__) . 'reset-shipping-method.js',
        array('jquery'),
        rand(111, 9999)
    );
}
