<?php

// Public functions and utilities

/**
 * Fetch BYOE enability or discount price in the admin
 * @param int $resource
 */
function get_byoe_price($resource)
{
    global $wpdb;
    $byoe_info = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
            FROM $wpdb->postmeta
            WHERE post_id = %d AND meta_key = %s",
            [$resource, 'byoe_price']
        )
    );

    return (is_null($byoe_info) || $byoe_info === 'N/A') ? null : sanitize_text_field($byoe_info);
}

/**
 * Get short name of the resource in a quick manner. Do not need to visit db.
 * @param int $resource
 * @return string|null
 */
function sz_get_resource_short_slug($resource)
{
    switch ($resource) {
        case ARCHERY_ID:
            $slug = 'archery';
            break;
        case AIRSOFT_ID:
            $slug = 'airsoft';
            break;
        case COMBO_ID:
            $slug = 'combo';
            break;
        default:
            return;
    }
    return $slug;
}

/**
 * Retrieve current cart items
 * @return array cart items
 */
function sz_get_cart()
{
    return WC()->cart->get_cart();
}

/**
 * Wrap a div around the output for styling
 * @param function $callback - function to execute
 * @return null
 */
function sz_wrap_admin_custom_field($callback)
{
    echo '<div class="admin-discount-field">';
    $callback();
    echo '</div>';
}

/**
 * Template of morning/evening discount enabling checkboxes
 * @param string $time - 'morning' | 'evening'
 * @return array
 */
function sz_create_admin_time_discount_checkbox_field($time)
{
    if ($time !== 'morning' && $time !== 'evening') {
        return;
    }

    $id = "admin-$time-discount-enable";

    $field = [
        'id' => $id,
        'label' => esc_html__('Enable ' . ucfirst($time) . ' Discount', 'woocommerce'),
        'class' => 'sz-admin-checkbox-enable',
        'style' => '',
        'wrapper_class' => 'form-row form-row-first',
        'value' => '',
        'custom_attributes' => [
            'checked' => true,
        ],
    ];
    return $field;
}

/**
 * Template of morning/evening discount info input in booking
 * @param string $time - 'morning' | 'evening'
 * @param string $side - 'from' | 'to'
 * @return array
 */
function sz_create_admin_time_discount_time_input_field($time, $side)
{
    if ($time !== 'morning' && $time !== 'evening') {
        return;
    }
    if ($side !== 'from' && $side !== 'to') {
        return;
    }

    $id = "admin-$time-discount-time-$side";

    $field = [
        'id' => $id,
        'label' => esc_html__(ucfirst($side), 'woocommerce'),
        'type' => 'time',
        'wrapper_class' => 'form-row form-row-first',
        'custom_attributes' => [
            'step' => 3600000,
        ],
    ];
    return $field;
}

/**
 * Template of morning/evening discount info input in booking
 * @param string $time - 'morning' | 'evening'
 * @return array
 */
function sz_create_admin_time_discount_price_input_field($time)
{
    if ($time !== 'morning' && $time !== 'evening') {
        return;
    }

    $id = "admin-$time-discount-time-price";

    $field = [
        'id' => $id,
        'label' => esc_html__(ucfirst($time) . ' Discount Price', 'woocommerce'),
        'type' => 'number',
        'data_type' => 'price',
        'class' => '',
        'wrapper_class' => 'form-row form-row-first',
        'value' => '',
        'custom_attributes' => [
            'step' => 0.01,
            'min' => 0,
        ],
    ];

    return $field;
}

/**
 * Template of byoe enabling checkboxes
 * @param int $resource
 * @return array
 */
function sz_create_admin_byoe_checkbox_field($resource)
{
    $type = sz_get_resource_short_slug($resource);
    $id = 'admin-byoe-enable-' . $type;

    $field = [
        'id' => $id,
        'label' => esc_html__("Enable Bring Your Own Equipment Discount", "woocommerce"),
        'class' => 'sz-admin-checkbox-enable',
        'wrapper_class' => 'form-row form-row-first',
        'value' => true,
        'custom_attributes' => is_null(get_byoe_price($resource)) ? '' : [
            'checked' => true,
        ],
    ];
    return $field;
}

/**
 * Template of byoe text fields
 * @param int $resource
 * @return array
 */
function sz_create_admin_byoe_input_field($resource)
{
    $type = sz_get_resource_short_slug($resource);
    $id = "admin-byoe-price-$type";
    $value = get_byoe_price($resource);
    $value = is_null($value) ? '' : $value;

    $field = [
        'id' => $id,
        'label' => esc_html__('Bring Your Own Equipment Price', 'woocommerce'),
        'type' => 'number',
        'data_type' => 'price',
        'class' => '',
        'wrapper_class' => is_null(get_byoe_price($resource)) ? 'form-row form-row-first d-none' : 'form-row form-row-first',
        'value' => $value,
        'custom_attributes' => [
            'step' => 0.01,
            'min' => 0,
        ],
    ];
    return $field;
}

/**
 * Template of discount info checkboxes in booking
 * @param string $type
 * @return array
 */
function sz_create_admin_booking_discount_checkbox_field($type)
{
    $id = str_replace(' ', '-', $type);
    $id = preg_replace('/\(|\)/m', '', $id);
    $id = strtolower($id);

    $field = [
        'id' => $id,
        'label' => esc_html__($type, 'woocommerce'),
        'class' => 'sz-admin-checkbox-enable',
        'custom_attributes' => [
            'checked' => true,
            'onclick' => 'return false;',
        ],
    ];
    return $field;
}

/**
 * Template of discount info input in booking
 * @param int $qty
 * @return array
 */
function sz_create_admin_booking_discount_input_field($qty)
{
    $qty = +$qty;

    $field = [
        'id' => 'discount-qty',
        'label' => esc_html__('Quantity', 'woocommerce'),
        'type' => 'number',
        'value' => $qty,
        'custom_attributes' => [
            'readonly' => true,
        ],
    ];
    return $field;
}

/**
 * Get price for a person resource of a product
 * @param string $product_id
 * @param int $resource_id
 * @return Double|int|null
 */
function get_resource_price($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_person_types()) {
        return;
    }

    global $wpdb;
    $price = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
            FROM $wpdb->postmeta
            WHERE post_id = %d AND meta_key = %s",
            [$resource_id, 'cost']
        )
    );

    return sanitize_text_field($price);
}

/**
 * Get BYOE price for a person resource of a product, if enabled
 * @param string $product_id
 * @param int $resource_id
 * @return Double|int|null
 */
function get_resource_byoe_price($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_person_types()) {
        return;
    }

    $byoe_price = get_byoe_price($resource_id);

    return is_null($byoe_price) ? null : +$byoe_price;
}

/**
 * Get title for a person resource of a product
 * @param string $product_id
 * @param int $resource_id
 * @return string|null
 */
function get_resource_title($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_person_types()) {
        return;
    }

    global $wpdb;
    $title = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_title
            FROM $wpdb->posts
            WHERE ID = %d",
            $resource_id
        )
    );

    return sanitize_text_field($title);
}

/**
 * Check if a user is a VIP
 * @param int $user
 * @param int,... $memberships
 * @return boolean
 */
function is_vip($user, ...$memberships)
{
    if (!is_user_logged_in()) {
        return;
    }

    foreach ($memberships as $membership) {
        if (wc_memberships_is_user_active_member($user, $membership)) {
            return true;
        }

    }
    return false;
}

/**
 * Query the promo remaining for the given type
 * @param int $user
 * @param string $type
 * @return int|null
 */
function query_promo_times($user, $type)
{
    global $wpdb;
    $promo_times = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
             FROM $wpdb->usermeta
             WHERE meta_key LIKE %s
             AND user_id = %d",
            ["%$type%", $user]
        )
    );
    return $promo_times ?? 0;
}

/**
 * Query the vip remaining for VIP
 * @param int $user
 * @return int|null
 */
function query_vip_times($user)
{
    if (!is_VIP($user, VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID)) {
        return;
    }

    global $wpdb;
    $vip_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
             FROM $wpdb->usermeta
             WHERE meta_key = %s
             AND user_id = %d",
            ["VIP", $user]
        )
    );
    return $vip_count;
}

/**
 * Query the guest passes remaining for vvVIP
 * @param int $user
 * @return int|null
 */
function query_guest_times($user)
{
    if (!is_VIP($user, VIP_888_ANNUAL_ID)) {
        return;
    }

    global $wpdb;
    $guest_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value
             FROM $wpdb->usermeta
             WHERE meta_key = %s
             AND user_id = %d",
            ["Guest", $user]
        )
    );
    return $guest_count;
}
