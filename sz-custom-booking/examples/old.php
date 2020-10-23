<?php
// Functions that are deprecated temporarily or permanently

/**
 * @desc Set up AJAX at the front end for 'BYOE'
 * @return void
 */
function set_ajaxurl()
{
    echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}
add_action('wp_head', 'set_ajaxurl');

/** Unavailable
 * @desc Check if booking Archery or Combo
 * @return bool
 */
function is_promotable_type()
{
    $singular_passes = new WC_Booking(304);
    echo "<script>console.log($singular_passes)</script>";
    $resource_id = $singular_passes->get_resource_id();
    echo "<script>console.log($resource_id)</script>";
    return true;
    // return $resource_id === 291 || $resource_id === 293;
}

/**
 * @desc Add BYOE checkbox in 'Singular Passes'
 * @return void
 */
function add_byoe_checkbox()
{
    // woocommerce_form_field('byoe_archery', array(
    //     'type'        => 'checkbox',
    //     'label'       => 'Bring Your Own Equipment - Archery',
    //     'label_class' => array('sz-size-checkbox')
    // ), '17.5');
        // woocommerce_form_field('byoe_combo', array(
    //     'type'        => 'checkbox',
    //     'label'       => 'Bring Your Own Equipment - Combo',
    //     'label_class' => array('sz-size-checkbox'),
    //     'class'       => array('d-none')
    // ), '57.25');
    ?>

<div class="sz-pos-m">
    <input type="checkbox" id="byoe" name="byoe" value="byoe">
    <label for="byoe" title="Only apply to Archery">Bring Your Own Equipment (up to 50% off)<sup>?</sup></label>
</div>

<?php
}
add_action('woocommerce_before_add_to_cart_button', 'add_byoe_checkbox');

/**
 * @desc Remove '(optional)' text from the checkbox label in 'Singular Passes'
 * @param string $field
 * @param string $key
 * @param array $args
 * @param string? $value
 * @return void
 */
function remove_label_optional_text($field, $key, $args, $value)
{
    if (is_singular_pass()) {
        $optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
        $field = str_replace($optional, '', $field);
    }
    return $field;
}
add_filter('woocommerce_form_field', 'remove_label_optional_text', 10, 4);

/**
 * @desc Change text of notice if more than 5 persons booking
 * @return string
 */
function apply_info_if_more_than_five_persons()
{
    $singular_passes = new WC_Product_Booking(304);
    $data = wc_bookings_get_posted_data($_POST, $singular_passes);
    $persons = array_sum($data['_persons']);

    if ($persons > 5) {
        return 'Contact the host if more than 5 persons';
    }
}
add_filter('woocommerce_bookings_calculated_booking_cost_error_output', 'apply_info_if_more_than_five_persons');

/**
 * @desc Change the display of 'Singular Passes' price
 * @return string
 */
function apply_byoe_discount()
{
    $discounted_price = get_post_meta(SINGULAR_ID, 'cost', true);
    if ($discounted_price !== false) {
        $price_html = '<span class="amount">' . wc_price($discounted_price) . '</span>';
    }
    return $price_html;
}
add_filter('woocommerce_get_price_html', 'apply_byoe_discount');

// Modify actual quantity, will cause over calculation
foreach ($cart->cart_contents as $item_key => $item) {
    $cart->set_quantity($item_key, $item['booking']['_qty']);
    echo '<script>console.log(' . json_encode($item_key) .')</script>';
}

/**
 * @desc Add 'Use Promo' checkbox in 'Singular Passes'
 * @return void
 */
function add_promo_checkbox()
{
    // woocommerce_form_field('promo', array(
    //     'type'        => 'checkbox',
    //     'label'       => 'Use Promo' . " ($promo_count times left)",
    //     'label_class' => 'sz-size-checkbox',
    // ), '0');
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

/**
 * @desc Apply BYOE discount for Archery in 'Singular Passes'
 * @return void
 */
function apply_byoe_archery_discount()
{
    check_ajax_referer('byoe_archery_ajax');

    $product = wc_get_product(SINGULAR_ID);

    $price = $product->get_price();
    $discounted_price = number_format($price * 0.5, 2);

    $is_checked = $_POST['checked'];

    if ($is_checked) {
        echo $price;
    } else {
        echo $discounted_price;
    }

    wp_die();
}
add_action('wp_ajax_apply_byoe_archery_discount', 'apply_byoe_archery_discount');
add_action('wp_ajax_nopriv_apply_byoe_archery_discount', 'apply_byoe_archery_discount');




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

        <p>
            <input type="checkbox" id="promo-archery" name="promo" value="0">
            <label for="promo-archery">Use Promo (<?php echo $archery_promo_count; ?>
                left)</label>
        </p>

        <div class="txtAge" style="display:none">
            <div class="wdm-custom-fields">
                <label for="passes">How many passes to use:</label>
                <select name="wdm_name" id="passes">
                    <option selected="selected">1 t</option>
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

                    <option selected="selected">1</option>

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
                    <option selected="selected">1</option>
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