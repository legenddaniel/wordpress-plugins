<?php
// Admin Dashboard Components

is_admin() or exit;

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
        'admin',
        $plugin_url . 'js/admin.js',
        ['jquery'],
        rand(111, 9999)
    );
}
add_action('admin_enqueue_scripts', 'admin_init_assets');

/**
 * Add BYOE price setting field
 * @param Integer $resource (person)
 * @param Integer $product
 * @return Null
 */
function admin_byoe_field($resource)
{
    if (!in_array($resource, [ARCHERY_ID, AIRSOFT_ID, COMBO_ID])) {
        return;
    }

    $checkbox_field = create_admin_byoe_enabling_checkbox($resource);
    $text_field = create_admin_byoe_input_field($resource);

    woocommerce_wp_checkbox($checkbox_field);
    woocommerce_wp_text_input($text_field);
}
add_action('woocommerce_bookings_after_person_block_cost', 'admin_byoe_field', 10, 2);

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

/**
 * Display booking discount info in booking page in admin
 * @param Integer $booking_id
 * @return Null
 */
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

/**
 * Display Pass and VIP info in the admin->user->edit profile
 * @param WP_User
 * @return Null
 */
function admin_add_user_passes_field($user)
{
    ?>

        <h3><?=esc_html__('Promo Passes', 'woocommerce');?></h3>
        <table class="form-table">
            <tbody>
                <?php
$user_id = $user->ID;
    foreach (['Archery', 'Airsoft', 'Combo'] as $pass) {
        ?>

                <tr>
                    <th>
                        <label for="<?="edit$pass"?>"><?=esc_html__($pass, 'woocommerce');?></label>
                    </th>
                    <td>
                        <input type="number" min="0" name="<?="edit$pass"?>" id="<?="edit$pass"?>" value="<?=esc_attr(query_promo_times($user_id, $pass));?>" class="regular-text">
                    </td>
                </tr>

                <?php
}
    if (is_vip($user_id, VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID)) {
        $max = wc_memberships_is_user_active_member($user_id, VIP_888_ANNUAL_ID) ? VIP_888_QTY : VIP_REG_QTY;
        ?>
                        <tr>
                            <th>
                                <label for="editVIP">VIP</label>
                            </th>
                            <td>
                                <input type="number" min="0" max="<?=esc_attr($max);?>" name="editVIP" id="editVIP" value="<?=esc_attr(query_vip_times($user_id));?>" class="regular-text">
                            </td>
                        </tr>
                <?php
}
    ?>

            </tbody>
        </table>

    <?php
}
add_action('show_user_profile', 'admin_add_user_passes_field');
add_action('edit_user_profile', 'admin_add_user_passes_field');

/**
 * Save Pass and VIP info in the admin->user->edit profile to the db
 * @param Integer $user_id
 * @return Null
 */
function admin_save_user_passes_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    foreach (['Archery', 'Airsoft', 'Combo', 'VIP'] as $pass) {
        $qty = sanitize_text_field($_POST["edit$pass"]);
        if ($qty !== '') {
            $key = $pass === 'VIP' ? $pass : "Promo Passes 10+1 - $pass";
            update_user_meta($user_id, $key, $qty);
        }
    }
}
add_action('personal_options_update', 'admin_save_user_passes_field');
add_action('edit_user_profile_update', 'admin_save_user_passes_field');