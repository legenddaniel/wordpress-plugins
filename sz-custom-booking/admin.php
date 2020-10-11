<?php
// Admin Dashboard Components

/**
 * @desc Template of custom field
 * @param int $resource
 * @param string $product
 * @return array
 */
function create_admin_byoe_field($resource, $product)
{
    // Return if not the exact product types
    if ($product !== strval(SINGULAR_ID)) {
        return;
    }
    if ($resource !== 291 && $resource !== 293) {
        return;
    }

    switch ($resource) {
        case 291:
            $id = 'byoe_price_archery';
            $value = '17.5';
            break;
        case 293:
            $id = 'byoe_price_combo';
            $value = '57.25';
            break;
    }
    $field = array(
        'id'            => $id,
        'label'         => __("BYOE Price" . " resource_id:" . $resource . ":" . gettype($resource) . " product_id:" . $product . ":" . gettype($product), "woocommerce"),
        'class' => 'wc_input_price',
        'data_type'     => 'price',
        'wrapper_class' => 'form-row form-row-first',
        'value'         => $value
    );
    return $field;
}

/**
 * @desc Add BYOE price setting field
 * @return void
 */
function admin_byoe_field($resource, $product)
{
    // Return if not the exact product types
    if ($product !== strval(SINGULAR_ID)) {
        return;
    }
    if ($resource !== 291 && $resource !== 293) {
        return;
    }
    
    $field = create_admin_byoe_field($resource, $product);
    woocommerce_wp_text_input($field);
}
add_action('woocommerce_bookings_after_resource_cost', 'admin_byoe_field', 10, 2);
