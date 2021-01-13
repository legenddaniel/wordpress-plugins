<?php

class New_Point_Order extends New_Point
{

    public function __construct()
    {
        // Add order meta of point ratio for partial refund
        add_action('woocommerce_checkout_update_order_meta', array($this, 'add_ratio_into_order_meta'), 10, 2);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'add_points_used_into_order_meta'), 10, 2);

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
        $points_used = 0;
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($this->is_point_product($product_id)) {
                $product = wc_get_product($product_id);
                $points_used += $product->get_regular_price();
            }
        }

        return $points_used ? update_post_meta($order_id, 'points_used', $points_used) : false;
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
     * Set the total_amount after payment completed
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
        $user = $order->get_user_id();
        $total = $order->get_subtotal();

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
        $total = $order->get_subtotal();

        // Deduct the past refunds if applicable
        // Beware that order/refund processing might take some time so would be better if a little gap between each cancel/refund
        global $wpdb;
        $refund = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_sales)
                    FROM {$wpdb->prefix}wc_order_stats
                    WHERE parent_id = %d AND status = 'wc-completed'"
                , $order_id
            )
        );
        if ($total <= $refund) {
            return;
        }

        $total += $refund;
        $total_amount = $this->get_total_amount($user);
        $new_total = $total_amount >= $total ? $total_amount - $total : 0;

        return $this->set_total_amount_in_db($user, $new_total);
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
        $total = (new WC_Order_Refund($refund_id))->get_amount();

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
        // Separate redeeming and cancelled/refund logic
        // Beware that this event type is different from WooCommerce order status
        if ($event_type !== 'order-refunded' && $event_type !== 'order-cancelled') {
            return $points;
        }

        $order = wc_get_order($order_id);
        
        if ($event_type === 'order-refunded') {
            $ratio = $this->process_ratio($order->get_meta('point_ratio', true));
            $points *= $ratio;
        }

        // Deduct the past refunds if applicable
        if ($event_type === 'order-cancelled') {
            global $wpdb;
            $refund = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(points)
                        FROM {$wpdb->prefix}wc_points_rewards_user_points_log
                        WHERE order_id = %d AND type = 'order-refunded'"
                    , $order_id
                )
            );
            $points += $refund;
        }

        return $points;
    }

}
