<?php
// Admin Dashboard Components

is_admin() or exit;

/**
 * Load CSS and JavaScript
 * @return Null
 */
function admin_init_assets()
{
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'style',
        "{$plugin_url}style-admin.css",
        [],
        rand(111, 9999)
    );
    wp_enqueue_script(
        'admin',
        "{$plugin_url}admin.js",
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
        $byoe_enable = sanitize_text_field($_POST["admin_byoe_enable_$type"]);
        $byoe_price = sanitize_text_field($_POST["admin_byoe_price_$type"]);

        if ($byoe_enable) {
            if ($byoe_price === '') {
                continue;
            } else {
                $byoe_price = number_format($byoe_price, 2);
            }
        } else {
            $byoe_price = 'N/A';
        }

        $byoe_db = get_byoe_price($id, true);
        if (is_null($byoe_db)) {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO $wpdb->postmeta
                    (post_id, meta_key, meta_value)
                    VALUES (%d, %s, %s)",
                    [$id, 'byoe_price', $byoe_price]
                )
            );
            return;
        }
        if ($byoe_db !== $byoe_price) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->postmeta
                    SET meta_value = %s
                    WHERE post_id = %d AND meta_key = %s",
                    [$byoe_price, $id, 'byoe_price']
                )
            );
            return;
        }
    }
}
add_action('woocommerce_process_product_meta', 'admin_save_byoe_field');

/*function admin_add_booking_details($booking_id)
{
$booking = new WC_Booking($booking_id);
echo 'Will add discount info here';
}
add_action('woocommerce_admin_booking_data_after_booking_details', 'admin_add_booking_details');*/
