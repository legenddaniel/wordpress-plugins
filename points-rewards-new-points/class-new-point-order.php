<?php

if (!defined('ABSPATH')) {
    exit;
}

class New_Point_Order extends New_Point
{

    public function __construct()
    {

        // Add order meta and order item meta of point products for future use
        add_action('woocommerce_checkout_update_order_meta', array($this, 'add_ratio_into_order_meta'), 10, 2);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'add_points_used_into_order_meta'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_points_order_item_meta'), 10, 4);

        // Display points used in order details
        add_filter('woocommerce_get_order_item_totals', array($this, 'display_points_used_in_order_details'), 10, 3);

        // Change point product subtotal html in order details (thankyou, email, my account)
        add_filter('woocommerce_order_formatted_line_subtotal', array($this, 'change_gift_subtotal_html_frontend'), 10, 3);
        add_filter('woocommerce_display_item_meta', array($this, 'hide_point_product_meta'), 10, 3);

        // Change point product subtotal html in order details (admin)
        // add_filter('woocommerce_order_amount_item_subtotal', array($this, 'change_gift_subtotal_html_admin'), 10, 5);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_point_product_meta_admin'));
        // add_action('woocommerce_admin_order_item_values', array($this, 'display_point_product_notice'), 10, 3);

        // Set the total amount and point balance after payment completed
        add_action('woocommerce_payment_complete', array($this, 'set_total_after_payment'));
        add_action('woocommerce_order_status_completed', array($this, 'set_total_after_payment'));
        add_action('woocommerce_payment_complete', array($this, 'set_point_balance_after_point_order_payment'));
        add_action('woocommerce_order_status_completed', array($this, 'set_point_balance_after_point_order_payment'));

        // Restore the total_amount after order cancelled/refunded/failed
        add_action('woocommerce_order_status_cancelled', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_status_refunded', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_status_failed', array($this, 'reset_total_when_cancel_refund'));
        add_action('woocommerce_order_partially_refunded', array($this, 'reset_total_when_partially_refunded'), 10, 2);

        // Change points decreased based on user total spend
        add_filter('wc_points_rewards_decrease_points', array($this, 'reset_points_when_cancel_refunded'), 10, 5);
    }

    /**
     * Save current ratio in db for regular products. Useful when partially refund this item.
     * @param int $order_id
     * @param array $data
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function add_ratio_into_order_meta($order_id, $data)
    {
        $order = wc_get_order($order_id);
        if (!$order->get_user_id()) {
            return;
        }

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (!($this->is_point_product($product_id))) {
                $user = $order->get_user_id();
                $total_amount = $this->get_total_amount($user);
                $ratio = $this->get_ratio($total_amount);

                return update_post_meta($order_id, 'point_ratio', $ratio);
            }
        }
    }

    /**
     * Save points used in db (point products).
     * @param int $order_id
     * @param array $data
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database. Or no need to update.
     */
    public function add_points_used_into_order_meta($order_id, $data)
    {
        $order = wc_get_order($order_id);
        if (!$order->get_user_id()) {
            return;
        }

        $points_used = 0;
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($this->is_point_product($product_id)) {
                $variation_id = $item->get_variation_id();
                $qty = $item->get_quantity();
                $points_used += $this->recalculate_point_product_points($product_id, $variation_id, $qty);
            }
        }

        return $points_used ? update_post_meta($order_id, 'points_used', $points_used) : false;
    }

    /**
     * Add point price in the order meta data (point products).
     * @param WC_Order_Item_Product $item
     * @param string $cart_item_key
     * @param array $values
     * @param WC_Order $order
     * @return null
     */
    public function add_points_order_item_meta($item, $cart_item_key, $values, $order)
    {
        $product_id = $item->get_product_id();
        if (!$this->is_point_product($product_id)) {
            return;
        }
        if (!$order->get_user_id()) {
            return;
        }

        $variation_id = $item->get_variation_id();
        $qty = $item->get_quantity();

        $points = $this->recalculate_point_product_points($product_id, $variation_id, $qty);

        $item->update_meta_data('points_subtotal', $points);
    }

    /**
     * Display points used in order details if applicable
     * @param array $total_rows - Original data
     * @param WC_Order $order
     * @param string $tax_display
     * @return array
     */
    public function display_points_used_in_order_details($total_rows, $order, $tax_display)
    {
        if (!$order->get_user_id()) {
            return;
        }

        $order_id = $order->get_id();
        $points_used = get_post_meta($order_id, 'points_used', true);

        if (!$points_used) {
            return $total_rows;
        }

        // Display this row at 2nd last line
        $new_rows = $this->array_insert(
            $total_rows,
            ['points_used' => [
                'label' => __($this->text_points_used . ':', 'woocommerce'),
                'value' => sprintf($this->html_single_point_product_price, $points_used),
            ]],
            count($total_rows) - 1
        );

        return $new_rows;
    }

    /**
     * Change subtotal to points for point products in order details
     * @param string $subtotal_html - Subtotal html
     * @param WC_Order_Item_Product $item
     * @param WC_Order $order
     * @return string
     */
    public function change_gift_subtotal_html_frontend($subtotal_html, $item, $order)
    {
        if (!$order->get_user_id()) {
            return;
        }

        $points = $item->get_meta('points_subtotal');
        if (!$points) {
            return $subtotal_html;
        }

        return sprintf($this->html_single_point_product_price, $points);
    }

    /**
     * Change subtotal to points for point products in admin order page
     * @param float $subtotal
     * @param WC_Order $order
     * @param WC_Order_Item_Product $item
     * @param bool $inc_tax
     * @param bool $round
     * @return float
     */
    public function change_gift_subtotal_html_admin($subtotal, $order, $item, $inc_tax, $round)
    {
        // if (!$order->get_user_id()) {
        //     return;
        // }

        // return $this->change_gift_subtotal_html($subtotal, $item);
    }

    public function display_point_product_notice($product, $item, $item_id)
    {
        $product_id = $item->get_product_id();
        if ($this->is_point_product($product_id)) {
            echo 'This is a POINT Product.';
        }
    }

    /**
     * Hide ALL item meta data of point products. Change this code if there's some data to be shown.
     * @param string $html
     * @param WC_Order_Item_Product $item
     * @param array $args
     * @return string|null
     */
    public function hide_point_product_meta($html, $item, $args)
    {
        // So far hide ALL meta data
        $product_id = $item->get_product_id();
        if ($this->is_point_product($product_id)) {
            return;
        }
        return $html;
    }

    /**
     * Add points_subtotal meta data to hidden options
     * @param array $meta
     * @return array
     */
    public function hide_point_product_meta_admin(&$meta)
    {
        $meta[] = 'points_subtotal';
        return $meta;
    }

    /**
     * Set total amount in db
     * @param int $user_id
     * @param int|double $total_amount
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    private function set_total_amount_in_db($user, $total_amount)
    {
        return update_user_meta($user, 'total_amount', $total_amount);
    }

    /**
     * Check if total/points has been set during payment
     * @param int $order_id
     * @return bool
     */
    private function has_set_total_or_points($order_id)
    {
        if (current_filter() === 'woocommerce_order_status_completed') {
            global $wpdb;
            $dates = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT meta_value
                    FROM {$wpdb->prefix}postmeta
                    WHERE post_id = %d AND (meta_key = '_date_paid' OR meta_key = '_date_completed')"
                    , $order_id),
                ARRAY_N
            );
            return $dates[0][0] != $dates[1][0];
        }

        return false;
    }

    /**
     * Exchange total to USD for CAD orders
     * @param int $order_id
     * @param int|double $total - Original total spend
     * @param bool $round - Round or not
     * @return int|double New total
     */
    private function set_usd_based_total($order_id, $total, $round = true)
    {
        $currency = get_post_meta($order_id, '_order_currency', true);
        if ($currency && $currency === 'CAD') {
            $rate = get_post_meta($order_id, 'wmc_order_info', true);
            $total = $total * $rate['USD']['rate'] / $rate['CAD']['rate'];
            if ($round) {
                $total = number_format($total, 2);
            }
        }
        return $total;
    }

    /**
     * Set the total_amount after payment completed, based on USD amount
     * @param int $order_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function set_total_after_payment($order_id)
    {
        // Prevent from a duplicate setting since this function is used for 2 hooks
        if ($this->has_set_total_or_points($order_id)) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order->get_user_id()) {
            return;
        }

        $user = $order->get_user_id();
        $total = $order->get_subtotal();

        // Set the amount to USD based if applicable
        $total = $this->set_usd_based_total($order_id, $total);

        $total_amount = $this->get_total_amount($user);
        return $this->set_total_amount_in_db($user, $total_amount + $total);
    }

    /**
     * Set the point balance after payment (with point products)
     * @param int $order_id
     * @return void
     */
    public function set_point_balance_after_point_order_payment($order_id)
    {
        // Prevent from a duplicate setting since this function is used for 2 hooks
        if ($this->has_set_total_or_points($order_id)) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order->get_user_id()) {
            return;
        }

        // Redeeming logic
        $points_used = get_post_meta($order_id, 'points_used', true);
        if (!$points_used) {
            return;
        }

        $user_id = wc_get_order($order_id)->get_user_id();
        WC_Points_Rewards_Manager::decrease_points($user_id, $points_used, 'order-redeem', null, $order_id);
    }

    /**
     * Restore the total_amount after order cancelled/(fully) refunded/failed. Beware that this status is different from event type for Points and Rewards Plugin.
     * @param int $order_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function reset_total_when_cancel_refund($order_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        if (!$user) {
            return;
        }

        $total = $order->get_subtotal();

        // Deduct the past refunds if applicable
        // Beware that order/refund processing might take some time so would be better if a little gap between each cancel/refund
        global $wpdb; // Wait for 1 min!!!
        $refund = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(net_total)
                    FROM {$wpdb->prefix}wc_order_stats
                    WHERE parent_id = %d AND status = 'wc-completed'"
                , $order_id
            )
        ); // Wait for 1 min!!!
        if ($total + $refund <= 0) {
            return;
        }

        $total += $refund; // Wait for 1 min!!!

        // Set the amount to USD based if applicable
        $total = $this->set_usd_based_total($order_id, $total);

        $total_amount = $this->get_total_amount($user);
        $new_total = $total_amount >= $total ? $total_amount - $total : 0;

        return $this->set_total_amount_in_db($user, $new_total);
    }

    /**
     * Get refund subtotal without any fees from $_POST['line_item_totals']. WC_Order_Refund::get_amount not working properly.
     * @return int|float
     */
    private function get_refund_subtotal()
    {
        $refunds = $_POST['line_item_totals'];
        if (!$refunds) {
            return 0;
        }

        $total = 0;
        $totals = json_decode(stripslashes($refunds), true);
        if (is_array($totals)) {
            foreach ($totals as $line_total) {
                if ($line_total) {
                    $total += $line_total;
                }
            }
        }

        return $total;
    }

    /**
     * Restore the total_amount after order partially refunded.
     * @param int $order_id
     * @param int $refund_id
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
     */
    public function reset_total_when_partially_refunded($order_id, $refund_id)
    {
        $order = wc_get_order($order_id);
        $user = $order->get_user_id();
        if (!$user) {
            return;
        }

        $total = $this->get_refund_subtotal();

        // Set the amount to USD based if applicable
        $total = $this->set_usd_based_total($order_id, $total);

        $total_amount = $this->get_total_amount($user);
        $new_total = $total_amount >= $total ? $total_amount - $total : 0;
        return $this->set_total_amount_in_db($user, $new_total);
    }

    /**
     * Set points based on user total spend at the time they ordered. Beware that this hook triggers when the points decrease. So a refund amount (event type) has to be verified.
     * @param int $points
     * @param int $user_id
     * @param string $event_type
     * @param mixed $data
     * @param int $order_id
     * @return int
     */
    public function reset_points_when_cancel_refunded($points, $user_id, $event_type, $data, $order_id)
    {
        if (!$user_id) {
            return $points;
        }

        // Separate redeeming and cancelled/refund logic
        // Beware that this event type is different from WooCommerce order status
        if ($event_type !== 'order-refunded' && $event_type !== 'order-cancelled') {
            return $points;
        }

        $order = wc_get_order($order_id);

        // Points that have been refunded (negative integer)
        global $wpdb;
        $refund = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(points)
                    FROM {$wpdb->prefix}wc_points_rewards_user_points_log
                    WHERE order_id = %d AND type = 'order-refunded'"
                , $order_id
            )
        );

        // order-refunded means partially refunding
        if ($event_type === 'order-refunded') {
            $points_earned = get_post_meta($order_id, '_wc_points_earned', true);

            $total = $this->get_refund_subtotal();
            $ratio = $this->process_ratio($order->get_meta('point_ratio', true));

            // Sometimes the event type will still be 'order-refunded' even though this is the last item in the orde to refund/cancel since there's still other fees like shipping fee. In this case we need to use the min value between the current point value and the currently remaining point value.
            $rounding_option = $this->rounding_option;
            $points = $rounding_option($this->set_usd_based_total($order_id, $total, false) * $ratio);

            $points = min($points, $points_earned + $refund);
        }

        // order-cancelled means all items will be refunded/cancelled after current action including non-product items e.g. shipping fees.
        // Deduct the past refunds if applicable
        if ($event_type === 'order-cancelled') {

            // In case 'order-cancelled' $points is total points earned.
            $points += $refund;
        }

        return $points;
    }

}
