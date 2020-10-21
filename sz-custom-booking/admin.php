<?php
// Admin Dashboard Components

/**
 * Load CSS and JavaScript
 * @return void
 */
function init_admin_assets()
{
    // Now it's loaded globally within all the admin dashboards, will make it only be loaded in specific product editing admin pages
    if (!is_admin()) {
        return;
    }
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'style',
        "{$plugin_url}style-admin.css",
        array(),
        rand(111, 9999)
    );
    wp_enqueue_script(
        'admin',
        "{$plugin_url}admin.js",
        array('jquery'),
        rand(111, 9999)
    );
}
add_action('admin_enqueue_scripts', 'init_admin_assets');

/**
 * Template of byoe enabling checkboxes
 * @param int $resource
 * @param string $product
 * @return array
 */
function create_admin_byoe_enabling_checkbox($resource, $product)
{
    if ($product !== strval(SINGULAR_ID)) {
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
    $id = 'admin_byoe_checkbox_' . $type;

    $field = array(
        'id'            => $id,
        'label'         => __("Enable BYOE Discount", "woocommerce"),
        'class'         => 'sz-admin-byoe-checkbox',
        'wrapper_class' => 'form-row form-row-first',
        'value'         => true,
        'custom_attributes' => array(
            'checked' => true, // will retrieve from database
        )
    );
    return $field;
}

/**
 * Template of byoe text fields
 * @param int $resource
 * @param string $product
 * @return array
 */
function create_admin_byoe_input_field($resource, $product)
{
    if ($product !== strval(SINGULAR_ID)) {
        return;
    }

    // Value come from the database in the future
    switch ($resource) {
        case ARCHERY_ID:
            $type = 'archery';
            $value = '17.5';
            break;
        case AIRSOFT_ID:
            $type = 'airsoft';
            $value = '';
            break;
        case COMBO_ID:
            $type = 'combo';
            $value = '57.25';
            break;
    }
    $id = 'admin_byoe_price_' . $type;

    $field = array(
        'id'                => $id,
        'label'             => __("BYOE Price" . " resource_id:" . $resource . ":" . gettype($resource) . " product_id:" . $product . ":" . gettype($product), "woocommerce"),
        'type'              => 'number',
        'class'             => 'sz-admin-byoe-input',
        'data_type'         => 'price',
        'wrapper_class'     => 'form-row',
        'value'             => $value,
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
    
    $checkbox_field = create_admin_byoe_enabling_checkbox($resource, $product);
    $text_field = create_admin_byoe_input_field($resource, $product);

    woocommerce_wp_checkbox($checkbox_field);
    woocommerce_wp_text_input($text_field);
}
add_action('woocommerce_bookings_after_resource_cost', 'admin_byoe_field', 10, 2);

function admin_add_booking_details($booking_id)
{
    $booking = new WC_Booking($booking_id);
    echo 'Will add discount info here';
}
add_action('woocommerce_admin_booking_data_after_booking_details', 'admin_add_booking_details');
