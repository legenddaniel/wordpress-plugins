<?php

if (!defined('ABSPATH')) {
    exit;
}

class New_Point_Shop extends New_Point
{
    private $total_amount = 0;
    private $ratio_type = '';
    // private $cart_total_points = 0;

    public function __construct()
    {
        $this->total_amount = $this->get_total_amount(get_current_user_id());
        $this->ratio_type = $this->get_ratio_type($this->total_amount);

        add_action('wp_enqueue_scripts', array($this, 'init_assets'));

        // Render rewards html in cart page
        add_action('woocommerce_after_cart_table', array($this, 'apply_template'));

        // Validate point product purchase
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_point_product_purchase_add'), 10, 3);
        // add_filter('woocommerce_update_cart_validation', array($this, 'validate_point_product_purchase_update'), 10, 4);

        // Apply custom point:cost ratio
        add_filter('woocommerce_points_earned_for_cart_item', array($this, 'recalculate_points_cart'), 10, 3);
        add_filter('woocommerce_points_earned_for_order_item', array($this, 'recalculate_points_order'), 10, 5);

        // Display points in cart/checkout total lines
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'display_points_used_total'));
        add_action('woocommerce_review_order_before_order_total', array($this, 'display_points_used_total'));

        // Add cart item data for points earned
        // add_filter('woocommerce_add_cart_item_data', array($this, 'add_item_point_earned_data'), 10, 3);

        // Change the price display of point products
        add_filter('woocommerce_get_price_html', array($this, 'change_gift_price_html_product'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'change_gift_price_html_cart'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'change_gift_subtotal_html'), 10, 3);

        // Change the message display in single product page
        add_filter('wc_points_rewards_single_product_message', array($this, 'change_product_point_msg'));

        // Change the price of point product
        add_action('woocommerce_before_calculate_totals', array($this, 'change_gift_price'));
    }

    /**
     * Load CSS and JavaScript
     * @return void
     */
    public function init_assets()
    {
        $plugin_url = plugin_dir_url(__FILE__);

        wp_enqueue_style(
            'cr-css',
            $plugin_url . 'template-cart-rewards.css',
            [],
            rand(111, 9999)
        );

        wp_enqueue_script(
            'cr-js',
            $plugin_url . 'template-cart-rewards.js',
            ['jquery'],
            rand(111, 9999)
        );
    }

    /**
     * Apply rewards area html template in cart page
     * @return void
     */
    public function apply_template()
    {
        wc_get_template(
            '/template-cart-rewards.php',
            [
                'points' => WC_Points_Rewards_Manager::get_users_points(get_current_user_id()),
                // 'sliders' => [
                //     $this->render_slider(95), $this->render_slider(95), $this->render_slider(95)
                // ]
                'sliders' => [
                    $this->render_slider(17), $this->render_slider(18), $this->render_slider(19)
                ],
            ],
            '',
            dirname(__FILE__)
        );
    }

    /**
     * Render the slider from formatted shortcodes
     * @param int $cat_id
     * @return mixed
     */
    private function render_slider($cat_id)
    {
        // $slider_shortcode = '[slide-anything id="%d"]';
        // $slider_shortcode = '[wpb-product-slider product_type="category" category="%d"]';
        $slider_shortcode = '[products_slider cats="%d" autoplay="false" dots="false" order="ASC" orderby="meta_value_num" meta_key="_regular_price"]';
        return apply_filters('the_content', sprintf(__($slider_shortcode, 'woocommerce'), $cat_id));
    }

    /**
     * Check if have enough points for redeeming when add to cart
     * @param bool $passed
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function validate_point_product_purchase_add($passed, $product_id, $quantity)
    {
        $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
        $points_used = 0;
        foreach (WC()->cart->get_cart() as $item) {
            $product = $item['data'];
            if ($this->is_point_product($product)) {
                $points_used += $this->get_product_price($product) * $item['quantity'];
            }
        }
        if ($points_used > $total_points) {
            wc_add_notice(__('You don\'t have enough points!', 'woocommerce'), 'error');
            return false;
        }

        return $passed;
    }

    /**
     * Check if have enough points for redeeming when update cart
     * @param bool $passed
     * @param string $cart_item_key
     * @param array $values - Cart item
     * @param int $quantity - Whole cart item qty
     * @return bool
     */
    public function validate_point_product_purchase_update($passed, $cart_item_key, $values, $quantity)
    {
        // if (did_action('validate_point_product_purchase_update') > 1) {
        //     return;
        // }

        // $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
        // $points_used = 0;
        // foreach (WC()->cart->get_cart() as $item) {
        //     $product = $item['data'];
        //     if ($this->is_point_product($product)) {
        //         $points_used += $this->get_product_price($product) * $_POST["cart[$cart_item_key][qty]"];
        //     }
        // }
        // if ($points_used > $total_points) {
        //     wc_add_notice(__('You don\'t have enough points!', 'woocommerce'), 'error');
        //     return false;
        // }
        // return $passed;

        // Issue: cannot get $_POST["cart[$cart_item_key][qty]"]

        // $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
        // $product = $values['data'];
        // if ($this->is_point_product($product)) {
        //     $this->cart_total_points += $this->get_product_price($product) * $_POST["cart[$cart_item_key][qty]"];
        // }
        // if ($_REQUEST["cart[16a5cdae362b8d27a1d8f8c7b78b4330][qty]"] === null) {
        //     wc_add_notice(__('You don\'t have enough points!', 'woocommerce'), 'error');
        //     return false;
        // }
        return $passed;
    }

    /**
     * Recalculate points earned on a non-point product basis with various ratio
     * @param int|WC_Product $product
     * @param int $amount - The default points
     * @return int
     */
    private function recalculate_points($product, $amount)
    {
        // Return default points for point product (i.e. 0)
        if ($this->is_point_product($product)) {
            return $amount;
        }

        $price = $this->get_product_price($product);
        $total_amount = $this->total_amount;
        $ratio = $this->process_ratio($this->get_ratio($total_amount));

        return round($price * $ratio);
    }

    /**
     * Recalculate points earned on a single product basis with various ratio in cart
     * @param string|double $amount - The original points
     * @param string $item_key
     * @param array $item - Cart item
     * @return int
     */
    public function recalculate_points_cart($amount, $item_key, $item)
    {
        $product = $item['data'];
        return $this->recalculate_points($product, $amount);
    }

    /**
     * Recalculate points earned on a single product basis with various ratio in order
     * @param string|double $amount - The original points
     * @param WC_Product $product
     * @param string $item_key
     * @param WC_Order_Item_Product $item - Order item
     * @param WC_Order $order
     * @return int
     */
    public function recalculate_points_order($amount, $product, $item_key, $item, $order)
    {
        $product_id = $item->get_product_id();
        return $this->recalculate_points($product_id, $amount);
    }

    /**
     * Calculate points subtotal of a point product
     * @param WC_Product $product
     * @param array $cart_item
     * @return int
     */
    private function recalculate_product_points($product, $cart_item = null)
    {
        // Cannot use $product->get_regular_price with multi-currency plugin
        $price = $this->get_product_price($product);
        $qty = $cart_item['quantity'] ?: 1;
        return round($price * $qty);
    }

    /**
     * Display points in cart/checkout total lines
     * @return void
     */
    public function display_points_used_total()
    {
        $total_points = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $data = $cart_item['data'];
            if ($this->is_point_product($data)) {
                $total_points += $this->recalculate_product_points($data, $cart_item);
            }
        }
        if ($total_points) {
            echo
            '<tr>
                <th>' . __("Points Used", "woocommerce") . '</th>
                <td data-title="total-volume">' . $total_points . ' points</td>
            </tr>';
        }
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
                $data->set_price(0);
            }
        }
    }

    /**
     * Add current ratio for each item. Useful when partially refund this item.
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation
     * @return array
     */
    public function add_item_point_earned_data($cart_item_data, $product_id, $variation)
    {
        if (!$this->is_point_product($product_id)) {
            return $cart_item_data;
        }

        $cart_item_data['point_ratio'] = get_option($this->ratio_type);

        return $cart_item_data;
    }

    /**
     * Change price to points for gifts
     * @param string $price_html - Price html
     * @param WC_Product $product
     * @param array $cart_item
     * @return string
     */
    private function change_gift_price_html($price_html, $product, $cart_item = null)
    {
        if (!$this->is_point_product($product)) {
            return $price_html;
        }

        // Multiple by quantity for subtotal
        $points = is_null($cart_item) ? $this->get_product_price($product) : $this->recalculate_product_points($product, $cart_item);

        $new_html = $points . ' points';
        return $new_html;
    }

    /**
     * Change price to points for gifts in a product view
     * @param string $price_html - Price html
     * @param WC_Product $product
     * @return string
     */
    public function change_gift_price_html_product($price_html, $product)
    {
        return $this->change_gift_price_html($price_html, $product);
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
        return $this->change_gift_price_html($price_html, $product);
    }

    /**
     * Change subtotal to points for gifts in a cart item view
     * @param string $subtotal_html
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function change_gift_subtotal_html($subtotal_html, $cart_item, $cart_item_key)
    {
        $product = $cart_item['data'];
        return $this->change_gift_price_html($subtotal_html, $product, $cart_item);
    }

    /**
     * Change points earned message in single prduct page. Remove message for point products.
     * @param string $msg
     * @return string
     */
    public function change_product_point_msg($msg)
    {
        global $product;
        if ($this->is_point_product($product)) {
            return null;
        }

        $points = $this->recalculate_points($product, null);
        return preg_replace('/\d+/', $points, $msg);
    }

}
