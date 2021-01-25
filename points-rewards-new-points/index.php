<?php
/**
 * Plugin Name:       New_Points
 * Description:       Customized WooCommerce Points & Rewards.
 * Version:           1.0.0
 * Author:            Daniel Siyuan Zuo. Neo
 * Text Domain: 	  points-rewards-new-points
 */
 
defined( 'WPINC' ) || die;
 
is_admin() and include_once 'class-new-point-admin.php';

include_once 'template-cart-rewards.php';
include_once 'class-new-point.php';
include_once 'class-new-point-order.php';
include_once 'class-new-point-shop.php';
//include_once 'class-tutsplus-custom-woocommerce-display.php';
 
add_action( 'plugins_loaded', 'new_points_init' );
function new_points_init() {
	is_admin() and new New_Point_Admin();
	new New_Point_Order();
	new New_Point_Shop();
}

