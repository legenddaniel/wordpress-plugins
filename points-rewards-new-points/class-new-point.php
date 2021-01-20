<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class New_Point
{

    public function __construct()
    {

        // remove_action('woocommerce_order_status_cancelled', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');
        // remove_action('woocommerce_order_status_refunded', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');
        // remove_action('woocommerce_order_status_failed', 'WC_Points_Rewards_Order::handle_cancelled_refunded_order');

    }

    /**
     * Filter-less version of WC_Product:get_regular_product. Product may be single product id or variation id.
     * @param WC_Product|int $product - Product_id or Variation id
     * @return int|double
     */
    protected function get_product_price($product)
    {
        $type = gettype($product);
        if ($type === 'integer' || $type === 'string') {
            $product_id = $product;
        } else {
            $product_id = $product->get_id();
        }

        $price = get_post_meta($product_id, '_regular_price', true);
        // return $price === '' ? $price : +$price;
        return +$price;
    }

    /**
     * Fetch total amount from usermeta
     * @param int $user_id
     * @return int|double
     */
    protected function get_total_amount($user_id)
    {
        return +get_user_meta($user_id, 'total_amount', true) ?: 0;
    }

    /**
     * Get ratio type based on user total spend
     * @param int|double $total_amount
     * @return string
     */
    protected function get_ratio_type($total_amount)
    {
        $type = gettype($total_amount);
        if ($type !== 'integer' && $type !== 'double') {
            throw 'Param must be a number!';
        }

        $ratio_type = 'wc_points_rewards_earn_points_ratio';

        if ($total_amount < 500) {
            $ratio_type .= '_500';
        } else if ($total_amount >= 500 && $total_amount < 1000) {
            $ratio_type .= '_1000';
        } else {
            $ratio_type .= '_up1000';
        }

        return $ratio_type;
    }

    /**
     * Get ratio based on user total spend
     * @param int|double $total_amount
     * @return string e.g. 1:1
     */
    protected function get_ratio($total_amount)
    {
        return get_option($this->get_ratio_type($total_amount), '');
    }

    /**
     * Process the ratio string e.g.'1:1' to a multiple
     * @param string $ratio
     * @return int|double
     */
    protected function process_ratio($ratio)
    {
        // From WC_Points_Rewards_Manager::calculate_points
        // Ratio string "a:a" to array "[a,a]".
        $ratio = explode(':', $ratio);
        if (empty($ratio)) {
            return 0;
        }

        $points = !empty($ratio[0]) ? $ratio[0] : 0;
        $monetary_value = !empty($ratio[1]) ? $ratio[1] : 0;

        if (!$points || !$monetary_value) {
            return 0;
        }

        return $points / $monetary_value;
    }

    /**
     * Check if the product is a point product
     * @param int|string|WC_Product $product
     * @return bool
     */
    protected function is_point_product($product)
    {
        if (gettype($product) === 'integer' || gettype($product) === 'string') {
            $product_id = $product;
        } else if ($product instanceof WC_Product) {
            $product_id = $product->get_id();
        } else {
            die('Not a product!');
        }

        $terms = get_the_terms($product_id, 'product_cat');
        if (is_array($terms)) {
            foreach ($terms as $term) {
                if ($term->name === 'points') {
                    return true;
                }
            }
        }

        return false;
    }

    protected function remove_filters_with_method_name($hook_name = '', $method_name = '', $priority = 0)
    {
        global $wp_filter;
        // Take only filters on right hook name and priority
        if (!isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority])) {
            return false;
        }
        // Loop on filters registered
        foreach ((array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            // Test if filter is an array ! (always for class/method)
            if (isset($filter_array['function']) && is_array($filter_array['function'])) {
                // Test if object is a class and method is equal to param !
                if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                    if (is_a($wp_filter[$hook_name], 'WP_Hook')) {
                        unset($wp_filter[$hook_name]->callbacks[$priority][$unique_id]);
                    } else {
                        unset($wp_filter[$hook_name][$priority][$unique_id]);
                    }
                }
            }
        }
        return false;
    }
}
