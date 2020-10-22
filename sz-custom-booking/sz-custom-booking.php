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
        "{$plugin_url}style.css",
        array(),
        rand(111, 9999)
    );

    wp_enqueue_script(
        'discount_field',
        "{$plugin_url}discount-field.js",
        array('jquery'),
        rand(111, 9999)
    );
}
add_action('wp_enqueue_scripts', 'init_assets');

/**
 * Add html templates of access to 'Promo Passes' in 'Singular Passes'
 * @return void
 */
function render_summary()
{
    if (!is_singular_pass()) {
        return;
    } ?>
<div class="mtb-25 promoQuestion">
    <p><span class="sz-text-highlight">Did you know? </span>You can enjoy one FREE extra entry if you buy the promo
        package!</p>
    <a href="<?php echo get_permalink(PROMO_ID)?>"><button>Take me to
            Promo!</button></a>
</div>

<hr>

<div class="mtb-25 ">
    <p class="headLine">
        You are a few clicks away from booking your session at <span style="color:#5ab152">Solely Outdoors</span>
        located at <span style="color:#5ab152">101 - 8365 Woodbine Avenue, Marham ON.</span>. <br>
        We are open by reservation only. For same day booking, please call first to check availability: (905) 882-8629.
        <br>
        You may also book over the phone. See you soon!
    </p>
    <div class="sz-sum-sub-desc">
        <div class="mlr-10">
            <p class="sz-summary-title">Check-in</p>
            <p>
                Check-in starts 10 minutes prior to your booked session time. If you book for 4:30, please arrive at
                4:20 for check-in. </p>
        </div>
        <div class="mlr-10">
            <p class="sz-summary-title">Duration</p>
            <p>

                The session is 60 minutes long and includes expert shooting instructions from your instructor.
            </p>
        </div>
        <div class="mlr-10">
            <p class="sz-summary-title">Age</p>
            <p>

                There are no age restrictions but children under 16 years old must be accompanied by an adult.

            </p>
        </div>
    </div>
</div>
<?php
}
add_action('woocommerce_single_product_summary', 'render_summary');

/**
 * Add discount checkboxes for Archery in 'Singular Passes'
 * @return void
 */
function render_discount_field_archery()
{
    if (!is_singular_pass()) {
        return;
    }
    // In the future the discounted price will be from the admin dashboard
    $archery_promo_count = query_promo_times('Archery');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(ARCHERY_ID)->get_base_cost();
    $discounted_price = $price * 0.5; ?>

<div class="sz-discount-fields d-none" id="sz-discount-fields">
    <div class="sz-discount-field" id="archery-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="byoe-archery" name="byoe" value=<?php echo $discounted_price; ?>>
            <label for="byoe-archery">Bring Your Own Equipment - Archery</label>
        </p>

        <?php
        // Only display 'Use Promo' field to registered customers
        if (!is_user_logged_in()) {
            return;
        } ?>


        <input type="checkbox" id="promo-archery" name="promo" value="0">
        <label for="promo-archery">Use Promo (<?php echo $archery_promo_count; ?>
            left)</label>


        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes">How many passes to use:</label>
                <select name="wdm_name" id="passes">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>


    </div>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
// add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_archery');

/**
 * Add discount checkboxes for Airsoft in 'Singular Passes'
 * @return void
 */
function render_discount_field_airsoft()
{
    if (!is_singular_pass()) {
        return;
    }
    // Only display 'Use Promo' field to registered customers
    if (!is_user_logged_in()) {
        return;
    }
    
    $airsoft_promo_count = query_promo_times('Airsoft');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(AIRSOFT_ID)->get_base_cost(); ?>
    <div class="sz-discount-field d-none" id="airsoft-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="promo-airsoft" name="promo" value="0">
            <label for="promo-airsoft">Use Promo (<?php echo $airsoft_promo_count; ?>
                left)</label>
        </p>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes">How many passes to use:</label>
                <select name="wdm_name" id="passes">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>

    </div>

    <?php
}
// add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_airsoft');

/**
 * Add discount checkboxes for Combo in 'Singular Passes'
 * @return array
 */
function render_discount_field_combo()
{
    if (!is_singular_pass()) {
        return;
    }
    // In the future the discounted price will be from the admin dashboard
    $combo_promo_count = query_promo_times('Combo');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(COMBO_ID)->get_base_cost();
    $discounted_price = $price * 0.825; ?>

    <div class="sz-discount-field d-none" id="combo-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="byoe-combo" name="byoe" value=<?php echo $discounted_price; ?>>
            <label for="byoe-combo">Bring Your Own Equipment - Combo</label>
        </p>

        <?php
        // Only display 'Use Promo' field to registered customers
        if (!is_user_logged_in()) {
            return;
        } ?>

        <p>
            <input type="checkbox" id="promo-combo" name="promo" value="0">
            <label for="promo-combo">Use Promo (<?php echo $combo_promo_count; ?>
                left)</label>
        </p>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes">How many passes to use:</label>
                <select name="wdm_name" id="passes">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>

    </div>
</div>

<?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
// add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_combo');

/**
 * Add the entries of discounts in the cart item data. Fire at the beginning of $cart_item_data initialization?
 * @param array? $cart_item_data
 * @param int? $product
 * @param string $variation
 * @return array
 */
function add_discount_info_into_data($cart_item_data, $product, $variation)
{
    $cart_item_data['discount_type'] = array();
    if (isset($_POST['promo'])) {
        array_push($cart_item_data['discount_type'], 'Use Promo');
    }
    if (isset($_POST['byoe'])) {
        array_push($cart_item_data['discount_type'], 'Bring Your Own Equipment');
    }
    
    if (isset($_POST['promo'])) {
        // So far use promo for all persons by default, later on will add the number of passes being used
        // $cart_item_data['promo_used'] = '1';
        $cart_item_data['discounted_price'] = $_POST['promo'];
        return $cart_item_data;
    }
    if (isset($_POST['byoe'])) {
        $cart_item_data['discounted_price'] = $_POST['byoe'];
        return $cart_item_data;
    }
}
// add_filter('woocommerce_add_cart_item_data', 'add_discount_info_into_data', 10, 3);

/**
 * Add discount information field in the cart as well as the cart preview in the product page
 * @param mixed? $cart_item_data
 * @param mixed? $cart_item?
 * @return mixed?
 */
function render_discount_field_in_cart($cart_item_data, $cart_item)
{
    if (!isset($cart_item['discount_type'])) {
        return;
    }

    $display = join("\n", $cart_item['discount_type']);
    $cart_item_data[] = array(
        'name' => __("Discount Options", "woocommerce"),
        'value' => __($display, "woocommerce")
    );
    return $cart_item_data;
}
// add_filter('woocommerce_get_item_data', 'render_discount_field_in_cart', 10, 2);

/**
 * Re-calculate the prices in the cart
 * @param mixed? $cart
 * @return void
 */
function recalculate_total($cart)
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
// add_action('woocommerce_before_calculate_totals', 'recalculate_total');

// add_action('woocommerce_add_order_item_meta', 'add_order_item_meta', 10, 2);
// function add_order_item_meta($item_id, $values)
// {
//     echo '<script>console.log('.json_encode($values).')</script>';
//     if (isset($values['discount_type'])) {
//         $discount_type  = $values['discount_type'];
//         wc_add_order_item_meta($item_id, 'discount_type', $discount_type);
//     }
// }
//
// add_action( 'woocommerce_before_order_notes', 'add_checkout_custom_text_fields', 20, 1 );
//
function add_discount_info_into_booking_data($data)
{
    // 	if (isset($cart_item_data['discount_type']) && count($cart_item_data['discount_type'])) {
    // 		$booking = WC_Booking_Cart_Manager()::create_booking_from_cart_data();
    // 		$data['aaaaa'] = 'aaaaa';
    array_merge($data, array('aaaaa' => 'aaaaa'));
    return $data;
    // 	}
}
// add_filter('woocommerce_new_booking_data', 'add_discount_info_into_booking_data');


































// Daniel's new modification below

/**
 * Add discount checkboxes for Archery in 'Singular Passes'
 * @return void
 */
function render_discount_field_archery2()
{
    if (!is_singular_pass()) {
        return;
    }
    // In the future the discounted price will be from the admin dashboard
    $archery_promo_count = query_promo_times('Archery');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(ARCHERY_ID)->get_base_cost();
    $price_off = $price * (1 - 0.5); ?>

<div class="sz-discount-fields d-none" id="sz-discount-fields">
    <div class="sz-discount-field" id="archery-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="byoe-archery" name="byoe-enable" value=<?php echo $price_off; ?>>
            <label for="byoe-archery">Bring Your Own Equipment - Archery</label>
        </p>

        <?php
        // Only display 'Use Promo' field to registered customers
        if (!is_user_logged_in()) {
            return;
        } ?>

        <input type="checkbox" id="promo-archery" name="promo-enable" value="0">
        <label for="promo-archery">Use Promo (<?php echo $archery_promo_count; ?>
            left)</label>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes-archery">How many passes to use:</label>
                <select name="wdm_name" id="passes-archery">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>


    </div>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_archery2');

/**
 * Add discount checkboxes for Airsoft in 'Singular Passes'
 * @return void
 */
function render_discount_field_airsoft2()
{
    if (!is_singular_pass()) {
        return;
    }
    // Only display 'Use Promo' field to registered customers
    if (!is_user_logged_in()) {
        return;
    }
    
    $airsoft_promo_count = query_promo_times('Airsoft');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(AIRSOFT_ID)->get_base_cost(); ?>
    <div class="sz-discount-field d-none" id="airsoft-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="promo-airsoft" name="promo-enable" value="0">
            <label for="promo-airsoft">Use Promo (<?php echo $airsoft_promo_count; ?>
                left)</label>
        </p>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes-airsoft">How many passes to use:</label>
                <select name="wdm_name" id="passes-airsoft">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>

    </div>

    <?php
}
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_airsoft2');

/**
 * Add discount checkboxes for Combo in 'Singular Passes'
 * @return array
 */
function render_discount_field_combo2()
{
    if (!is_singular_pass()) {
        return;
    }
    // In the future the discounted price will be from the admin dashboard
    $combo_promo_count = query_promo_times('Combo');
    $product = wc_get_product(SINGULAR_ID);
    $price = $product->get_resource(COMBO_ID)->get_base_cost();
    $price_off = $price * (1 - 0.825); ?>

    <div class="sz-discount-field d-none" id="combo-field" data-price=<?php echo $price; ?>>
        <p>
            <input type="checkbox" id="byoe-combo" name="byoe-enable" value=<?php echo $price_off; ?>>
            <label for="byoe-combo">Bring Your Own Equipment - Combo</label>
        </p>

        <?php
        // Only display 'Use Promo' field to registered customers
        if (!is_user_logged_in()) {
            return;
        } ?>

        <p>
            <input type="checkbox" id="promo-combo" name="promo-enable" value="0">
            <label for="promo-combo">Use Promo (<?php echo $combo_promo_count; ?>
                left)</label>
        </p>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes-combo">How many passes to use:</label>
                <select name="wdm_name" id="passes-combo">
                    <?php
    for ($i = 0; $i <= 1; $i++) {
        echo "<option>" .$i. "</option>";
    } ?>
                </select>
            </div>
        </div>

    </div>
</div>

<?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field_combo2');


/**
 * Add the entries of discounts in the cart item data. Fire at the beginning of $cart_item_data initialization?
 * @param array? $cart_item_data
 * @param int? $product
 * @param string $variation
 * @return array
 */
function add_discount_info_into_data2($cart_item_data, $product, $variation)
{
    $cart_item_data['discount'] = array();
    
    // && !empty($_POST['wdm_name'])
    if (isset($_POST['promo-enable'])) {
        array_push($cart_item_data['discount'], array(
            'type' 		=> 'Use Promo',
            'price_off' => $_POST['promo-enable'],
            'qty' 		=> $_POST['wdm_name'],
        ));
    }
    
    if (isset($_POST['byoe-enable'])) {
        array_push($cart_item_data['discount'], array(
            'type' 		=> 'Bring Your Own Equipment',
            'price_off' => $_POST['byoe-enable'],
            'qty' 		=> $_POST['wdm_name'],
        ));
    }
    
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_discount_info_into_data2', 10, 3);

/**
 * Add discount information field in the cart as well as the cart preview in the product page
 * @param mixed? $cart_item_data
 * @param mixed? $cart_item?
 * @return mixed?
 */
function render_discount_field_in_cart2($cart_item_data, $cart_item)
{
    if (!count($cart_item['discount'])) {
        return;
    }
    
    $display = '';
    foreach ($cart_item['discount'] as $discount) {
        foreach ($discount as $key => $value) {
            $display .= "\n$key: $value";
        }
    }
    $display = ltrim($display, "\n");
    
    $cart_item_data[] = array(
        'name' => __("Discount Info", "woocommerce"),
        'value' => __($display, "woocommerce")
    );
    return $cart_item_data;
}
add_filter('woocommerce_get_item_data', 'render_discount_field_in_cart2', 10, 2);

/**
 * Re-calculate the prices in the cart
 * @param mixed? $cart
 * @return void
 */
function recalculate_total2($cart)
{
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (count($cart_item['discount'])) {
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
}
add_action('woocommerce_before_calculate_totals', 'recalculate_total2');
