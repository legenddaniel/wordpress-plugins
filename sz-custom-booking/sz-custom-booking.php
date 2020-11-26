<?php
/*
 * Plugin Name: Custom Booking
 * Version: 1.0.0
 * Description: Custom booking
 * Author: Daniel Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: custom-booking
 */

// Exit if accessed directly
defined('ABSPATH') or exit;

$plugin_dir = plugin_dir_path(__FILE__);

// General utilities
require_once $plugin_dir . 'function.php';

// Admin Dashboard
is_admin() and
require_once $plugin_dir . 'includes/admin.php';

// Booking
require_once $plugin_dir . 'includes/booking.php';

// VIP
require_once $plugin_dir . 'includes/vip.php';

// ---------------Config Area Starts

// For real
define('SINGULAR_ID', 68051);
define('PROMO_ID', 68067);

define('ARCHERY_ID', 70541);
define('AIRSOFT_ID', 70542);
define('COMBO_ID', 70543);

define('VIP_ANNUAL_ID', 68456);
define('VIP_SEMIANNUAL_ID', 68463);

// For test
// define('SINGULAR_ID', 304);
// define('PROMO_ID', 358);
// define('ARCHERY_ID', 291);
// define('AIRSOFT_ID', 292);
// define('COMBO_ID', 293);

// For local
// define('SINGULAR_ID', 7);
// define('PROMO_ID', 8);
// define('ARCHERY_ID', 20);
// define('AIRSOFT_ID', 21);
// define('COMBO_ID', 22);

// ---------------Config Area Ends
