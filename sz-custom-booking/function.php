<?php

// Public functions and utilities

/**
 * Fetch BYOE enability or discount price in the admin
 * @param Integer $resource
 * @param Boolean $separate - Count 'N/A' as null or not
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
function create_admin_byoe_enabling_checkbox($resource, $product)
{
    if ($product != SINGULAR_ID) {
        return;
    }

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
        'class' => 'sz-admin-byoe-enable',
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
function create_admin_byoe_input_field($resource, $product)
{
    if ($product != SINGULAR_ID) {
        return;
    }

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
 * @param Array $discount
 * @return Array
 */
function create_admin_booking_discount_checkbox_field($discount)
{
    $type = $discount['type'];
    $id = str_replace(' ', '-', $type);
    $id = preg_replace('/\(|\)/m', '', $id);
    $id = strtolower($id);

    $field = [
        'id' => $id,
        'label' => esc_html__($type, 'woocommerce'),
        'class' => '',
        'style' => '',
        'wrapper_class' => '',
        'value' => '',
        'custom_attributes' => '',
    ];
    return $field;
}

/**
 * Get price for a resource of a product
 * @param String $product_id
 * @param Integer $resource_id
 * @return Double|Integer|Null
 */
function get_resource_price($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_resources()) {
        return;
    }

    $price = $product->get_resource($resource_id)->get_base_cost();
    return $price;
}

/**
 * Get BYOE price for a resource of a product, if enabled
 * @param String $product_id
 * @param Integer $resource_id
 * @return Double|Integer|Null
 */
function get_resource_byoe_price($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_resources()) {
        return;
    }

    $byoe_price = get_byoe_price($resource_id);

    return is_null($byoe_price) ? null : +$byoe_price;
}

/**
 * Get title for a resource of a product
 * @param String $product_id
 * @param Integer $resource_id
 * @return String|Null
 */
function get_resource_title($product_id, $resource_id)
{
    $product = wc_get_product($product_id);
    if (!$product->has_resources()) {
        return;
    }

    $title = $product->get_resource($resource_id)->get_title();
    return $title;
}

/**
 * Check if the current user is a VIP
 * @param Integer,... $memberships
 * @return boolean
 */
function is_vip(...$memberships)
{
    if (!is_user_logged_in()) {
        return;
    }

    $user = get_current_user_id();
    foreach ($memberships as $membership) {
        if (wc_memberships_is_user_active_member($user, $membership)) {
            return true;
        }

    }
    return false;
}

/**
 * Query the promo remaining for the given type
 * @param String $type
 * @return Integer|Null
 */
function query_promo_times($type)
{
    global $wpdb;
    $user = get_current_user_id();
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
 * Query the vip remaining for the given types
 * @param String,... $types
 * @return Integer|Null
 */
function query_vip_times(...$types)
{
    if (!is_VIP(...$types)) {
        return;
    }

    global $wpdb;
    $user = get_current_user_id();
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
