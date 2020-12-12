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

// For all
define('PROMO_QTY', 11);
define('GUEST_QTY', 10);
define('VIP_REG_QTY', 2);
define('VIP_888_QTY', 8);

// For real
define('SINGULAR_ID', 68051);
define('PROMO_ID', 68067);

define('ARCHERY_ID', 70541);
define('AIRSOFT_ID', 70542);
define('COMBO_ID', 70543);

define('ARCHERY_ACT_ID', 70057);
define('AIRSOFT_ACT_ID', 70055);
define('COMBO_ACT_ID', 70056);

define('ARCHERY_PROMO_ID', 68068);
define('AIRSOFT_PROMO_ID', 68069);
define('COMBO_PROMO_ID', 68070);

define('VIP_ANNUAL_ID', 68456);
define('VIP_SEMIANNUAL_ID', 68463);
define('VIP_888_ANNUAL_ID', 70749);

define('VIP_PURCHASE_ID', 70868);

// For test
// define('SINGULAR_ID', 2996);
// define('PROMO_ID', 3004);

// define('ARCHERY_ID', 2997);
// define('AIRSOFT_ID', 2998);
// define('COMBO_ID', 2999);

// define('ARCHERY_ACT_ID', 3018);
// define('AIRSOFT_ACT_ID', 3019);
// define('COMBO_ACT_ID', 3020);

// define('ARCHERY_PROMO_ID', 3005);
// define('AIRSOFT_PROMO_ID', 3006);
// define('COMBO_PROMO_ID', 3007);

// define('VIP_ANNUAL_ID', 3001);
// define('VIP_SEMIANNUAL_ID', 3002);
// define('VIP_888_ANNUAL_ID', 3093);

// define('VIP_PURCHASE_ID', 3193);

// For local
// define('SINGULAR_ID', 7);
// define('PROMO_ID', 8);

// define('ARCHERY_ID', 369);
// define('AIRSOFT_ID', 370);
// define('COMBO_ID', 371);

// define('ARCHERY_ACT_ID', 377);
// define('AIRSOFT_ACT_ID', 378);
// define('COMBO_ACT_ID', 379);

// define('ARCHERY_PROMO_ID', 9);
// define('AIRSOFT_PROMO_ID', 10);
// define('COMBO_PROMO_ID', 11);

// define('VIP_ANNUAL_ID', 380);
// define('VIP_SEMIANNUAL_ID', 381);
// define('VIP_888_ANNUAL_ID', 382);

// ---------------Config Area Ends
