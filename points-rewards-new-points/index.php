<?php
/**
 * Plugin Name:       New_Points
 * Plugin URI:        http://
 * Description:       Customized WooCommerce Points & Rewards.
 * Version:           1.0.0
 * Author:            Neo, Daniel Siyuan Zuo
 * Author URI:        https://
 */
 
defined( 'WPINC' ) || die;
 
include_once 'new-point.php';
//include_once 'class-tutsplus-custom-woocommerce-display.php';
 
add_action( 'plugins_loaded', 'new_points_init' );
/**
 * Start the plugin.
 */
function new_points_init() {

	$admin = new New_Points();

}

