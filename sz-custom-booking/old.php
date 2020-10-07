<?php
// Functions that are deprecated temporarily or permanently

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
    ?>

<div class="sz-pos-m">
    <input type="checkbox" id="byoe" name="byoe" value="byoe">
    <label for="byoe" title="Only apply to Archery">Bring Your Own Equipment (up to 50% off)<sup>?</sup></label>
</div>

<?php
}
add_action('woocommerce_before_add_to_cart_button', 'add_byoe_checkbox');

/**
 * @desc Add 'Use Promo' checkbox in 'Singular Passes'
 * @return void
 */
function add_promo_checkbox()
{
    global $promo_count; ?>

<div class="sz-pos-m">
    <input type="checkbox" id="byoe" name="byoe" value="byoe">
    <label for="byoe" title="Use Promo">Use Promo (<?php echo $promo_count; ?> times left)</label>
</div>

<?php
}
add_action('woocommerce_before_add_to_cart_button', 'add_promo_checkbox');

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
