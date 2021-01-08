<?php

class New_Points
{

    public function __construct()
    {
        // Add custom field identification
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_500', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_1000', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('woocommerce_admin_settings_sanitize_option_wc_points_rewards_earn_points_ratio_up1000', 'WC_Points_Rewards_Admin::save_conversion_ratio_field', 10, 3);
        add_filter('wc_points_rewards_settings', array($this, 'new_points_rewards_settings'));

        add_action('woocommerce_after_cart_table', function () {
            //     echo apply_filters( 'the_content',"[wcps id='90']");
            // echo apply_filters( 'the_content',"[TABS_R id=91]");
            echo apply_filters('the_content', "[WPSM_AC id=105]");
            // echo apply_filters( 'the_content',"[carousel_slide id='102']");
            // echo apply_filters( 'the_content','[sp_wpcarousel id="94"]');

        });

        // Apply custom point:cost ratio
        add_filter('woocommerce_points_earned_for_cart_item', array($this, 'recalculate_ratio'));

        // Set the total amount and point balance after the order completed
        add_action('woocommerce_payment_complete', array($this, 'set_total_when_order_complete'));
        add_action('woocommerce_order_status_completed', array($this, 'set_total_when_order_complete'));
        add_action('woocommerce_payment_complete', array($this, 'set_point_balance_when_order_complete'));
        add_action('woocommerce_order_status_completed', array($this, 'set_point_balance_when_order_complete'));

        // Restore the total_amount after order cancelled/refunded/failed
        add_action('woocommerce_order_status_cancelled', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_status_refunded', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_status_failed', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_partially_refunded', array($this, 'reset_total_when_partially_refunded'), 10, 2);

        // remove_action('woocommerce_order_status_cancelled', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');
        // remove_action('woocommerce_order_status_refunded', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');
        // remove_action('woocommerce_order_status_failed', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');

        add_filter('woocommerce_add_cart_item_data', array($this, 'add_item_point_used'), 10, 3);

        // Change the price display of point products
        add_filter('woocommerce_get_price_html', array($this, 'change_gift_price_html_product'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'change_gift_price_html_cart'), 10, 3);

        // Change the price of point product
        add_action('woocommerce_before_calculate_totals', array($this, 'change_gift_price'));

        // Display points in cart/checkout total lines
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'display_point_total'));
        add_action('woocommerce_review_order_before_order_total', array($this, 'display_point_total'));
    }

    /**
     * Custom template for the setting page. Partially copied from WC_Points_Rewards_Admin::save_conversion_ratio_field
     * @return array The filtered setting page template
     */
    public function new_points_rewards_settings()
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

    /**
     * Fetch total amount from usermeta
     * @param int $user_id
     * @return int|double
     */
    private function get_total_amount($user_id)
    {
        return +get_user_meta($user_id, 'total_amount', true) ?: 0;
    }

    /**
     * Recalculate points earned on a single product basis with various ratio
     * @param string|double $amount - The original points
     * @return int
     */
    public function recalculate_ratio($amount)
    {
        $user = get_current_user_id();
        $total_amount = $this->get_total_amount($user);
        $ratio_type = 'wc_points_rewards_earn_points_ratio';

        if ($total_amount < 500) {
            $ratio_type .= '_500';
        } else if ($total_amount >= 500 && $total_amount < 1000) {
            $ratio_type .= '_1000';
        } else {
            $ratio_type .= '_up1000';
        }

        // From WC_Points_Rewards_Manager::calculate_points
        // Ratio string "a:a" to array "[a,a]".
        $ratio = explode(':', get_option($ratio_type, ''));
        if (empty($ratio)) {
            return 0;
        }

        $points = !empty($ratio[0]) ? $ratio[0] : 0;
        $monetary_value = !empty($ratio[1]) ? $ratio[1] : 0;

        if (!$points || !$monetary_value || !$amount) {
            return 0;
        }

        return $amount * ($points / $monetary_value);
    }

    /**
     * Set the total_amount after order completed
     * @param int $order_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function set_total_when_order_complete($order_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        $total = $order->get_subtotal();

        $total_amount = get_user_meta($user, 'total_amount', true) ?: 0;
        return update_user_meta($user, 'total_amount', $total_amount + $total);
    }

    /**
     * Restore the total_amount after order cancelled/refunded/failed
     * @param int $order_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function reset_total_when_cancel_refund($order_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        $total = $order->get_subtotal();

        // Should not minus the whole subtotal if refunded before when cancelled
        if ($order->get_status() === 'cancelled') {
            global $wpdb;
            $refund = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(total_sales)
                    FROM {$wpdb->prefix}wc_order_stats
                    WHERE parent_id = %d"
                    , $order_id)
            );
            $total += $refund;
        }

        $total_amount = get_user_meta($user, 'total_amount', true);
        $new_total = $total_amount >= $total ? $total_amount - $total : 0;
        return update_user_meta($user, 'total_amount', $new_total);
    }

    /**
     * Restore the total_amount after order partially refunded
     * @param int $order_id
     * @param int $refund_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function reset_total_when_partially_refunded($order_id, $refund_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        $total = (new WC_Order_Refund($refund_id))->get_amount();

        $total_amount = get_user_meta($user, 'total_amount', true);
        $new_total = $total_amount >= $total ? $total_amount - $total : 0;
        return update_user_meta($user, 'total_amount', $new_total);
    }

    /**
     * Check if the product is a point product
     * @param int|string|WC_Product $product
     * @return bool
     */
    private function is_point_product($product)
    {
        if (gettype($product) === 'integer' || gettype($product) === 'string') {
            $product_id = $product;
        }
        if ($product instanceof WC_Product) {
            $product_id = $product->get_id();
        } else {
            die('Not a product!');
        }

        $terms = get_the_terms($product_id, 'product_cat');
        foreach ($terms as $term) {
            if ($term->name === 'points') {
                return true;
            }
        }
        return false;
    }

    /**
     * Set price to 0 for point products
     * @param WC_Cart $cart
     * @return void
     */
    public function change_gift_price($cart)
    {
        foreach ($cart->get_cart() as $cart_item) {
            $data = $cart_item['data'];
            if ($this->is_point_product($data)) {
                $cart_item['points_used'] = round($data->get_regular_price());
                $data->set_price(0);
            }
        }
    }

    public function add_item_point_used($cart_item_data, $product_id, $variation)
    {
        // if (!$this->is_point_product($cart_item_data)) {
        //     return $cart_item_data;
        // }

        // $cart_item_data['points_used'] = $cart_item_data->get_regular_price();
        return $cart_item_data;
    }

    /**
     * Change price to points for gifts in a product view
     * @param string $price_html - Price html
     * @param WC_Product $product
     * @return string
     */
    public function change_gift_price_html_product($price_html, $product)
    {
        if (!$this->is_point_product($product)) {
            return $price_html;
        }

        $price = $product->get_regular_price();
        $new_html = intval($price) . ' points';

        return $new_html;
    }

    /**
     * Change price to points for gifts in a cart item view
     * @param string $price_html - Price html
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function change_gift_price_html_cart($price_html, $cart_item, $cart_item_key)
    {
        $product = $cart_item['data'];
        if (!$this->is_point_product($product)) {
            return $price_html;
        }

        $price = $product->get_regular_price();
        $qty = $cart_item['quantity'];
        $new_html = round($price * $qty) . ' points';

        return $new_html;
    }

    /**
     * Display points in cart/checkout total lines
     * @return void
     */
    public function display_point_total()
    {
        $total_points = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $data = $cart_item['data'];
            if ($this->is_point_product($data)) {
                $total_points += round($data->get_regular_price());
            }
        }
        if ($total_points) {
            echo
            '<tr>
                <th>' . __("Points Used", "woocommerce") . '</th>
                <td data-title="total-volume">' . $total_points . '</td>
            </tr>';
        }
    }

    /**
     * Set the point balance after order completed (point products)
     * @param int $order_id
     * @return bool
     */
    public function set_point_balance_when_order_complete($order_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        $total_points = 0;
        foreach ($order->get_items() as $item) {
            $total_points -= $item->get_meta('points_used') ?: 0;
        }

        $total_points = -20;

        // if ($total_points) {
            global $wpdb;
            return $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}wc_points_rewards_user_points 
                    (user_id, points, points_balance, order_id, date)
                    VALUES (%d, %d, %d, %d, %s)"
                , [$user, $total_points, $total_points, $order_id, date("Y-m-d H:i:s", gmmktime())])
                );
        // }

        // return false;
    }
}
