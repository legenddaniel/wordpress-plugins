<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class New_Point_Admin
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_init_assets'));

        // Add custom field identification
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_500', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_1000', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_up1000', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('wc_points_rewards_settings', array($this, 'change_plugin_settings'));
    }

    /**
     * Load CSS and JavaScript
     * @return void
     */
    public function admin_init_assets()
    {
        $plugin_url = plugin_dir_url(__FILE__);

        wp_enqueue_script(
            'admin',
            $plugin_url . 'admin.js',
            ['jquery'],
            rand(111, 9999)
        );
    }

    /**
     * Custom template for the setting page. Partially copied from WC_Points_Rewards_Admin::save_conversion_ratio_field
     * @return array The filtered setting page template
     */
    public function change_plugin_settings()
    {
        $settings = array(

            array(
                'title' => __('Points Settings', 'woocommerce-points-and-rewards'),
                'type' => 'title',
                'id' => 'wc_points_rewards_points_settings_start',
            ),

            // earn points conversion.
            array(
                'title' => __('Earn Points Conversion Rate 0-500', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the number of points awarded based on the product price.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_earn_points_ratio_500',
                'default' => '1:1',
                'type' => 'conversion_ratio',
            ),

            array(
                'title' => __('Earn Points Conversion Rate 500-1000', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the number of points awarded based on the product price.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_earn_points_ratio_1000',
                'default' => '5:1',
                'type' => 'conversion_ratio',
            ),

            array(
                'title' => __('Earn Points Conversion Rate up 1000', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the number of points awarded based on the product price.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_earn_points_ratio_up1000',
                'default' => '10:1',
                'type' => 'conversion_ratio',
            ),

            // earn points conversion.
            array(
                'title' => __('Earn Points Rounding Mode', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set how points should be rounded.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_earn_points_rounding',
                'default' => 'round',
                'options' => array(
                    'round' => 'Round to nearest integer',
                    'floor' => 'Always round down',
                    'ceil' => 'Always round up',
                ),
                'type' => 'select',
            ),

            // redeem points conversion.
            array(
                'title' => __('Redemption Conversion Rate', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the value of points redeemed for a discount.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_redeem_points_ratio',
                'default' => '100:1',
                'type' => 'conversion_ratio',
            ),

            // redeem points conversion.
            array(
                'title' => __('Partial Redemption', 'woocommerce-points-and-rewards'),
                'desc' => __('Enable partial redemption', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Lets users enter how many points they wish to redeem during cart/checkout.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_partial_redemption_enabled',
                'default' => 'no',
                'type' => 'checkbox',
            ),

            // Minimum points discount.
            array(
                'title' => __('Minimum Points Discount', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the minimum amount a user\'s points must add up to in order to redeem points. Use a fixed monetary amount or leave blank to disable.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_cart_min_discount',
                'default' => '',
                'type' => 'text',
            ),

            // maximum points discount available.
            array(
                'title' => __('Maximum Points Discount', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the maximum product discount allowed for the cart when redeeming points. Use either a fixed monetary amount or a percentage based on the product price. Leave blank to disable.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_cart_max_discount',
                'default' => '',
                'type' => 'text',
            ),

            // maximum points discount available.
            array(
                'title' => __('Maximum Product Points Discount', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the maximum product discount allowed when redeeming points per-product. Use either a fixed monetary amount or a percentage based on the product price. Leave blank to disable. This can be overridden at the category and product level.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_max_discount',
                'default' => '',
                'type' => 'text',
            ),

            // Tax settings.
            array(
                'title' => __('Tax Setting', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Whether or not points should apply to prices inclusive of tax.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_points_tax_application',
                'default' => wc_prices_include_tax() ? 'inclusive' : 'exclusive',
                'options' => array(
                    'inclusive' => 'Apply points to price inclusive of taxes.',
                    'exclusive' => 'Apply points to price exclusive of taxes.',
                ),
                'type' => 'select',
            ),

            // points label.
            array(
                'title' => __('Points Label', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('The label used to refer to points on the frontend, singular and plural.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_points_label',
                'default' => sprintf('%s:%s', __('Point', 'woocommerce-points-and-rewards'), __('Points', 'woocommerce-points-and-rewards')),
                'type' => 'singular_plural',
            ),

            // Expire Points.
            array(
                'title' => __('Points Expire After', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Set the period after which points expire once granted to a user', 'woocommerce-points-and-rewards'),
                'type' => 'points_expiry',
                'id' => 'wc_points_rewards_points_expiry',
            ),

            array('type' => 'sectionend', 'id' => 'wc_points_rewards_points_settings_end'),

            array(
                'title' => __('Product / Cart / Checkout Messages', 'woocommerce-points-and-rewards'),
                'desc' => sprintf(__('Adjust the message by using %1$s{points}%2$s and %1$s{points_label}%2$s to represent the points earned / available for redemption and the label set for points.', 'woocommerce-points-and-rewards'), '<code>', '</code>'),
                'type' => 'title',
                'id' => 'wc_points_rewards_messages_start',
            ),

            // single product page message.
            array(
                'title' => __('Single Product Page Message', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Add an optional message to the single product page below the price. Customize the message using {points} and {points_label}. Limited HTML is allowed. Leave blank to disable.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_single_product_message',
                'css' => 'min-width: 400px;',
                'default' => sprintf(__('Purchase this product now and earn %s!', 'woocommerce-points-and-rewards'), '<strong>{points}</strong> {points_label}'),
                'type' => 'textarea',
            ),

            // variable product page message.
            array(
                'title' => __('Variable Product Page Message', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Add an optional message to the variable product page below the price. Customize the message using {points} and {points_label}. Limited HTML is allowed. Leave blank to disable.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_variable_product_message',
                'css' => 'min-width: 400px;',
                'default' => sprintf(__('Earn up to %s.', 'woocommerce-points-and-rewards'), '<strong>{points}</strong> {points_label}'),
                'type' => 'textarea',
            ),

            // earn points cart/checkout page message.
            array(
                'title' => __('Earn Points Cart/Checkout Page Message', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Displayed on the cart and checkout page when points are earned. Customize the message using {points} and {points_label}. Limited HTML is allowed.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_earn_points_message',
                'css' => 'min-width: 400px;',
                'default' => sprintf(__('Complete your order and earn %s for a discount on a future purchase', 'woocommerce-points-and-rewards'), '<strong>{points}</strong> {points_label}'),
                'type' => 'textarea',
            ),

            // redeem points cart/checkout page message.
            array(
                'title' => __('Redeem Points Cart/Checkout Page Message', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Displayed on the cart and checkout page when points are available for redemption. Customize the message using {points}, {points_value}, and {points_label}. Limited HTML is allowed.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_redeem_points_message',
                'css' => 'min-width: 400px;',
                'default' => sprintf(__('Use %s for a %s discount on this order!', 'woocommerce-points-and-rewards'), '<strong>{points}</strong> {points_label}', '<strong>{points_value}</strong>'),
                'type' => 'textarea',
            ),

            // earned points thank you / order received page message.
            array(
                'title' => __('Thank You / Order Received Page Message', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('Displayed on the thank you / order received page when points were earned. Customize the message using {points}, {total_points}, {points_label}, and {total_points_label}. Limited HTML is allowed.', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_thank_you_message',
                'css' => 'min-width: 400px;min-height: 75px;',
                'default' => sprintf(__('You have earned %s for this order. You have a total of %s.', 'woocommerce-points-and-rewards'), '<strong>{points}</strong> {points_label}', '<strong>{total_points}</strong> {total_points_label}'),
                'type' => 'textarea',
            ),

            array('type' => 'sectionend', 'id' => 'wc_points_rewards_messages_end'),

            array(
                'title' => __('Points Earned for Actions', 'woocommerce-points-and-rewards'),
                'desc' => __('Customers can also earn points for actions like creating an account or writing a product review. You can enter the amount of points the customer will earn for each action in this section.', 'woocommerce-points-and-rewards'),
                'type' => 'title',
                'id' => 'wc_points_rewards_earn_points_for_actions_settings_start',
            ),

            array('type' => 'sectionend', 'id' => 'wc_points_rewards_earn_points_for_actions_settings_end'),

            array(
                'type' => 'title',
                'title' => __('Actions', 'woocommerce-points-and-rewards'),
                'id' => 'wc_points_rewards_points_actions_start',
            ),

            array(
                'title' => __('Apply Points to Previous Orders', 'woocommerce-points-and-rewards'),
                'desc_tip' => __('This will apply points to all previous orders (paid or completed) and cannot be reversed.', 'woocommerce-points-and-rewards'),
                'button_text' => __('Apply Points', 'woocommerce-points-and-rewards'),
                'type' => 'apply_points',
                'id' => 'wc_points_rewards_apply_points_to_previous_orders',
                'class' => 'wc-points-rewards-apply-button',
            ),

            array('type' => 'sectionend', 'id' => 'wc_points_rewards_points_actions_end'),

        );

        if ($integration_settings) {

            // set defaults.
            foreach (array_keys($integration_settings) as $key) {
                if (!isset($integration_settings[$key]['css'])) {
                    $integration_settings[$key]['css'] = 'max-width: 50px;';
                }

                if (!isset($integration_settings[$key]['type'])) {
                    $integration_settings[$key]['type'] = 'text';
                }

            }

            // find the start of the Points Earned for Actions settings to splice into.
            $index = -1;
            foreach ($settings as $index => $setting) {
                if (isset($setting['id']) && 'wc_points_rewards_earn_points_for_actions_settings_start' == $setting['id']) {
                    break;
                }

            }

            array_splice($settings, $index + 1, 0, $integration_settings);
        }

        return $settings;

    }
}
