<?php
// Admin Dashboard Components

is_admin() or exit;

/**
 * Transpile scripts by Babel
 * @param String $tag
 * @param String $handle
 * @param String $src
 * @return String
 */
/*function sz_admin_babelize_script($tag, $handle, $src)
{
    $scripts = ['admin'];

    if (in_array($handle, $scripts)) {
        $tag = '<script type="text/babel" src="' . esc_url($src) . '" id="' . $handle . '-js"></script>';
    }

    return $tag;
}
add_filter('script_loader_tag', 'sz_admin_babelize_script', 10, 3);*/

/**
 * Load CSS and JavaScript
 * @return Null
 */
function admin_init_assets()
{
    $plugin_url = plugin_dir_url(__DIR__);

    wp_enqueue_style(
        'style',
        $plugin_url . 'css/style-admin.css',
        [],
        rand(111, 9999)
    );

    wp_enqueue_script(
        'babel',
        'https://unpkg.com/@babel/standalone/babel.min.js',
    );
    wp_enqueue_script(
        'admin',
        $plugin_url . 'js/admin.js',
        ['jquery'],
        rand(111, 9999)
    );
}
add_action('admin_enqueue_scripts', 'admin_init_assets');

/**
 * Add BYOE price setting field
 * @return Null
 */
function admin_byoe_field($resource, $product)
{
    // Return if not the exact product types
    if ($product != SINGULAR_ID) {
        return;
    }

    $checkbox_field = create_admin_byoe_enabling_checkbox($resource, $product);
    $text_field = create_admin_byoe_input_field($resource, $product);

    woocommerce_wp_checkbox($checkbox_field);
    woocommerce_wp_text_input($text_field);
}
add_action('woocommerce_bookings_after_resource_cost', 'admin_byoe_field', 10, 2);

/**
 * Save BYOE info in the database
 * @param Integer $post_id
 * @return Null
 */
function admin_save_byoe_field($post_id)
{
    if ($post_id !== SINGULAR_ID) {
        return;
    }

    $resources = [
        'archery' => ARCHERY_ID,
        'airsoft' => AIRSOFT_ID,
        'combo' => COMBO_ID,
    ];

    global $wpdb;

    foreach ($resources as $type => $id) {
        $byoe_enable = sanitize_text_field($_POST["admin-byoe-enable-$type"]);
        $byoe_price = sanitize_text_field($_POST["admin-byoe-price-$type"]);

        if ($byoe_enable) {
            if ($byoe_price === '') {
                continue;
            } else {
                $byoe_price = number_format($byoe_price, 2);
            }
        } else {
            $byoe_price = 'N/A';
        }

        update_post_meta($id, 'byoe_price', $byoe_price);
    }
}
add_action('woocommerce_process_product_meta', 'admin_save_byoe_field');

function admin_add_booking_details($booking_id)
{
    $booking = new WC_Booking($booking_id);
    $id = $booking->get_order_item_id();
    $discounts = wc_get_order_item_meta($id, 'discount');

    if (empty($discounts)) {
        echo 'No discount applied to this booking.';
        return;
    }

    foreach ($discounts as $discount) {
        $checkbox_field = create_admin_booking_discount_checkbox_field($discount['type']);
        $text_field = create_admin_booking_discount_input_field($discount['qty']);

        woocommerce_wp_checkbox($checkbox_field);
        woocommerce_wp_text_input($text_field);
    }

}
add_action('woocommerce_admin_booking_data_after_booking_details', 'admin_add_booking_details');