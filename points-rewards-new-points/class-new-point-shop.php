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

        // Hide point products (not categories)
        // add_action('woocommerce_product_query', array($this, 'hide_products'));

        // Render rewards html in cart page
        add_action('woocommerce_before_cart_table', array($this, 'apply_template'));

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

        // Change the price display of point products
        add_filter('woocommerce_get_price_html', array($this, 'change_gift_price_html_product'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'change_gift_price_html_cart'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'change_gift_subtotal_html'), 10, 3);

        // Change the message display in single product page
        add_filter('wc_points_rewards_single_product_message', array($this, 'change_product_point_msg'));

        // Replace the default display for variable products (non-point)
        add_filter('woocommerce_available_variation', array($this, 'replace_available_variable_product_html'), 20, 3);
        add_action('woocommerce_before_add_to_cart_button', array($this, 'replace_variable_product_points_html'));

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
        $args = [
            'points' => WC_Points_Rewards_Manager::get_users_points(get_current_user_id()),
            'sliders' => [
                $this->render_slider($this->point_500_cat), $this->render_slider($this->point_1000_cat), $this->render_slider($this->point_up1000_cat),
            ],
        ];
        new_point_template_cart_rewards($args);
    }

    /**
     * Render the slider from formatted shortcodes
     * @param int $cat_id
     * @return mixed
     */
    private function render_slider($cat_id)
    {
        $slider_shortcode = '[products_slider cats="%d" autoplay="false" dots="false" order="ASC" orderby="meta_value_num" meta_key="_price"]';

        return apply_filters('the_content', sprintf(__($slider_shortcode, 'woocommerce'), $cat_id));
    }

    /**
     * Do not query products of certain categories
     * @param WP_Query
     * @return void
     */
    public function hide_products($query)
    {
        if (is_cart()) {
            return;
        }
        
        $tax_query = $query->get('tax_query');
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('points'),
            'operator' => 'NOT IN',
        );
        $query->set('tax_query', $tax_query);
    }

    /**
     * Check if current product (including its sibling variations) has been in the cart
     * @param int|WC_Product $product - Not variation
     * @return bool
     */
    private function is_in_cart($product)
    {
        if (gettype($product) === 'integer' || gettype($product) === 'string') {
            $product_id = $product;
        } else if ($product instanceof WC_Product) {
            $product_id = $product->get_id();
        } else {
            die('Not a product!');
        }

        foreach (WC()->cart->get_cart() as $item) {
            if ($item['product_id'] === $product_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update cart model with latest quantity
     * @param array $items
     * @param int $product_id
     * @param int|null $variation_id - 0 or null if no variation
     * @param int $qty
     * @return array
     */
    private function add_qty_to_cart_model(&$items, $product_id, $variation_id, $qty)
    {
        if ($this->is_point_product($product_id)) {
            $id = $variation_id ?: $product_id;
            $items[$id] = $qty;
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
            $variation_id = $item['variation_id'];
            $qty = $item['quantity'];
            $items = $this->add_qty_to_cart_model($items, $product_id, $variation_id, $qty);
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

            $points = $this->recalculate_point_product_points($product_id, $variation_id, $quantity);
            $points_used = $this->get_points_used() + $points;

            $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
            if ($points_used > $total_points) {
                wc_add_notice(__($this->text_no_point, 'woocommerce'), 'error');
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
            $variation_id = $values['variation_id'];
            $items = $this->set_cart_model(); // Init cart model
            $items = $this->add_qty_to_cart_model($items, $product_id, $variation_id, $quantity); // Add current to cart model

            // Sum up the total points used. Here $id can be the variation so no $variation_id passed.
            $points_used = 0;
            foreach ($items as $id => $qty) {
                $points_used += $this->recalculate_point_product_points($id, null, $qty);
            }

            $total_points = WC_Points_Rewards_Manager::get_users_points(get_current_user_id());
            if ($total_points < $points_used) {
                wc_add_notice(__($this->text_no_point, 'woocommerce'), 'error');
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
     * @param int $amount - The original points
     * @param string $item_key
     * @param array $item - Cart item
     * @return int
     */
    public function recalculate_points_cart($amount, $item_key, $item)
    {
        $product = $item['product_id'];
        $variation = $item['variation_id'];
        return $this->recalculate_points($product, $amount, $variation);
    }

    /**
     * Recalculate points earned on a single product basis with various ratio in order
     * @param int $amount - The original points
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
     * Get points used in cart
     * @return int
     */
    private function get_points_used()
    {
        $points_used = 0;
        foreach (WC()->cart->get_cart() as $item) {
            $product_id = $item['product_id'];
            if ($this->is_point_product($product_id)) {
                $quantity = $item['quantity'];
                $variation_id = $item['variation_id'];
                $points_used += $this->recalculate_point_product_points($product_id, $variation_id, $quantity);
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

        printf(__($this->html_cart_subtotal, 'woocommerce'), $this->text_points_used, $this->text_points_used, $total_points);
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

        printf(__($this->html_minicart_subtotal, 'woocommerce'), $this->text_points_used, $total_points);
    }

    /**
     * Set price to 0 for point products
     * @param WC_Cart $cart
     * @return void
     */
    public function change_gift_price($cart)
    {
        // Separate $product_id for both simple and variable products
        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $data = $cart_item['data'];
            if ($this->is_point_product($product_id)) {
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
     * @param WC_Product|int $product - Might be the variation itself
     * @param int $variation - Variation id
     * @param int $qty
     * @return string
     */
    private function change_gift_price_html($price_html, $product, $variation = null, $qty = 1)
    {
        if (!$this->is_point_product($product)) {
            return $price_html;
        }

        //For variable point products (for product view since 'variable' only in single product page)
        if (method_exists($product, 'is_type') && $product->is_type('variable')) {
            $variations = $product->get_children();

            $prices = array_map(function ($n) {
                return $this->get_product_price($n);
            }, $variations);
            $low = min($prices);
            $high = max($prices);

            return sprintf($this->html_variable_point_product_price, $low, $high);
        }

        // For simple/variation point products. Most probably ids as the param.
        $points = $this->recalculate_point_product_points($product, $variation, $qty);

        // Should not apply in checkout page. Apply in cart page due to the point product slider
        if (!is_checkout() && method_exists($product, 'is_on_sale') && $product->is_on_sale()) {
            $reg_points = $this->recalculate_point_product_points($product, $variation, $qty, 'regular');

            return sprintf($this->html_onsale_variable_point_product_price, $reg_points, $points);
        }

        return sprintf($this->html_single_point_product_price, $points);
    }

    /**
     * Change price to points for gifts in a product view
     * @param string $price_html - Price html
     * @param WC_Product $product
     * @return string
     */
    public function change_gift_price_html_product($price_html, $product)
    {
        // $product must be WC_Product instance rather than id
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
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        return $this->change_gift_price_html($price_html, $product_id, $variation_id);
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
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        $qty = $cart_item['quantity'];
        return $this->change_gift_price_html($subtotal_html, $product_id, $variation_id, $qty);
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
     * Replace/hide the default point price html for available variable products.
     * @param array $data
     * @param WC_Product $product
     * @param WC_Product $variation
     * @return array
     */
    public function replace_available_variable_product_html($data, $product, $variation)
    {
        // Hide the msg if item already in the cart
        if ($this->is_in_cart($product)) {
            $data['price_html'] = '';
            return $data;
        }

        // Points earned for regular products (Purchase this product now and earn XX Points!). Apply to single product page rather than the preview since (Purchase this product now and earn XX Points!) is not in the preview.
        if (!$this->is_point_product($product)) {
            if (is_product()) {
                $points = $this->recalculate_points($variation);
                $data['price_html'] = preg_replace('/\d+/', $points, $data['price_html'], 1);
            }
            return $data;
        }

        // Points used for point products (XX Points)
        if ($product->is_type('variable')) {
            $points = $this->recalculate_point_product_points($product, $variation);
            if ($product->is_on_sale()) {
                $reg_points = $this->recalculate_point_product_points($product, $variation, 1, 'regular');
                $price_html = '<span class="price">' . sprintf($this->html_onsale_variable_point_product_price, $reg_points, $points) . '</span>';
            } else {
                $price_html = '<span class="price">' . sprintf($this->html_single_point_product_price, $points) . '</span>';
            }

            $data['price_html'] = $price_html;
            return $data;
        }
    }

    /**
     * Replace/hide the default html for variable products (Earn up to XX Points). Partially copy from WC_Points_Rewards_Product::add_variation_message_to_product_summary
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

            // Our client doesn't need anything below for now.
            return;

            /*

            // Hide the html for point products after removing default, or item already in the cart
            if ($this->is_point_product($product) || $this->is_in_cart($product)) {
                return;
            }

            // get variation with the highest price. $that->get_highest_points_variation() not working properly
            $variations = $product->get_available_variations();
            $variation_ids = wp_list_pluck($variations, 'variation_id');

            $prices = array_map(function ($id) {
                return $this->get_product_price($id);
            }, $variation_ids);
            $price = max($prices);

            $total_amount = $this->total_amount;
            $ratio = $this->process_ratio($this->get_ratio($total_amount));
            $points = round($price * $ratio);

            $message = '';
            // if we have a points value let's create a message; other wise don't print anything
            if ($points) {
                $message = $that->create_variation_message_to_product_summary($points);
            }

            echo $message;
            */
        }
    }
}
