<?php
// Admin Dashboard Components

is_admin() or exit;

/**
 * Load CSS and JavaScript
 * @return Null
 */
function init_admin_assets()
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
add_action('admin_enqueue_scripts', 'init_admin_assets');

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
    $id = 'admin_byoe_enable_' . $type;

    $field = [
        'id' => $id,
        'label' => __("Enable BYOE Discount", "woocommerce"),
        'class' => 'sz-admin-byoe-enable',
        'wrapper_class' => 'form-row form-row-first',
        'value' => true,
        'custom_attributes' => [
            'checked' => true, // will retrieve from database
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
    $id = "admin_byoe_price_$type";

    $field = [
        'id' => $id,
        'label' => __('BYOE Price', 'woocommerce'),
        'type' => 'number',
        'class' => 'sz-admin-byoe-input',
        'data_type' => 'price',
        'wrapper_class' => 'form-row form-row-first',
        'value' => $value,
        'custom_attributes' => [
            'step' => 0.01,
            'min' => 0,
        ],
    ];
    return $field;
}

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
function save_byoe_field($post_id)
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
        $byoe_enable = !!$_POST["admin_byoe_enable_$type"];
        $byoe_price = number_format($_POST["admin_byoe_price_$type"], 2);

        $fields = [
            'byoe_enable' => $byoe_enable,
            'byoe_price' => $byoe_price,
        ];

        global $wpdb;

        foreach ($fields as $field => $value) {
            $byoe_db = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT meta_value
                    FROM $wpdb->postmeta
                    WHERE post_id = %d AND meta_key = %s",
                    [$id, $field]
                )
            );

            if (is_null($byoe_db)) {
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO $wpdb->postmeta
                        (post_id, meta_key, meta_value)
                        VALUES (%d, %s, %s)",
                        [$id, $field, $value]
                    )
                );
            } elseif (+$byoe_db !== $value) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE $wpdb->postmeta
                        SET meta_value = %s
                        WHERE post_id = %d AND meta_key = %s",
                        [$value, $id, $field]
                    )
                );
            }
        }
    }
}
add_action('woocommerce_process_product_meta', 'save_byoe_field');

/*function admin_add_booking_details($booking_id)
{
$booking = new WC_Booking($booking_id);
echo 'Will add discount info here';
}
add_action('woocommerce_admin_booking_data_after_booking_details', 'admin_add_booking_details');*/
