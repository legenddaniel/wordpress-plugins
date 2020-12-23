<?php

// Main functionalties of custom booking

/**
 * Load CSS and JavaScript
 * @return null
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

    // Must be first
    wp_enqueue_script(
        'polyfill',
        'https://polyfill.io/v3/polyfill.min.js?features=NodeList.prototype.forEach%2CMutationObserver'
    );
    // Must be right after the polyfill
    wp_enqueue_script(
        'resource',
        $plugin_url . 'js/resource.js',
        ['jquery'],
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
    wp_enqueue_script(
        'mini_cart',
        $plugin_url . 'js/mini-cart.js',
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
 * @return null
 */
function fetch_discount_prices()
{
    try {
        check_ajax_referer('discount_prices');

        $user = get_current_user_id();

        $resource = sanitize_text_field($_POST['resource_id']);
        $resource_name = get_resource_title(SINGULAR_ID, $resource);

        $price = get_resource_price(SINGULAR_ID, $resource);
        $byoe_price = get_resource_byoe_price(SINGULAR_ID, $resource);
        $final_price = $byoe_price ?? $price;
        $price_off = $price - $final_price;

        $vip_count = query_vip_times($user);
        $promo_count = query_promo_times($user, $resource_name);
        $total_promo_count = $promo_count + $vip_count;

        $promo_label = "Use Promo ($total_promo_count left";
        $promo_label .= is_null($vip_count) ? ")" : ", including $vip_count free VIP discount)";

        // Get used discounts from cart items
        $promo_cart_count = 0;
        $vip_cart_count = 0;

        foreach (sz_get_cart() as $cart_item) {
            if ($cart_item['product_id'] !== SINGULAR_ID) {
                continue;
            }

            // Count VIP and Promo for specific resource
            foreach ($cart_item['discount'] as $discount) {
                $cart_discount_type = $discount['type'];
                $cart_discount_qty = $discount['qty'];
                if (strpos($cart_discount_type, 'Use VIP') !== false) {
                    $vip_cart_count += $cart_discount_qty;
                    break;
                }
                if (strpos($cart_discount_type, 'Use Promo') !== false && $cart_item['booking']['_persons'][$resource]) {
                    $promo_cart_count += $cart_discount_qty;
                    break;
                }
            }
        }

        // Append extra discount info in the cart to the label
        if ($promo_cart_count xor $vip_cart_count) {
            $cart_discount_type = $promo_cart_count > $vip_cart_count ? 'Promo' : 'VIP';
            $cart_discount_qty = max($promo_cart_count, $vip_cart_count);
            $promo_label .= " ($cart_discount_qty $cart_discount_type being deducted in the cart)";
        }
        if ($promo_cart_count && $vip_cart_count) {
            $promo_label .= " ($promo_cart_count Promo, $vip_cart_count VIP being deducted in the cart)";
        }
        $available_promo_count = $total_promo_count - $promo_cart_count - $vip_cart_count;

        $res = [
            'resource' => $resource,
            'byoe_enable' => !is_null($byoe_price),
            'price' => $price,
            'price_off' => $price_off,
            'promo_label' => $promo_label,
            'has_promo' => !!$available_promo_count,
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
 * @return null
 */
function render_summary()
{
    if (!is_single(SINGULAR_ID)) {
        return;
    }

    $is_logged_in = is_user_logged_in();
    $btn_text = $is_logged_in ? 'Take me to Promo!' : 'Get me in first!';
    $href = $is_logged_in ? PROMO_ID : get_option('woocommerce_myaccount_page_id');
    ?>

    <div class="mtb-25 promoQuestion">
	    <div class="row">
	        <div class="column">
                <p class="p-question">
                    <span class="sz-text-highlight-green">Did you know? </span>You can enjoy one FREE extra entry if you <?=$is_logged_in ? '' : 'become a member and ';?>buy the promo package!
                </p>
            </div>
            <div class="column">
                <a class="a-question" href=<?=get_permalink($href);?>><button><?=$btn_text;?></button></a>
            </div>
        </div>
    </div>

    <hr>

<?php
}
add_action('woocommerce_single_product_summary', 'render_summary');

/**
 * Set booking availability based on user type (member or not).
 * @param array $availability_rules - Array of availability rules
 * @param int $resource_id - Resource rules apply to, if resource is 0, then for the product itself
 * @param WC_Product_Booking $product - Bookable product
 */
function sz_set_booking_availability($availability_rules, $resource_id, $product)
{
    /**
     *
     * Temporarily fixed date and time for existing 3 memberships.
     *
     *
     */

    // Allow certain members to book for evening time
    if ($product->get_id() == SINGULAR_ID) {
        $memberships = [VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID];
        $user = get_current_user_id();
        if (!is_vip($user, ...$memberships)) {
            return $availability_rules;
        }

        // Clear cache to enforce customize rules in any cases
        WC_Bookings_Cache::delete_booking_slots_transient($product);

        $l = count($availability_rules);
        // foreach (['morning', 'evening'] as $time) {
        //     $time_discount = get_post_meta(SINGULAR_ID, $time . '_discount', true);
        //     if (!$time_discount['enable']) {
        //         continue;
        //     }

        //     if ($time === 'morning') {
        //         for ($i = 0; $i < $l; $i++) {
        //             if ($time_discount['from'] < $availability_rules[$i]['range']['from']) {
        //                 $availability_rules[$i]['range']['from'] = $time_discount['from'];
        //             }
        //         }
        //     }
        //     if ($time === 'evening') {
        //         for ($i = 0; $i < $l; $i++) {
        //             if ($time_discount['to'] > $availability_rules[$i]['range']['to']) {
        //                 $availability_rules[$i]['range']['to'] = $time_discount['to'];
        //             }
        //         }
        //     }
        // }

        for ($i = 0; $i < $l; $i++) {
            $availability_rules[$i]['range']['to'] = '21:00';
        }
    }

    return $availability_rules;
}
// add_filter('woocommerce_booking_get_availability_rules', 'sz_set_booking_availability', 10, 3);

/**
 * Set timeslot area html based on users
 * @param string $block_html
 * @param array $available_blocks - index+associative array
 * @param array $blocks index array
 * @return string
 */
function sz_set_timeslot_field($block_html, $available_blocks, $blocks)
{
    $memberships = [VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID];
    $user = get_current_user_id();
    if (is_vip($user, ...$memberships)) {
        return $block_html;
    }

    // Do not provide evening timeslots to those unqualified visitors
    $new_block_html = '';

    $new_available_blocks = array_filter($available_blocks, function ($block) use ($blocks) {
        return in_array($block, $blocks);
    }, ARRAY_FILTER_USE_KEY);

    // new WC_Booking_Form->get_time_slots_html
    foreach ($new_available_blocks as $block => $quantity) {
        if ($quantity['available'] > 0) {
            if ($quantity['booked']) {
                /* translators: 1: quantity available */
                $new_block_html .= '<li class="block" data-block="' . esc_attr(date('Hi', $block)) . '" data-remaining="' . esc_attr($quantity['available']) . '" ><a href="#" data-value="' . get_time_as_iso8601($block) . '">' . date_i18n(wc_bookings_time_format(), $block) . ' <small class="booking-spaces-left">(' . sprintf(_n('%d left', '%d left', $quantity['available'], 'woocommerce-bookings'), absint($quantity['available'])) . ')</small></a></li>';
            } else {
                $new_block_html .= '<li class="block" data-block="' . esc_attr(date('Hi', $block)) . '"><a href="#" data-value="' . get_time_as_iso8601($block) . '">' . date_i18n(wc_bookings_time_format(), $block) . '</a></li>';
            }
        }
    }

    return $new_block_html;
}
// add_filter('wc_bookings_get_time_slots_html', 'sz_set_timeslot_field', 10, 3);

/**
 * Add discount field in 'Singular Passes'
 * @return null
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

    $user = get_current_user_id();

    // Get available discounts from the db
    $promo_count = query_promo_times($user, 'Archery');
    $vip_count = query_vip_times($user);

    $total_promo_count = $promo_count + $vip_count;

    $promo_label = "Use Promo ($total_promo_count left";
    $promo_label .= is_null($vip_count) ? ")" : ", including $vip_count free VIP discount)";

    // Get used discounts from cart items
    $promo_cart_count = 0;
    $vip_cart_count = 0;

    foreach (sz_get_cart() as $cart_item) {
        if ($cart_item['product_id'] !== SINGULAR_ID) {
            continue;
        }

        // Count VIP and Promo for specific resource
        foreach ($cart_item['discount'] as $discount) {
            $cart_discount_type = $discount['type'];
            $cart_discount_qty = $discount['qty'];
            if (strpos($cart_discount_type, 'Use VIP') !== false) {
                $vip_cart_count += $cart_discount_qty;
                break;
            }
            if (strpos($cart_discount_type, 'Use Promo') !== false && $cart_item['booking']['_persons'][ARCHERY_ID]) {
                $promo_cart_count += $cart_discount_qty;
                break;
            }
        }
    }

    // Append extra discount info in the cart to the label
    if ($promo_cart_count xor $vip_cart_count) {
        $cart_discount_type = $promo_cart_count > $vip_cart_count ? 'Promo' : 'VIP';
        $cart_discount_qty = max($promo_cart_count, $vip_cart_count);
        $promo_label .= " ($cart_discount_qty $cart_discount_type being deducted in the cart)";
    }
    if ($promo_cart_count && $vip_cart_count) {
        $promo_label .= " ($promo_cart_count Promo, $vip_cart_count VIP being deducted in the cart)";
    }
    $available_promo_count = $total_promo_count - $promo_cart_count - $vip_cart_count;

    ?>

        <div>
            <input type="checkbox" id="promo-enable" name="promo-enable" data-price=<?=esc_attr($price);?> data-promo=<?=esc_attr($available_promo_count);?> <?=esc_attr($available_promo_count ? '' : 'disabled');?>>
            <label for="promo-enable"><?=esc_html__($promo_label);?></label>
        </div>

    <?php

    // Display Use Guest for VIP 888 only
    if (!wc_memberships_is_user_active_member($user, VIP_888_ANNUAL_ID)) {
        return;
    }

    $guest_count = query_guest_times($user);
    $guest_label = "Pay For Guests ($guest_count left)";

    // Get used discounts from cart items
    $guest_cart_count = 0;
    foreach (sz_get_cart() as $cart_item) {
        if ($cart_item['product_id'] !== SINGULAR_ID) {
            continue;
        }

        // Count Guest Pass for specific resource
        foreach ($cart_item['discount'] as $discount) {
            $cart_discount_type = $discount['type'];
            $cart_discount_qty = $discount['qty'];
            if (strpos($cart_discount_type, 'Pay For Guests') !== false) {
                $guest_cart_count += $cart_discount_qty;
                break;
            }
        }
    }

    // Append extra discount info in the cart to the label
    if ($guest_cart_count) {
        $plural = $guest_cart_count === 1 ? '' : 'es';
        $guest_label .= " ($guest_cart_count Guest Pass$plural being deducted in the cart)";
    }
    $available_guest_count = $guest_count - $guest_cart_count;

    ?>

        <div>
            <input type="checkbox" id="guest-enable" name="guest-enable" data-price=<?=esc_attr($price);?> data-guest=<?=esc_attr($available_guest_count);?> <?=esc_attr($available_guest_count ? '' : 'disabled');?>>
            <label for="guest-enable"><?=esc_html__($guest_label);?></label>
        </div>

        <div class="sz-select-field" style="display:none">
            <label for="guest-qty">Quantity:</label>
            <select name="guest-qty" id="guest-qty">
            </select>
        </div>

    <?php

    echo '</div>';
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'render_discount_field');

/**
 * Validate the discount quantity
 * @param array $passed
 * @param int $product_id
 * @param int $quantity
 * @return bool
 */
function sz_validate_discount_qty($passed, $product_id, $quantity)
{
    if ($product_id == SINGULAR_ID) {

        // Check if leaving a select dropdown blank
        foreach (['byoe', 'promo', 'guest'] as $field) {
            if (isset($_POST["$field-enable"]) && isset($_POST["$field-qty"]) && empty($_POST["$field-qty"])) {
                $passed = false;
                wc_add_notice(__('Please input the discount quantity you want to use!', 'woocommerce'), 'error');
                break;
            }
        }

        // Check if #discounts is less than #persons
        $discount = 0;
        $persons = 0;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'wc_bookings_field_persons_') !== false && +sanitize_text_field($value) > 0) {
                $persons = +sanitize_text_field($value);
                break;
            }
        }
        foreach (['promo', 'guest'] as $field) {
            if (isset($_POST["$field-enable"])) {
                $qty = sanitize_text_field($_POST["$field-qty"]);
                $discount += $qty ? +$qty : 1;
            }
            if ($discount > $persons) {
                $passed = false;
                wc_add_notice(__('The quantity of discounts exceeds the total booking quantity!', 'woocommerce'), 'error');
                break;
            }
        }
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'sz_validate_discount_qty', 10, 3);

/**
 * Add the entries of discounts in the cart item data with validation. Fire at the beginning of $cart_item_data initialization?
 * @param array? $cart_item_data
 * @param int? $product
 * @param string $variation
 * @return array
 */
function add_discount_info_into_cart($cart_item_data, $product_id, $variation)
{
    if ($product_id != SINGULAR_ID) {
        return $cart_item_data;
    }

    $cart_item_data['discount'] = [];

    if (!isset($_POST["byoe-enable"]) && !isset($_POST["promo-enable"]) && !isset($_POST["guest-enable"])) {
        return $cart_item_data;
    }

    $resource = sanitize_text_field($_POST['sz-resources']);

    // Must match the input name at the client side
    $fields = ['byoe', 'promo', 'guest'];
    foreach ($fields as $field) {

        // Continue if discount is not enabled
        if (!isset($_POST["$field-enable"])) {
            continue;
        }

        // Continue if using 0 discount
        $qty = $_POST["$field-qty"];
        if (isset($qty) && empty($qty)) {
            continue;
        }

        $price = get_resource_price(SINGULAR_ID, $resource);

        // Discount quantity will be 1 if no select dropdown
        $qty = sanitize_text_field($qty) ? +sanitize_text_field($qty) : 1;
        $price_off = 0;
        $discount_type = '';

        if ($field === 'byoe') {
            $discount_type = 'Bring Your Own Equipment';
            $byoe_price = get_resource_byoe_price(SINGULAR_ID, $resource);
            $final_price = $byoe_price ?? $price;
            $price_off = $price - $final_price;

            $persons = 0;
            foreach ([ARCHERY_ID, AIRSOFT_ID, COMBO_ID] as $type) {
                $person = +sanitize_text_field($_POST["wc_bookings_field_persons_$type"]);
                if ($person > $persons) {
                    $persons = $person;
                    break;
                }
            }

            $qty = min($qty, $persons);
        }

        if ($field === 'promo') {
            $user = get_current_user_id();
            $resource_name = get_resource_title(SINGULAR_ID, $resource);

            // Discount validation. $vip_count should be also from the database
            $vip_count = query_vip_times($user);
            $promo_count = query_promo_times($user, $resource_name);
            $total_promo_count = $promo_count + $vip_count;

            // Get used discounts from cart items
            $promo_cart_count = 0;
            $vip_cart_count = 0;

            foreach (sz_get_cart() as $cart_item) {
                if ($cart_item['product_id'] !== SINGULAR_ID) {
                    continue;
                }

                // Skip self
                if ($cart_item_data['key'] === $cart_item['key']) {
                    continue;
                }

                // Count VIP and Promo for specific resource
                foreach ($cart_item['discount'] as $discount) {
                    $cart_discount_type = $discount['type'];
                    $cart_discount_qty = $discount['qty'];
                    if (strpos($cart_discount_type, 'Use VIP') !== false) {
                        $vip_cart_count += $cart_discount_qty;
                        break;
                    }
                    if (strpos($cart_discount_type, 'Use Promo') !== false && $cart_item['booking']['_persons'][$resource]) {
                        $promo_cart_count += $cart_discount_qty;
                        break;
                    }
                }
            }

            $available_vip_count = max($vip_count - $vip_cart_count, 0);
            $available_promo_count = max($promo_count - $promo_cart_count, 0);

            if ($available_vip_count >= $qty) {
                $discount_type = 'Use VIP';
            } else {
                if ($available_promo_count >= $qty) {
                    if (is_null($vip_count)) {
                        $discount_type = 'Use Promo';
                    } else {
                        $discount_type = 'Use Promo (no valid VIP)';
                    }
                } else {
                    // wc_add_notice(__('Selected discount type not valid!', 'woocommerce'), 'notice');
                    return $cart_item_data;
                }
            }

            $price_off = $price;
            //$qty = min($qty, $total_promo_count);
        }

        if ($field === 'guest') {
            $user = get_current_user_id();
            $guest_count = query_guest_times($user);

            // Get used discounts from cart items
            $guest_cart_count = 0;
            foreach (sz_get_cart() as $cart_item) {
                if ($cart_item['product_id'] !== SINGULAR_ID) {
                    continue;
                }

                // Skip self
                if ($cart_item_data['key'] === $cart_item['key']) {
                    continue;
                }

                // Count Guest for specific resource
                foreach ($cart_item['discount'] as $discount) {
                    $cart_discount_type = $discount['type'];
                    $cart_discount_qty = $discount['qty'];
                    if (strpos($cart_discount_type, 'Pay For Guests') !== false) {
                        $guest_cart_count += $cart_discount_qty;
                        break;
                    }
                }
            }

            $available_guest_count = max($guest_count - $guest_cart_count, 0);

            if ($available_guest_count >= $qty) {
                $discount_type = 'Pay For Guests';
            } else {
                return $cart_item_data;
            }

            $price_off = $price;
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
 * @param array $cart_item_data
 * @param mixed $cart_item?
 * @return mixed?
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
        $total = max($total - $price_off, 0);
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