<?php
// Admin Dashboard Components

is_admin() or exit;

/**
 * Load CSS and JavaScript
 * @return null
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
 * @param int $resource (person)
 * @return null
 */
function sz_admin_byoe_field($resource)
{
    if (in_array($resource, [ARCHERY_ID, AIRSOFT_ID, COMBO_ID])) {
        $checkbox_field = sz_create_admin_byoe_checkbox_field($resource);
        $text_field = sz_create_admin_byoe_input_field($resource);

        $display = !is_null(get_byoe_price($resource));
        woocommerce_wp_checkbox($checkbox_field);
        sz_wrap_admin_custom_field($display, function () use ($text_field) {
            woocommerce_wp_text_input($text_field);
        });

    }
}
add_action('woocommerce_bookings_after_person_block_cost', 'sz_admin_byoe_field');

/**
 * Save BYOE info in the database
 * @param int $post_id
 * @return null
 */
function sz_admin_save_byoe_field($post_id)
{
    if ($post_id !== SINGULAR_ID) {
        return;
    }

    $resources = [
        'archery' => ARCHERY_ID,
        'airsoft' => AIRSOFT_ID,
        'combo' => COMBO_ID,
    ];

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
add_action('woocommerce_process_product_meta', 'sz_admin_save_byoe_field');

/**
 * Save morning/evening discount info in the database
 * @param int $post_id
 * @return null
 */
function sz_admin_save_time_discount_field($post_id)
{
    if ($post_id !== SINGULAR_ID) {
        return;
    }

    foreach (['morning', 'evening'] as $time) {
        $time_discount_enable = sanitize_text_field($_POST["admin-$time-discount-enable"]);
        $time_discount_from = sanitize_text_field($_POST["admin-$time-discount-time-from"]);
        $time_discount_to = sanitize_text_field($_POST["admin-$time-discount-time-to"]);
        $time_discount_price = sanitize_text_field($_POST["admin-$time-discount-price"]);

        $time_discount = [
            'enable' => !!$time_discount_enable,
            'from' => $time_discount_from,
            'to' => $time_discount_to,
            'price' => $time_discount_price,
        ];

        $update = true;
        foreach ($time_discount as $key => $value) {
            if ($key !== 'enable' && $value === '') {
                $update = false;
                break;
            }
        }
        if ($update) {
            // update_post_meta($post_id, $time . '_discount, $time_discount);
        }

    }
}
// add_action('woocommerce_process_product_meta', 'sz_admin_save_time_discount_field');

/**
 * Add morning/evening discount setting field
 * @param int $product
 * @return null
 */
function sz_admin_time_discount_field($product)
{
    if ($product == SINGULAR_ID) {
        foreach (['morning', 'evening'] as $time) {
            $checkbox_field = sz_create_admin_time_discount_checkbox_field($time);
            $text_field_from = sz_create_admin_time_discount_time_input_field($time, 'from');
            $text_field_to = sz_create_admin_time_discount_time_input_field($time, 'to');
            $text_field_price = sz_create_admin_time_discount_price_input_field($time);

            woocommerce_wp_checkbox($checkbox_field);
            sz_wrap_admin_custom_field(true, function () use ($text_field_from, $text_field_to, $text_field_price) {
                woocommerce_wp_text_input($text_field_from);
                woocommerce_wp_text_input($text_field_to);
                woocommerce_wp_text_input($text_field_price);
            });
        }
    }
}
add_action('woocommerce_bookings_after_bookings_pricing', 'sz_admin_time_discount_field');

/**
 * Display booking discount info in booking page in admin
 * @param int $booking_id
 * @return null
 */
function sz_admin_add_booking_details($booking_id)
{
    $booking = new WC_Booking($booking_id);
    $id = $booking->get_order_item_id();
    $discounts = wc_get_order_item_meta($id, 'discount');

    if (empty($discounts)) {
        echo 'No discount applied to this booking.';
        return;
    }

    foreach ($discounts as $discount) {
        $checkbox_field = sz_create_admin_booking_discount_checkbox_field($discount['type']);
        $text_field = sz_create_admin_booking_discount_input_field($discount['qty']);

        woocommerce_wp_checkbox($checkbox_field);
        woocommerce_wp_text_input($text_field);
    }

}
add_action('woocommerce_admin_booking_data_after_booking_details', 'sz_admin_add_booking_details');

/**
 * Display Pass and VIP info in the admin->user->edit profile
 * @param WP_User
 * @return null
 */
function sz_admin_add_user_passes_field($user)
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
    if (is_vip($user_id, VIP_888_ANNUAL_ID)) {
        ?>
                <tr>
                    <th>
                        <label for="editGuest">Guest</label>
                    </th>
                    <td>
                        <input type="number" min="0" max="<?=esc_attr(GUEST_QTY);?>" name="editGuest" id="editGuest" value="<?=esc_attr(query_guest_times($user_id));?>" class="regular-text">
                    </td>
                </tr>
        <?php
}
    ?>

            </tbody>
        </table>

    <?php
}
add_action('show_user_profile', 'sz_admin_add_user_passes_field');
add_action('edit_user_profile', 'sz_admin_add_user_passes_field');

/**
 * Save Pass and VIP info in the admin->user->edit profile to the db
 * @param int $user_id
 * @return null
 */
function sz_admin_save_user_passes_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    foreach (['Archery', 'Airsoft', 'Combo', 'VIP', 'Guest'] as $pass) {
        $qty = sanitize_text_field($_POST["edit$pass"]);
        if (!$qty) {
            continue;
        }

        $key = '';
        switch ($pass) {
            case 'VIP':
                $key = $pass;
                break;
            case 'Guest':
                $key = $pass;
                break;
            default:
                $key = "Promo Passes 10+1 - $pass";
                break;
        }

        update_user_meta($user_id, $key, $qty);

    }
}
add_action('personal_options_update', 'sz_admin_save_user_passes_field');
add_action('edit_user_profile_update', 'sz_admin_save_user_passes_field');

/**
 * Do not show unpaid bookings from checkout on calendar
 * @param array $booking_ids
 * @return array
 */
function sz_admin_remove_unpaid_checkout_bookings_from_calendar($booking_ids)
{
    return array_filter($booking_ids, function ($id) {
        $is_unpaid = get_post_status($id) === 'unpaid';
        $is_checkout = false;
        if ($is_unpaid) {
            $booking = new WC_Booking($id);
            $order_id = $booking->get_order_id();
            $is_checkout = get_post_meta($order_id, '_created_via', true) === 'checkout';
        }
        return !($is_unpaid && $is_checkout);
    });
}
add_filter('woocommerce_bookings_in_date_range_query', 'sz_admin_remove_unpaid_checkout_bookings_from_calendar');