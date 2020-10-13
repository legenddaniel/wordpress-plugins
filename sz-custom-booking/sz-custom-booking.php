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
define('ARCHERY_ID', 291);
define('COMBO_ID', 293);
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
        'discount_field',
        $plugin_url . 'discount-field.js',
        array('jquery'),
        rand(111, 9999)
    );
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
    }
    // In the future the discounted price will be from the admin dashboard
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(ARCHERY_ID)->get_base_cost();
    $discounted_price = $price * 0.5; ?>

<div class="sz-discount-fields d-none" id="sz-discount-fields">
    <p class="sz-discount-field" id="byoe-archery-field">
        <input type="checkbox" id="byoe-archery" name="byoe-archery" value=<?php echo $discounted_price; ?> data-price=<?php echo $price; ?>>
        <label for="byoe-archery">Bring Your Own Equipment - Archery</label>
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
    }
    // In the future the discounted price will be from the admin dashboard
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(COMBO_ID)->get_base_cost();
    $discounted_price = $price * 0.825; ?>

    <p class="sz-discount-field d-none" id="byoe-combo-field">
        <input type="checkbox" id="byoe-combo" name="byoe-combo" value=<?php echo $discounted_price; ?> data-price=<?php echo $price; ?>>
        <label for="byoe-combo">Bring Your Own Equipment - Combo</label>
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

    <p class="sz-discount-field" id="promo-field">
        <input type="checkbox" id="promo" name="promo" value="0">
        <label for="promo">Use Promo (<?php echo $promo_count ?>
            left)</label>
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
 * @desc Add the entry of discount in the cart item data
 * @param array $cart_item_data
 * @param int $product?
 * @param string $variation
 * @return array
 */
function set_discount_in_cart_data($cart_item_data, $product, $variation)
{
    if (isset($_POST['byoe-archery'])) {
        $cart_item_data['discounted_price'] = $_POST['byoe-archery'];
        $cart_item_data['unique_key']     = md5(microtime().rand());
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'set_discount_in_cart_data', 10, 3);

/**
 * @desc Re-calculate the prices in the cart
 * @return void
 */
function calculate_byoe_discount($cart)
{
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['discounted_price'])) {
            $cart_item['data']->set_price($cart_item['booking']['_qty'] * $cart_item['discounted_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'calculate_byoe_discount');
