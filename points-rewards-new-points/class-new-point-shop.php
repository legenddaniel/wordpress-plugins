<?php

class New_Point_Shop extends New_Point
{
    private $total_amount = 0;
    private $ratio_type = '';

    public function __construct()
    {
        $this->total_amount = $this->get_total_amount(get_current_user_id());
        $this->ratio_type = $this->get_ratio_type($this->total_amount);

        add_action('woocommerce_after_cart_table', function () {
            //     echo apply_filters( 'the_content',"[wcps id='90']");
            // echo apply_filters( 'the_content',"[TABS_R id=91]");
            echo apply_filters('the_content', "[WPSM_AC id=105]");
            // echo apply_filters( 'the_content',"[carousel_slide id='102']");
            // echo apply_filters( 'the_content','[sp_wpcarousel id="94"]');

        });

        // Apply custom point:cost ratio
        add_filter('woocommerce_points_earned_for_cart_item', array($this, 'recalculate_ratio'));
        add_filter('woocommerce_points_earned_for_order_item', array($this, 'recalculate_ratio'));

        // Display points in cart/checkout total lines
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'display_points_used_total'));
        add_action('woocommerce_review_order_before_order_total', array($this, 'display_points_used_total'));

        // Add cart item data for points earned
        // add_filter('woocommerce_add_cart_item_data', array($this, 'add_item_point_earned_data'), 10, 3);

        // Change the price display of point products
        add_filter('woocommerce_get_price_html', array($this, 'change_gift_price_html_product'), 10, 2);
        add_filter('woocommerce_cart_item_price', array($this, 'change_gift_price_html_cart'), 10, 3);

        // Change the price of point product
        add_action('woocommerce_before_calculate_totals', array($this, 'change_gift_price'));
    }

    /**
     * Recalculate points earned on a single product basis with various ratio in cart
     * @param string|double $amount - The original points
     * @return int
     */
    public function recalculate_ratio($amount)
    {
        $total_amount = $this->total_amount;
        $ratio = $this->process_ratio($this->get_ratio($total_amount));

        return $amount * $ratio;
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
                $total_points += $this->recalculate_points($data, $cart_item);
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

        $new_html = $this->recalculate_points($product) . ' points';

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

        $new_html = $this->recalculate_points($product, $cart_item) . ' points';

        return $new_html;
    }

}
