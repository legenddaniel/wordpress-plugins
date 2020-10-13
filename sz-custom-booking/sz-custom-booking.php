<?php
/*
 * Plugin Name: Custom Booking
 * Version: 0.0.0
 * Description: Custom booking
 * Author: Daniel Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: costom-booking
 */

// Exit if accessed directly
defined('ABSPATH') or exit;

// Admin Dashboard
require_once plugin_dir_path(__FILE__) . 'admin.php';

// Config Area
define('SINGULAR_ID', 304);
define('PROMO_ID', 358);
// define('SINGULAR_ID', 7);
// define('PROMO_ID', 8);

// Times remaining of the Promo Pass. Fetch from the database
// Type of pass will be in the future
$promo_count = 11;

/**
 * @desc Check if the current product is 'Singular Passes'
 * @return boolean
 */
function is_singular_pass()
{
    return get_the_ID() === SINGULAR_ID;
}

/**
 * @desc Load CSS and JavaScript
 * @return void
 */
function init_assets()
{
    if (!is_singular_pass()) {
        return;
    }
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'style',
        $plugin_url . 'style.css',
        array(),
        rand(111, 9999)
    );

    wp_enqueue_script(
        'byoe',
        $plugin_url . 'discount-field.js',
        array('jquery'),
        rand(111, 9999)
    );
    wp_enqueue_script(
        'byoe_archery_ajax',
        $plugin_url . 'byoe-archery-ajax.js',
        array('jquery'),
        rand(111, 9999)
    );
    
    $nonce = wp_create_nonce('byoe_archery_ajax');
    wp_localize_script('byoe_archery_ajax', 'my_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => $nonce,
    ));
}
add_action('wp_enqueue_scripts', 'init_assets');

/**
 * @desc Add BYOE checkbox for Archery in 'Singular Passes'
 * @return void
 */
function add_byoe_checkbox_archery()
{
    if (!is_singular_pass()) {
        return;
    } ?>

<div class="sz-discount-fields d-none" id="sz-discount-fields">
    <p class="sz-discount-field" id="byoe_archery_field">
        <input type="checkbox" id="byoe_archery" name="byoe_archery" value="17.5">
        <label for="byoe_archery">Bring Your Own Equipment - Archery</label>
    </p>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'add_byoe_checkbox_archery');

/**
 * @desc Add BYOE checkbox for Combo in 'Singular Passes'
 * @return void
 */
function add_byoe_checkbox_combo()
{
    if (!is_singular_pass()) {
        return;
    } ?>

    <p class="sz-discount-field d-none" id="byoe_combo_field">
        <input type="checkbox" id="byoe_combo" name="byoe_combo" value="57.25">
        <label for="byoe_combo">Bring Your Own Equipment - Combo</label>
    </p>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'add_byoe_checkbox_combo');

/**
 * @desc Add 'Use Promo' checkbox in 'Singular Passes'
 * @return void
 */
function add_promo_checkbox()
{
    global $promo_count;
    if (!is_singular_pass()) {
        return;
    } ?>

    <p class="sz-discount-field" id="promo_field">
        <input type="checkbox" id="promo" name="promo" value="0">
        <label for="promo">Use Promo (<?php echo $promo_count ?> left)</label>
    </p>
</div>

<?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'add_promo_checkbox');

/**
 * @desc Add html templates of access to 'Promo Passes' in 'Singular Passes'
 * @return void
 */
function add_promo_link()
{
    if (is_singular_pass()) {
        ?>
<div>
    <p><span class="sz-text-highlight">Do you know? </span>You can enjoy one FREE extra entry if you buy the promo?</p>
    <a href="<?php echo get_permalink(PROMO_ID)?>"><button>Take me to
            Promo!</button></a>
</div>
<?php
    }
}
add_action('woocommerce_single_product_summary', 'add_promo_link');

/**
 * @desc Apply BYOE discount for Archery in 'Singular Passes'
 * @return void
 */
function apply_byoe_archery_discount()
{
    check_ajax_referer('byoe_archery_ajax');

    $product = wc_get_product(SINGULAR_ID);

    echo $product->get_type();
    $price = $product->get_price();
    $discounted_price = number_format($price * 0.5, 2);

    $is_checked = $_POST['checked'];

    if ($is_checked) {
        $product->set_price($discounted_price);
    } else {
        $product->set_price($price);
    }
    $product->save();

    do_action('woocommerce_bookings_calculated_booking_cost');
    wp_die();
}
add_action('wp_ajax_apply_byoe_archery_discount', 'apply_byoe_archery_discount');
add_action('wp_ajax_nopriv_apply_byoe_archery_discount', 'apply_byoe_archery_discount');

/**
 * @desc Apply Promo discount in 'Singular Passes'
 * @return int
 */
function apply_promo_discount()
{
    $product = wc_get_product(SINGULAR_ID);
    $product->set_price(0);
    $product->save();
    return 0;
}
// add_filter('woocommerce_bookings_calculated_booking_cost', 'apply_promo_discount');
