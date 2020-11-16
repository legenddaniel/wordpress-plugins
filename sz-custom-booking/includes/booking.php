<?php

// Main functionalties of custom booking

/**
 * Load CSS and JavaScript
 * @return Null
 */
function init_assets()
{
    if (!is_single(SINGULAR_ID)) {
        return;
    }
    $plugin_url = plugin_dir_url(__DIR__);

    wp_enqueue_style(
        'style',
        $plugin_url . 'css/style.css',
        [],
        rand(111, 9999)
    );

    wp_enqueue_script(
        'discount_ajax',
        $plugin_url . 'js/discount-ajax.js',
        ['jquery'],
        rand(111, 9999)
    );
    wp_enqueue_script(
        'discount_field',
        $plugin_url . 'js/discount-field.js',
        ['jquery'],
        rand(111, 9999)
    );
    wp_enqueue_script(
        'select',
        $plugin_url . 'js/select.js',
        ['jquery'],
        rand(111, 9999)
    );

    wp_localize_script(
        'discount_ajax',
        'my_ajax_obj',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'discount_nonce' => wp_create_nonce('discount_prices'),
        ]
    );
}
add_action('wp_enqueue_scripts', 'init_assets');

/**
 * Process discount query ajax for 'Singular Passes'
 * @return Null
 */
function fetch_discount_prices()
{
    try {
        check_ajax_referer('discount_prices');

        $resource = sanitize_text_field($_POST['resource_id']);
        $resource_name = get_resource_title(SINGULAR_ID, $resource);

        $price = get_resource_price(SINGULAR_ID, $resource);
        $byoe_price = get_resource_byoe_price(SINGULAR_ID, $resource);
        $final_price = $byoe_price ?? $price;
        $price_off = $price - $final_price;

        $vip_count = query_vip_times(VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID);
        $promo_count = query_promo_times($resource_name);
        $total_promo_count = $promo_count + $vip_count;

        $promo_label = "Use Promo ($total_promo_count left";
        $promo_label .= is_null($vip_count) ? ")" : ", including $vip_count free VIP discount)";

        $res = [
            'resource' => $resource,
            'byoe_enable' => !is_null($byoe_price),
            'price' => $price,
            'price_off' => $price_off,
            'promo_label' => $promo_label,
        ];

        wp_send_json_success($res);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_fetch_discount_prices', 'fetch_discount_prices');
add_action('wp_ajax_nopriv_fetch_discount_prices', 'fetch_discount_prices');

/**
 * Add html templates of access to 'Promo Passes' in 'Singular Passes'
 * @return Null
 */
function render_summary()
{
    if (!is_single(SINGULAR_ID)) {
        return;
    }

    $btn_text = is_user_logged_in() ? 'Take me to Promo!' : 'Get me in first! (wrong url now)'
    ?>

    <div class="mtb-25 promoQuestion">
	    <div class="row">
	        <div class="column">
                <p class="p-question">
                    <span class="sz-text-highlight-red">Did you know? </span>You can enjoy one FREE extra entry if you <?=is_user_logged_in() ? '' : 'become a member and ';?>buy the promo package!
                </p>
            </div>
            <div class="column">
                <a class="a-question" href="<?=get_permalink(PROMO_ID)?>"><button class="b-question"><?=$btn_text;?></button></a>
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
 * @return Null
 */
function render_discount_field()
{
    if (!is_single(SINGULAR_ID)) {
        return;
    }

    // Render Archery info by default.
    $price = get_resource_price(SINGULAR_ID, ARCHERY_ID);
    $byoe_price = get_resource_byoe_price(SINGULAR_ID, ARCHERY_ID);
    $final_price = $byoe_price ?? $price;
    $price_off = $price - $final_price;
    $byoe_display = is_null($byoe_price) ? 'display: none;' : '';
    ?>

    <div class="sz-discount-field d-none" id="sz-discount-field" data-price=<?=esc_attr($price);?>>
        <div style=<?=esc_attr($byoe_display);?>>
            <input type="checkbox" id="byoe-enable" name="byoe-enable" data-price=<?=esc_attr($price_off);?>>
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

    // Should be from the database.
    $vip_count = query_vip_times(VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID);
    $total_promo_count = $promo_count + $vip_count;

    $input_label = "Use Promo ($total_promo_count left";
    $input_label .= is_null($vip_count) ? ")" : ", including $vip_count free VIP discount)";

    ?>

        <div>
            <input type="checkbox" id="promo-enable" name="promo-enable" data-price=<?=esc_attr($price);?> <?=esc_attr($total_promo_count ? '' : 'disabled');?>>
            <label for="promo-enable"><?=esc_html__($input_label);?></label>
        </div>

    <?php
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field');

/**
 * Validate the discount quantity
 * @param Array $passed
 * @param Integer $product_id
 * @param Boolean $quantity
 * @return Array
 */
function validate_discount_qty($passed, $product_id, $quantity)
{
    if ($product_id !== SINGULAR_ID) {
        return $passed;
    }

    foreach (['byoe', 'promo'] as $field) {
        if (isset($_POST["$field-enable"]) && isset($_POST["$field-qty"]) && empty($_POST["$field-qty"])) {
            $passed = false;
            wc_add_notice(__('Please input the discount quantity you want to use!', 'woocommerce'), 'error');
            break;
        }
    }
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'validate_discount_qty', 10, 3);

/**
 * Add the entries of discounts in the cart item data with validation. Fire at the beginning of $cart_item_data initialization?
 * @param Array? $cart_item_data
 * @param Integer? $product
 * @param String $variation
 * @return Array
 */
function add_discount_info_into_cart($cart_item_data, $product, $variation)
{
    $cart_item_data['discount'] = [];

    if (!isset($_POST["byoe-enable"]) && !isset($_POST["promo-enable"])) {
        return $cart_item_data;
    }

    $resource = sanitize_text_field($_POST['wc_bookings_field_resource']);

    // Must match the input name at the client side
    $fields = ['byoe', 'promo'];
    foreach ($fields as $field) {

        // Return if discount is not enabled
        if (!isset($_POST["$field-enable"])) {
            continue;
        }

        // Return if using 0 discount
        $qty = $_POST["$field-qty"];
        if (isset($qty) && empty($qty)) {
            continue;
        }

        $price = get_resource_price(SINGULAR_ID, $resource);

        // Discount quantity will be 1 if no select dropdown
        $qty = +sanitize_text_field($qty) ?: 1;
        $price_off = 0;
        $discount_type = '';

        if ($field === 'byoe') {
            $discount_type = 'Bring Your Own Equipment';
            $byoe_price = get_resource_byoe_price(SINGULAR_ID, $resource);
            $final_price = $byoe_price ?? $price;
            $price_off = $price - $final_price;
            $qty = min($qty, +sanitize_text_field($_POST['wc_bookings_field_persons']));
        }

        if ($field === 'promo') {
            $resource_name = get_resource_title(SINGULAR_ID, $resource);

            // Discount validation. $vip_count should be also from the database
            $vip_count = query_vip_times(VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID);
            $promo_count = query_promo_times($resource_name);
            $total_promo_count = $promo_count + $vip_count;

            switch ($vip_count) {
                case null:
                    $discount_type = 'Use Promo';
                    break;
                case 0:
                    $discount_type = 'Use Promo (no valid VIP)';
                    break;
                default:
                    $discount_type = 'Use VIP';
                    break;
            }
            $price_off = $price;
            $qty = min($qty, $total_promo_count);
        }

        $cart_item_data['discount'][] = [
            'type' => $discount_type,
            'price_off' => $price_off,
            'qty' => $qty,
        ];
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_discount_info_into_cart', 10, 3);

/**
 * Add discount information field in the cart as well as the cart preview in the product page
 * @param Array $cart_item_data
 * @param Mixed $cart_item?
 * @return Mixed?
 */
function render_discount_field_in_cart($cart_item_data, $cart_item)
{
    // No rendering if no discount applied
    if (empty($cart_item['discount'])) {
        return $cart_item_data;
    }

    $display = '';
    foreach ($cart_item['discount'] as $discount) {
        $display .= "\n" . 'Discount Type: ' . $discount['type'];
        $display .= "\n" . 'Discount: -' . wc_price($discount['price_off']) . ' * ' . $discount['qty'];
    }
    $display = ltrim($display, "\n");

    $cart_item_data[] = [
        'name' => __("Discount Info", "woocommerce"),
        'value' => __($display, "woocommerce"),
    ];
    return $cart_item_data;
}
add_filter('woocommerce_get_item_data', 'render_discount_field_in_cart', 10, 2);

/**
 * Re-calculate the prices in the cart
 * @param Mixed $cart
 * @return Null
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
        $total = max($total - $price_off, 0);
        $cart_item['data']->set_price($total);
    }
}
add_action('woocommerce_before_calculate_totals', 'recalculate_total');

/**
 * Add the entries of discounts in the order meta data.
 * @param Mixed $item
 * @param String $cart_item_key
 * @param Mixed $values
 * @param Mixed $order
 * @return Null
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