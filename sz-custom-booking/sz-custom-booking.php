<?php
/*
 * Plugin Name: Custom Booking
 * Version: 0.0.0
 * Description: Custom booking
 * Author: Daniel Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: custom-booking
 */

// Exit if accessed directly
defined('ABSPATH') or exit;

// Admin Dashboard
is_admin() and
require_once plugin_dir_path(__FILE__) . 'admin.php';

// ---------------Config Area Starts

// For real
define('SINGULAR_ID', 68051);
define('PROMO_ID', 68067);
define('ARCHERY_ID', 68059);
define('AIRSOFT_ID', 68060);
define('COMBO_ID', 68062);
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

/**
 * Check if the current product is 'Singular Passes'
 * @return boolean
 */
function is_singular_pass()
{
    return get_the_ID() === SINGULAR_ID;
}

/**
 * Get price for a resource of a product
 * @param string $product_id
 * @param integer $resource_id
 * @return integer
 */
function get_resource_price($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_resources()) {
        return;
    }

    $price = $product->get_resource($resource_id)->get_base_cost();
    return $price;
}

/**
 * Get price off for a resource of a product
 * @param string $product_id
 * @param integer $resource_id
 * @return integer
 */
function get_resource_price_off($product_id, $resource_id)
{
    // So far it's fixed and only for our 3 booking products. In the future they will be from the admin dashboard.

    $product = wc_get_product($product_id);
    if (!$product->has_resources()) {
        return;
    }

    $price_off = 0;
    switch ($resource_id) {
        case ARCHERY_ID:
            $price_off = 17.5;
            break;
        case AIRSOFT_ID:
            $price_off = 0;
            break;
        case COMBO_ID:
            $price_off = 12.25;
            break;
    }
    return $price_off;
}

/**
 * Query the promo remaining for the given type
 * @param string $type
 * @return string
 */
function query_promo_times($type)
{
    global $wpdb;
    $user = get_current_user_id();
    $promo_times = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
             FROM $wpdb->usermeta
             WHERE meta_key LIKE %s
             AND user_id = %d",
            array("%$type%", $user)
        )
    );
    return $promo_times ?? 0;
}

/**
 * Load CSS and JavaScript
 * @return null
 */
function init_assets()
{
    if (!is_singular_pass()) {
        return;
    }
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'style',
        "{$plugin_url}style.css",
        array(),
        rand(111, 9999)
    );

    wp_enqueue_script(
        'discount_ajax',
        "{$plugin_url}discount-ajax.js",
        array('jquery'),
        rand(111, 9999)
    );
    wp_enqueue_script(
        'discount_field',
        "{$plugin_url}old-discount-field.js",
        array('jquery'),
        rand(111, 9999)
    );
    wp_enqueue_script(
        'select',
        "{$plugin_url}select.js",
        array('jquery'),
        rand(111, 9999)
    );

    wp_localize_script(
        'discount_ajax',
        'my_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'discount_nonce' => wp_create_nonce('discount_prices'),
            'vip_nonce' => wp_create_nonce('vip_count'),
        )
    );
}
add_action('wp_enqueue_scripts', 'init_assets');

/**
 * Process discount query ajax for 'Singular Passes'
 * @return void
 */
function fetch_discount_prices()
{
    try {
        check_ajax_referer('discount_prices');

        $resource = $_POST['resource_id'];
        $price = get_resource_price(SINGULAR_ID, $resource);
        $price_off = get_resource_price_off(SINGULAR_ID, $resource);

        $res = array(
            'resource' => $resource,
            'price' => $price,
            'price_off' => $price_off,
        );
        wp_send_json_success($res);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_fetch_discount_prices', 'fetch_discount_prices');
add_action('wp_ajax_nopriv_fetch_discount_prices', 'fetch_discount_prices');

/**
 * Add html templates of access to 'Promo Passes' in 'Singular Passes'
 * @return null
 */
function render_summary()
{
    if (!is_singular_pass()) {
        return;
    }?>
    <div class="mtb-25 promoQuestion">
	    <div class="row">
	        <div class="column">
                <p class="p-question">
                    <span class="sz-text-highlight-red">Did you know? </span>You can enjoy one FREE extra entry if you buy the promo package!
                </p>
            </div>
            <div class="column">
                <a class="a-question" href="<?php echo get_permalink(PROMO_ID) ?>"><button>Take me to Promo!</button></a>
            </div>
        </div>
    </div>

    <hr>

    <div class="mtb-25">
        <p class="sz-sum-head">
            You are a few clicks away from booking your session at
            <span class="sz-text-highlight-green">Solely Outdoors</span> located at
            <span class="sz-text-highlight-green">101 - 8365 Woodbine Avenue, Markham ON</span>. <br>
            We are open by reservation only. For same day booking, please call first to check availability: (905) 882-8629.
            <br>
            You may also book over the phone. See you soon!
        </p>
        <div class="sz-sum-sub-desc">
            <div class="mlr-10">
                <h4 class="sz-sum-title">Check-in</h4>
                <p class="p-content">
                    Check-in starts 10 minutes prior to your booked session time. If you book for 4:30, please arrive at
                    4:20 for check-in. </p>
            </div>
            <div class="mlr-10">
                <h4 class="sz-sum-title">Duration</h4>
                <p class="p-content">

                    The session is 60 minutes long and includes expert shooting instructions from your instructor.
                </p>
            </div>
            <div class="mlr-10">
                <h4 class="sz-sum-title">Age</h4>
                <p class="p-content">

                    There are no age restrictions but children under 16 years old must be accompanied by an adult.

                </p>
            </div>
        </div>
    </div>
<?php
}
add_action('woocommerce_single_product_summary', 'render_summary');

/**
 * Add discount field in 'Singular Passes'
 * @return null
 */
function render_discount_field()
{
    if (!is_singular_pass()) {
        return;
    }

    // Render Archery info by default.
    $price = get_resource_price(SINGULAR_ID, ARCHERY_ID);
    $price_off = get_resource_price_off(SINGULAR_ID, ARCHERY_ID);
    ?>

    <div class="sz-discount-field d-none" id="sz-discount-field" data-price=<?php echo $price; ?>>
        <div>
            <input type="checkbox" id="byoe-enable" name="byoe-enable" data-price=<?php echo $price_off; ?>>
            <label for="byoe-enable">Bring Your Own Equipment</label>
        </div>

        <div class="sz-select-field" style="display:none">
            <label for="byoe-qty">Quantity:</label>
            <select name="byoe-qty" id="byoe-qty">
            </select>
        </div>

    <?php

    // SO far only display 'Use Promo' field to registered customers
    if (!is_user_logged_in()) {
        echo '</div>';
        return;
    }
    $promo_count = query_promo_times('Archery');

    ?>

        <div>
            <input type="checkbox" id="promo-enable" name="promo-enable" data-price=<?php echo $price; ?>>
            <label for="promo-enable">Use Promo (<?php echo $promo_count; ?> left)</label>
        </div>


    <?php

    $user = get_current_user_id();
    $isVIP = wc_memberships_is_user_active_member($user, VIP_ANNUAL_ID) || wc_memberships_is_user_active_member($user, VIP_SEMIANNUAL_ID);
    if (!$isVIP) {
        echo '</div>';
        return;
    }
    $vip_count = 2; // SHould be from the database.

    ?>

        <div>
            <input type="radio" id="vip-enable" name="vip-enable" data-price=<?php echo $price; ?> checked>
            <label for="vip-enable">Use VIP (<?php echo $vip_count; ?> left)</label>
        </div>
    </div>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field');

/**
 * Add the entries of discounts in the cart item data. Fire at the beginning of $cart_item_data initialization?
 * @param array? $cart_item_data
 * @param int? $product
 * @param string $variation
 * @return array
 */
function add_discount_info_into_cart($cart_item_data, $product, $variation)
{
    $cart_item_data['discount'] = array();

    foreach (array('byoe', 'promo') as $field) {
        // Return if discount is not enabled
        if (!isset($_POST["$field-enable"])) {
            return;
        }

        // Return if using 0 discount
        if (isset($_POST["$field-qty"]) && empty($_POST["$field-qty"])) {
            return;
        }

        $resource = $_POST['wc_bookings_field_resource'];
        $price = get_resource_price(SINGULAR_ID, $resource);

        // Discount quantity will be 1 if no select dropdown
        $qty = !isset($_POST["$field-qty"]) ? 1 : +$_POST["$field-qty"];

        $price_off = 0;
        $discount_type = '';
        if ($field === 'byoe') {
            $discount_type = 'Bring Your Own Equipment';
            $price_off = get_resource_price_off(SINGULAR_ID, $resource);
        }
        if ($field === 'promo') {
            $discount_type = 'Use Promo';
            $price_off = $price;
        }

        $cart_item_data['discount'][] = array(
            'type' => $discount_type,
            'price_off' => $price_off,
            'qty' => $qty,
        );
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_discount_info_into_cart', 10, 3);

/**
 * Add discount information field in the cart as well as the cart preview in the product page
 * @param array $cart_item_data
 * @param mixed $cart_item?
 * @return mixed?
 */
function render_discount_field_in_cart($cart_item_data, $cart_item)
{
    if (empty($cart_item['discount'])) {
        return;
    }

    $display = '';
    foreach ($cart_item['discount'] as $discount) {
        $display .= "\n" . 'Discount Type: ' . $discount['type'];
        $display .= "\n" . 'Discount: -' . wc_price($discount['price_off']) . ' * ' . $discount['qty'];
    }
    $display = ltrim($display, "\n");

    $cart_item_data[] = array(
        'name' => __("Discount Info", "woocommerce"),
        'value' => __($display, "woocommerce"),
    );
    return $cart_item_data;
}
add_filter('woocommerce_get_item_data', 'render_discount_field_in_cart', 10, 2);

/**
 * Re-calculate the prices in the cart
 * @param mixed $cart
 * @return null
 */
function recalculate_total($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (empty($cart_item['discount'])) {
            continue;
        }

        $total = $cart_item['booking']['_cost'];
        $price_off = 0;
        foreach ($cart_item['discount'] as $discount) {
            $price_off += $discount['price_off'] * $discount['qty'];
        }
        $total -= $price_off;
        $total = $total > 0 ? $total : 0;
        $cart_item['data']->set_price($total);
    }
}
add_action('woocommerce_before_calculate_totals', 'recalculate_total');

/**
 * Add the entries of discounts in the order meta data.
 * @param mixed $item
 * @param string $cart_item_key
 * @param mixed $values
 * @param mixed $order
 * @return null
 */
function add_discount_info_into_order($item, $cart_item_key, $values, $order)
{
    $discount_data = $values['discount'];
    if (empty($discount_data)) {
        return;
    }

    // Serialized data
    $item->update_meta_data('discount', $discount_data);
}
add_action('woocommerce_checkout_create_order_line_item', 'add_discount_info_into_order', 10, 4);