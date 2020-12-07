<?php

// Public functions and utilities

/**
 * Fetch BYOE enability or discount price in the admin
 * @param Integer $resource
 * @return String|Null
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
 * Template of byoe enabling checkboxes
 * @param Integer $resource
 * @param String $product
 * @return Array
 */
function create_admin_byoe_enabling_checkbox($resource)
{
    switch ($resource) {
        case ARCHERY_ID:
            $type = 'archery';
            break;
        case AIRSOFT_ID:
            $type = 'airsoft';
            break;
        case COMBO_ID:
            $type = 'combo';
            break;
    }
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
 * @param Integer $resource
 * @param String $product
 * @return Array
 */
function create_admin_byoe_input_field($resource)
{
    switch ($resource) {
        case ARCHERY_ID:
            $type = 'archery';
            break;
        case AIRSOFT_ID:
            $type = 'airsoft';
            break;
        case COMBO_ID:
            $type = 'combo';
            break;
    }
    $id = "admin-byoe-price-$type";
    $value = get_byoe_price($resource);
    $value = is_null($value) ? '' : $value;

    $field = [
        'id' => $id,
        'label' => esc_html__('Bring Your Own Equipment Price', 'woocommerce'),
        'type' => 'number',
        'class' => 'sz-admin-byoe-input',
        'data_type' => 'price',
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
 * @param String $type
 * @return Array
 */
function create_admin_booking_discount_checkbox_field($type)
{
    $id = str_replace(' ', '-', $type);
    $id = preg_replace('/\(|\)/m', '', $id);
    $id = strtolower($id);

    $field = [
        'id' => $id,
        'label' => esc_html__($type, 'woocommerce'),
        'class' => 'sz-admin-checkbox-enable',
        'style' => '',
        'wrapper_class' => '',
        'value' => '',
        'custom_attributes' => [
            'checked' => true,
            'onclick' => 'return false;',
        ],
    ];
    return $field;
}

/**
 * Template of discount info input in booking
 * @param Integer $qty
 * @return Array
 */
function create_admin_booking_discount_input_field($qty)
{
    $qty = +$qty;

    $field = [
        'id' => 'discount-qty',
        'label' => esc_html__('Quantity', 'woocommerce'),
        'type' => 'number',
        'class' => '',
        'wrapper_class' => '',
        'value' => $qty,
        'custom_attributes' => [
            'readonly' => true,
        ],
    ];
    return $field;
}

/**
 * Get price for a person resource of a product
 * @param String $product_id
 * @param Integer $resource_id
 * @return Double|Integer|Null
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
 * @param String $product_id
 * @param Integer $resource_id
 * @return Double|Integer|Null
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
 * @param String $product_id
 * @param Integer $resource_id
 * @return String|Null
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
 * @param Integer $user
 * @param Integer,... $memberships
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
 * @param Integer $user
 * @param String $type
 * @return Integer|Null
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
 * @param Integer $user
 * @return Integer|Null
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
 * @param Integer $user
 * @return Integer|Null
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
