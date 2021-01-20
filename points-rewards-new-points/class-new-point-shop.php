<?php

if (!defined('ABSPATH')) {
    exit;
}

class New_Point_Shop extends New_Point
{
    private $total_amount = 0;
    private $ratio_type = '';

    public function __construct()
    {
        $this->total_amount = $this->get_total_amount(get_current_user_id());
        $this->ratio_type = $this->get_ratio_type($this->total_amount);

        add_action('wp_enqueue_scripts', array($this, 'init_assets'));

        // Render rewards html in cart page
        add_action('woocommerce_after_cart_table', array($this, 'apply_template'));

        // Validate point product purchase
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_point_product_purchase_add'), 10, 3);
        add_filter('woocommerce_update_cart_validation', array($this, 'validate_point_product_purchase_update'), 10, 4);

        // Apply custom point:cost ratio
        add_filter('woocommerce_points_earned_for_cart_item', array($this, 'recalculate_points_cart'), 10, 3);
        add_filter('woocommerce_points_earned_for_order_item', array($this, 'recalculate_points_order'), 10, 5);

        // Display points in cart/checkout total lines
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'display_points_used_total'));
        add_action('woocommerce_review_order_before_order_total', array($this, 'display_points_used_total'));
        add_action('woocommerce_widget_shopping_cart_before_buttons', array($this, 'display_minicart_points_used'));

        // Add cart item data for points earned
        // add_filter('woocommerce_add_cart_item_data', array($this, 'add_item_point_earned_data'), 10, 3);

        // Change the price display of point products
        add_filter('woocommerce_get_price_html', array($this, 'change_gift_price_html_product'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'change_gift_price_html_cart'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'change_gift_subtotal_html'), 10, 3);

        // Change the message display in single product page
        add_filter('wc_points_rewards_single_product_message', array($this, 'change_product_point_msg'));

        // Replace the default display for variable products (non-point)
        // First remove the current one from WC_Points_Rewards_Product
        // add_action('init', function () {
        //     $this->remove_filters_with_method_name('woocommerce_before_add_to_cart_button', 'add_variation_message_to_product_summary', 25);
        //     remove_action('woocommerce_before_add_to_cart_button', 'add_variation_message_to_product_summary', 25);
        // }, 20);
        add_action('woocommerce_before_add_to_cart_button', array($this, 'replace_variable_product_points_html'));
        // add_filter('woocommerce_variation_price_html', array($this, 'replace_variable_reg_product_html'), 20, 2);
        // add_filter( 'woocommerce_variation_sale_price_html', array( $this, 'replace_variable_reg_product_html' ), 20, 2 );
        add_filter('woocommerce_available_variation', array($this, 'replace_available_variable_reg_product_html'), 20, 3);

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
        wp_enqueue_script(
            'variable-js',
            $plugin_url . 'variable.js',
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
                'sliders' => [
                    $this->render_slider(17), $this->render_slider(18), $this->render_slider(19),
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
        $slider_shortcode = '[products_slider cats="%d" autoplay="false" dots="false" order="ASC" orderby="meta_value_num" meta_key="_regular_price"]';

        return apply_filters('the_content', sprintf(__($slider_shortcode, 'woocommerce'), $cat_id));
    }

    /**
     * Update cart model with latest quantity
     * @param array $items
     * @param int $product_id
     * @param int $qty
     * @return array
     */
    private function add_qty_to_cart_model(&$items, $product_id, $qty)
    {
        if ($this->is_point_product($product_id)) {
            $items[$product_id] = $qty;
        }
        return $items;
    }

    /**
     * Create/update a cart model with array[prdt_id => qty]
     * @param array $items
     * @return array
     */
    private function set_cart_model($items = null)
    {
        $items = $items ?? [];
        foreach (WC()->cart->get_cart() as $item) {
            $product_id = $item['product_id'];
            $qty = $item['quantity'];
            $items = $this->add_qty_to_cart_model($items, $product_id, $qty);
        }
        return $items;
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
        if ($this->is_point_product($product_id)) {
            $variation_id = $_POST['variation_id'];

            $points = $variation_id ? $this->recalculate_product_points($variation_id, $quantity) : $this->recalculate_product_points($product_id, $quantity);
            $points_used = $this->get_points_used() + $points;

            $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
            if ($points_used > $total_points) {
                wc_add_notice(__('You don\'t have enough points!', 'woocommerce'), 'error');
                return false;
            }
        }

        return $passed;
    }

    /**
     * Check if have enough points for redeeming when update cart
     * @param bool $passed
     * @param string $cart_item_key
     * @param array $values - Cart item
     * @param int $quantity - Item new quantity
     * @return bool
     */
    public function validate_point_product_purchase_update($passed, $cart_item_key, $values, $quantity)
    {
        $product_id = $values['product_id'];
        if ($this->is_point_product($product_id)) {
            $items = $this->set_cart_model(); // Init cart model
            $items = $this->add_qty_to_cart_model($items, $product_id, $quantity); // Add current to cart model

            // Sum up the total points used
            $points_used = 0;
            foreach ($items as $product_id => $qty) {
                $points_used += $this->recalculate_product_points($product_id, $qty);
            }

            $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
            if ($total_points < $points_used) {
                wc_add_notice(__('You don\'t have enough points!', 'woocommerce'), 'error');
                return false;
            }
        }

        return $passed;
    }

    /**
     * Recalculate points earned on a non-point product basis with various ratio
     * @param int|WC_Product $product
     * @param int $amount - The default points
     * @param int $variation_id
     * @return int
     */
    private function recalculate_points($product, $amount = null, $variation_id = null)
    {
        // Return default points for point product (i.e. 0)
        if ($this->is_point_product($product)) {
            return $amount;
        }

        // $price = $this->get_product_price($product);
        $price = $variation_id ? $this->get_product_price($variation_id) : $this->get_product_price($product);
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
        $variation = $item['variation_id'];
        return $this->recalculate_points($product, $amount, $variation);
        // return $this->recalculate_points($product, $amount);
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
        $variation_id = $item->get_variation_id();
        return $this->recalculate_points($product_id, $amount, $variation_id);
    }

    /**
     * Calculate points subtotal of a POINT product (ratio-less)
     * @param WC_Product $product
     * @param array|int $cart_item_or_qty
     * @return int
     */
    private function recalculate_product_points($product, $cart_item_or_qty = null)
    {
        // Cannot use $product->get_regular_price with multi-currency plugin
        $price = $this->get_product_price($product);
        switch (gettype($cart_item_or_qty)) {
            case 'integer':
                $qty = $cart_item_or_qty;
                break;
            case 'NULL':
                $qty = 1;
                break;
            default:
                $qty = $cart_item_or_qty['quantity'];
                break;
        }
        // $qty = $cart_item['quantity'] ?: 1;
        return round($price * $qty);
    }

    /**
     * Get points used in cart
     * @return int
     */
    private function get_points_used()
    {
        $points_used = 0;
        foreach (WC()->cart->get_cart() as $item) {
            $product = $item['data'];
            if ($this->is_point_product($product)) {
                $points_used += $this->recalculate_product_points($product, $item['quantity']);
            }
        }
        return $points_used;
    }

    /**
     * Display points used in cart/checkout
     * @return void
     */
    public function display_points_used_total()
    {
        $total_points = $this->get_points_used();
        if (!$total_points) {
            return;
        }

        echo
        '<tr class="cart-subtotal">
            <th>' . __("Points Used", "woocommerce") . '</th>
            <td data-title="Points Used"><span class="woocommerce-Price-amount amount">' . $total_points . ' Points</span></td>
        </tr>';
    }

    /**
     * Display points used in minicart
     * @return void
     */
    public function display_minicart_points_used()
    {
        $total_points = $this->get_points_used();
        if (!$total_points) {
            return;
        }

        echo '<p class="woocommerce-mini-cart__total total"><strong>' . __("Points Used", "woocommerce") . ':</strong><span class="woocommerce-Price-amount amount">' . $total_points . ' Points</span></p>';
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
     * @param WC_Product $product - Variation_id in cart
     * @param array $cart_item
     * @return string
     */
    private function change_gift_price_html($price_html, $product, $cart_item = null)
    {
        // Skip for variable products (not the variations)
        if (!$this->is_point_product($product) || $product->is_type('variable')) {
            return $price_html;
        }

        // Multiple by quantity for subtotal
        $points = is_null($cart_item) ? $this->get_product_price($product) : $this->recalculate_product_points($product, $cart_item);

        $new_html = '<span class="woocommerce-Price-amount amount">' . $points . ' Points</span>';
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
     * Change points earned message in single product page. Remove message for point products. P.S. Seems only work for simple products.
     * @param string $msg
     * @return string
     */
    public function change_product_point_msg($msg)
    {
        // Hide redundant msg for variable products (not the variations)
        global $product;
        if ($this->is_point_product($product) || $product->is_type('variable')) {
            return null;
        }

        $points = $this->recalculate_points($product);
        return preg_replace('/\d+/', $points, $msg);
    }

    /**
     * Replace the default point html for available variable products for non-point prducts.
     * @param array $data
     * @param WC_Product $product
     * @param WC_Product $variation
     * @return array
     */
    public function replace_available_variable_reg_product_html($data, $product, $variation)
    {
        if (!$this->is_point_product($product) || $product->is_type('variable')) {
            $points = $this->recalculate_points($variation);
            $data['price_html'] = preg_replace('/\d+/', $points, $data['price_html'], 1);
            return $data;
        }
    }

    /**
     * Replace/hide the default html for variable products. Partially copy from WC_Points_Rewards_Product::add_variation_message_to_product_summary
     * @return void
     */
    public function replace_variable_product_points_html()
    {
        global $product;

        // make sure the product has variations (otherwise it's probably a simple product)
        if ($product && method_exists($product, 'get_available_variations')) {
            
            // Create context for the following methods and remove the embedded action
            $that = new WC_Points_Rewards_Product();
            $this->remove_filters_with_method_name('woocommerce_before_add_to_cart_button', 'add_variation_message_to_product_summary', 25);

            // Hide the html after removing default
            if ($this->is_point_product($product)) {
                return;
            }
            
            // get variations
            $variations = $product->get_available_variations();

            // find the variation with the most points
            $points = $that->get_highest_points_variation($variations, $product->get_id());

            $total_amount = $this->total_amount;
            $ratio = $this->process_ratio($this->get_ratio($total_amount));
            $points = round($points * $ratio);

            $message = '';
            // if we have a points value let's create a message; other wise don't print anything
            if ($points) {
                $message = $that->create_variation_message_to_product_summary($points);
            }

            echo $message;
        }
    }
}
