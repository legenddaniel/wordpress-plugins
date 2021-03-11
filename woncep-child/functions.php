<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Moditec
{
    private $max_address = 2;
    private $cart_ad = 9116;
    private $size_chart = 9466;
    private $popup = 10225; // 11763 for storem

    private $point_cat = 182; // When you change this, change also $point_cat in New_Point
    private $new_arrival_limit = 8;
    private $top_seller_limit = 8;

    private $new_arrivals = [];
    private $top_sellers = [];

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'init_assets']);

        // Change default max address of the multi-shipping-address plugin
        add_action('init', [$this, 'set_max_address']);

        // Query product info during certain pages pre-load
        add_action('template_redirect', [$this, 'init_special_products']);

        // Display 'New Arrival/Top Sellers/Sales' label
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'display_label']);
        add_action('woocommerce_product_thumbnails', [$this, 'display_label']);

        // Change 'Read More' button text to 'Out Of Stock'
        add_filter('gettext', [$this, 'change_read_more_text'], 10, 3);

        // Hide default sale label
        add_filter('woocommerce_sale_flash', [$this, 'hide_sale_label']);

        // Hide 'Select Options' for variable products
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'hide_select_option'], 10, 2);

        // Display size chart
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
        add_action('woocommerce_product_meta_start', 'woocommerce_template_single_excerpt');
        // add_action('woocommerce_product_meta_start', [$this, 'display_size_chart']);

        // Reset size chart
        add_filter('woocommerce_short_description', [$this, 'custom_short_desc']);

        // Relocate the description box
        // remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
        add_filter('woocommerce_product_tabs', [$this, 'display_review_only']);
        add_action('woocommerce_after_single_product_summary', [$this, 'custom_desc'], 9); // priority < 10

        // Add direct checkout button in product page
        // add_action('woocommerce_after_add_to_cart_button', [$this, 'add_checkout_in_product']);
        // add_filter('woocommerce_add_to_cart_redirect', [$this, 'direct_checkout']);

        // Ceil price and hide decimals, care the priority
        add_filter('woocommerce_price_trim_zeros', [$this, 'hide_decimals']);
        add_filter('woocommerce_get_price_html', [$this, 'ceil_product_price_product'], 9, 2);
        // add_filter('woocommerce_cart_item_price', [$this, 'ceil_product_price_cart'], 9, 3);
        // add_filter('woocommerce_cart_item_subtotal', [$this, 'ceil_product_subtotal'], 9, 3);
        add_action('woocommerce_before_calculate_totals', [$this, 'change_product_price'], 9);

        // Hide stock
        add_filter('woocommerce_get_stock_html', [$this, 'hide_stock_html'], 10, 2);

        // Hide CAD/USD
        // add_filter('woocommerce_currency_symbol', [$this, 'hide_currency_html'], 20, 2);

        // Display recommended products slider. Display 1st on large and 2nd on small screen
        // add_action('woocommerce_after_cart_table', [$this, 'display_recommended_products']);
        // add_action('woocommerce_cart_collaterals', [$this, 'display_recommended_products']);

        // Relocate coupon field
        add_action('woocommerce_after_cart_totals', [$this, 'relocate_coupon_field']);

        // Display cart ad
        // add_action('woocommerce_cart_collaterals', [$this, 'display_cart_ad']);

        // Display cart ad settings in admin
        // add_filter('woocommerce_general_settings', [$this, 'admin_display_ad_settings']);

        // Display gap to free shipping label in cart/checkout
        add_action('woocommerce_after_shipping_rate', [$this, 'free_shipping_notice']);

        // Hide flat_rate if free shipping is applicable
        add_filter('woocommerce_package_rates', [$this, 'hide_flat_when_free_shipping'], 10, 2);

        // Provide link to register in checkout
        // add_action('woocommerce_before_checkout_form_cart_notices', [$this, 'add_checkout_register_link']);
        // add_filter('woocommerce_checkout_must_be_logged_in_message', [$this, 'remove_default_checkout_login_msg']);

        // Add meta box for tracking number in admin order page, as well as in my account page
        add_action('add_meta_boxes_shop_order', [$this, 'add_order_tracking_number']);
        add_action('woocommerce_process_shop_order_meta', [$this, 'save_order_metabox'], 10, 2);
        add_action('woocommerce_order_details_after_order_table', [$this, 'display_tracking_number_myaccount']);

        // Add footer subscription to sgpb database
        add_action('wp_ajax_elementor_pro_forms_send_form', [$this, 'handle_subscription']);
        add_action('wp_ajax_elementor_pro_forms_send_form', [$this, 'handle_subscription']);
    }

    public function init_assets()
    {
        wp_enqueue_script(
            'breadcrumb-home',
            get_stylesheet_directory_uri() . '/breadcrumb-home.js',
            ['jquery'],
            rand(111, 9999)
        );
        wp_enqueue_script(
            'register-form',
            get_stylesheet_directory_uri() . '/register-form.js',
            ['jquery'],
            rand(111, 9999)
        );
        wp_enqueue_script(
            'abort-resubmission',
            get_stylesheet_directory_uri() . '/abort-resubmission.js',
            ['jquery'],
            rand(111, 9999)
        );
        wp_enqueue_script(
            'subscription-cookie',
            get_stylesheet_directory_uri() . '/subscription-cookie.js',
            ['jquery'],
            rand(111, 9999)
        );
        wp_enqueue_script(
            'search-result',
            get_stylesheet_directory_uri() . '/search-result.js',
            ['jquery'],
            rand(111, 9999)
        );
        // wp_enqueue_script(
        //     'hide-zone',
        //     get_stylesheet_directory_uri() . '/hide-zone.js',
        //     ['jquery'],
        //     rand(111, 9999)
        // );
        // wp_enqueue_script(
        //     'wechat-video',
        //     get_stylesheet_directory_uri() . '/wechat-video.js',
        //     ['jquery'],
        //     rand(111, 9999)
        // );
        if (is_product()) {
            wp_enqueue_script(
                'direct-checkout',
                get_stylesheet_directory_uri() . '/direct-checkout.js',
                ['jquery'],
                rand(111, 9999)
            );
        }

    }

    private function get_top_sellers()
    {
        // Partially copied from WC_Admin_Dashboard::get_top_seller
        global $wpdb;

        // $query = array();
        // $query['fields'] = "SELECT SUM( order_item_meta.meta_value ) as qty, order_item_meta_2.meta_value as product_id
        //     FROM {$wpdb->posts} as posts";
        // $query['join'] = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id ";
        // $query['join'] .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id ";
        // $query['join'] .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id ";
        // $query['where'] = "WHERE posts.post_type IN ( '" . implode("','", wc_get_order_types('order-count')) . "' ) ";
        // $query['where'] .= "AND posts.post_status IN ('wc-processing','wc-completed') ";
        // $query['where'] .= "AND order_item_meta.meta_key = '_qty' ";
        // $query['where'] .= "AND order_item_meta_2.meta_key = '_product_id' ";
        // $query['groupby'] = 'GROUP BY product_id';
        // $query['orderby'] = 'ORDER BY qty DESC';
        // $query['limits'] = 'LIMIT ' . $this->top_seller_limit;

        // return $wpdb->get_results(implode(' ',  $query));

        // $top_sellers = $wpdb->get_results(
        //     $wpdb->prepare(
        //         "SELECT p.ID
        //         FROM {$wpdb->prefix}posts p
        //         INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim
        //             ON p.ID = oim.meta_value
        //         INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2
        //             ON oim.order_item_id = oim2.order_item_id
        //         INNER JOIN {$wpdb->prefix}woocommerce_order_items oi
        //             ON oim.order_item_id = oi.order_item_id
        //         INNER JOIN {$wpdb->prefix}posts as o
        //             ON o.ID = oi.order_id
        //         WHERE p.post_type = 'product'
        //         AND p.post_status = 'publish'
        //         AND o.post_status IN ('wc-processing','wc-completed')
        //         AND oim.meta_key = '_product_id'
        //         AND oim2.meta_key = '_qty'
        //         AND p.ID NOT IN (
        //                 SELECT DISTINCT object_id
        //                 FROM {$wpdb->prefix}term_relationships
        //                 WHERE term_taxonomy_id = %d
        //             )
        //         GROUP BY p.ID
        //         ORDER BY COUNT(oim2.meta_value) * 1 DESC
        //         LIMIT %d",
        //         [$this->point_cat, $this->top_seller_limit]
        //     )
        // );

        $top_sellers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.post_id
                FROM {$wpdb->prefix}postmeta AS p
                WHERE
                    p.meta_key = 'total_sales' AND
                        p.post_id NOT IN (
                            SELECT DISTINCT t.object_id
                            FROM {$wpdb->prefix}term_relationships AS t
                            WHERE t.term_taxonomy_id = %d
                        )
                ORDER BY p.meta_value * 1 DESC
                LIMIT %d",
                [$this->point_cat, $this->top_seller_limit]
            )
        );

        return $top_sellers;
    }

    private function get_new_arrivals()
    {
        global $wpdb;

        $new_arrivals = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT post.ID
                FROM {$wpdb->prefix}posts AS post
                WHERE
                    post.post_status = 'publish' AND
                    post.post_type = 'product' AND
                    post.ID NOT IN (
                        SELECT DISTINCT term.object_id
                        FROM {$wpdb->prefix}term_relationships AS term
                        WHERE term.term_taxonomy_id = %d
                    )
                ORDER BY post.post_date DESC, post.ID DESC
                LIMIT %d",
                [$this->point_cat, $this->new_arrival_limit]
            ),
        );
        return $new_arrivals;
    }

    public function init_special_products()
    {
        if (is_home() || is_shop() || is_product_category() || is_product() || strpos(get_page_link(), 'index') !== false) {

            $new_arrivals = $this->get_new_arrivals();
            if ($new_arrivals) {
                foreach ($new_arrivals as $new_arrival) {
                    $this->new_arrivals[] = $new_arrival->ID;
                }
            }

            $top_sellers = $this->get_top_sellers();
            if ($top_sellers) {
                foreach ($top_sellers as $top_seller) {
                    $this->top_sellers[] = $top_seller->post_id;
                    // $this->top_sellers[] = $top_seller->ID;
                }
            }
        }
    }

    public function change_read_more_text($translated_text, $text, $domain)
    {
        if (!is_admin() && $domain === 'woocommerce' && $translated_text === 'Read more') {
            $translated_text = 'Out Of Stock';
        }
        return $translated_text;
    }

    public function hide_sale_label()
    {
        return false;
    }

    public function display_label()
    {
        global $product;
        if (!$product->is_in_stock()) {
            return;
        }

        $product_id = $product->get_id();

        // Priority: 1. new arrival 2. top seller 3. sale
        $label = '';
        if ($product->is_on_sale()) {
            $label = 'Sale';
        }
        if (in_array($product_id, $this->top_sellers)) {
            $label = 'Top Seller';
        }
        if (in_array($product_id, $this->new_arrivals)) {
            $label = 'New Arrival';
        }

        if (!$label) {
            return;
        }

        echo '<span class="sz-label">' . $label . '</span>';
    }

    public function hide_select_option($html, $product)
    {
        if ($product->get_type() !== 'simple') {
            return;
        }
        return $html;
    }

    public function custom_short_desc($short_desc)
    {
        if ($short_desc) {
            preg_match('/\[the_ad id="\d+"\]/', $short_desc, $ad);
        }
        if ($ad) {
            $html = do_shortcode($ad[0]);
            preg_match('/<a href="http.*wp-content\/uploads.*(png|jpg|jpeg)">/', $html, $a_half);
        }
        if ($a_half) {
            $new_desc = '<div class="sz-size-chart"><span class="button">' . $a_half[0] . '-- Size Chart --<a/></span></div>' . $html; // Must include this redundant $html.
        }

        return $new_desc ?: $short_desc;
    }

    public function display_review_only($tabs)
    {
        $new_tab = [];
        $new_tab['reviews'] = $tabs['reviews'];
        return $new_tab;
    }

    public function custom_desc()
    {
        $content = get_the_content();
        if (!$content) {
            return;
        }
        ?>
    <div class="sz-desc-wrapper">
        <?php woocommerce_product_description_tab();?>
    </div>
        <?php
}

    public function add_checkout_in_product()
    {
        // Only work for simple and variable products
        echo '<a id="sz-custom-checkout" href="' . wc_get_checkout_url() . '?add-to-cart=$ID&quantity=$QTY" class="single_add_to_cart_button button alt">Checkout</a>';
    }

    public function direct_checkout($url)
    {
        $checkout_url = wc_get_checkout_url();
        if (strpos($url, $checkout_url) !== false) {
            return $checkout_url;
        }
        return $url;
    }

    public function hide_stock_html($html, $product)
    {
        return '';
    }

    public function hide_currency_html($currency_symbol, $currency)
    {
        return '$';
    }

    public function hide_decimals($display)
    {
        return true;
    }

    private function get_currency_rate()
    {
        $currency_params = get_option('woo_multi_currency_params');
        $currencies = $currency_params['currency'];
        $currency_rates = $currency_params['currency_rate'];
        $rates = [];
        foreach ($currencies as $k => $currency) {
            $rates[$currency] = $currency_rates[$k];
        }
        $rate = $rates['CAD'] / $rates['USD'];

        return $rate;
    }

    private function get_product_active_price($product)
    {
        $product_id = $product->get_id();
        $price = $product->is_on_sale() ?
        get_post_meta($product_id, '_sale_price', true) :
        get_post_meta($product_id, '_regular_price', true);

        return $price;
    }

    private function ceil_product_price($price_html, $product, $qty = 1)
    {
        if (is_admin()) {
            return $price_html;
        }

        if (get_woocommerce_currency() === 'USD') {
            return $price_html;
        }

        if ($product->is_on_sale()) {
            if ($product->is_type('variable')) {
                // Assume all variations have same price
                $prices = $product->get_variation_prices(true);

                $regular_price = ceil(min($prices['regular_price']));
                $sale_price = ceil(min($prices['sale_price']));

                return wc_format_sale_price($regular_price, $sale_price);
            }
            return $price_html;
        }

        $price = $this->get_product_active_price($product);

        $rate = $this->get_currency_rate();

        if (is_cart() || is_checkout()) {
            $price = $price * $rate;
        } else {
            $price = wc_get_price_to_display($product);
        }

        return wc_price(ceil($price) * $qty);
    }

    public function ceil_product_price_product($price_html, $product)
    {
        return $this->ceil_product_price($price_html, $product);
    }

    public function ceil_product_price_cart($price_html, $cart_item, $cart_item_key)
    {
        return $this->ceil_product_price($price_html, $cart_item['data']);
    }

    public function ceil_product_subtotal($subtotal_html, $cart_item, $cart_item_key)
    {
        return $this->ceil_product_price($subtotal_html, $cart_item['data'], $cart_item['quantity']);
    }

    public function change_product_price($cart)
    {
        if (get_woocommerce_currency() === 'USD') {
            return;
        }

        $rate = $this->get_currency_rate();

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            // if ($product->is_on_sale()) {
            //     continue;
            // }
            $price = $this->get_product_active_price($product);

            $product->set_price(ceil($price * $rate) / $rate);
        }

    }

    private function render_slider($cat_id)
    {
        $slider_shortcode = '[products_slider cats="%d" autoplay="false" dots="false" slide_to_show="4"]';

        return apply_filters('the_content', sprintf(__($slider_shortcode, 'woocommerce'), $cat_id));
    }

    public function display_recommended_products()
    {
        ?>
            <div class="sz-recommend">
                <h2>Recommended For You</h2>
                <?=$this->render_slider(124);?>
            </div>
        <?php
}

    public function relocate_coupon_field()
    {
        ?>
      <form class="sz-coupon-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
         <?php if (wc_coupons_enabled()) {?>
            <div class="coupon under-proceed">
               <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Promo or Reward Code', 'woocommerce');?>" style="width: 100%" />
               <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce');?>" style="width: 100%"><?php esc_attr_e('Apply coupon', 'woocommerce');?></button>
            </div>
         <?php }?>
      </form>
   <?php
}

    private function display_ad($ad)
    {
        the_ad($ad);
    }

    public function display_cart_ad()
    {
        $this->display_ad($this->cart_ad);
    }

    public function display_size_chart()
    {
        // May have different size charts for different products (e.g. coats, pants, t-shirts, etc.)
        $this->display_ad($this->size_chart);
    }

    public function admin_display_ad_settings($settings)
    {
        $new_settings = [
            [
                'title' => __('Display Ad In Cart', 'woocommerce'),
                'type' => 'title',
                'id' => 'display_cart_ad',
            ],
            [
                'title' => __('Display Image and/or Text', 'woocommerce'),
                'desc' => __('Ad Image', 'woocommerce'),
                'id' => 'display_cart_ad_img',
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'show_if_checked' => 'option',
            ],
            [
                'desc' => __('Ad Text', 'woocommerce'),
                'id' => 'display_cart_ad_txt',
                'type' => 'checkbox',
                'checkboxgroup' => 'end',
                // 'show_if_checked' => 'yes',
            ],
            [
                'type' => 'sectionend',
                'id' => 'display_cart_ad',
            ],
        ];
        return array_merge($settings, $new_settings);
    }

    public function free_shipping_notice($rate)
    {

        $cart = WC()->cart;

        $packages = $cart->get_shipping_packages();
        $package = reset($packages);
        $zone = wc_get_shipping_zone($package);

        $cart_total = $cart->get_displayed_subtotal();
        if ($cart->display_prices_including_tax()) {
            $cart_total = round($cart_total - ($cart->get_discount_total() + $cart->get_discount_tax()), wc_get_price_decimals());
        } else {
            $cart_total = round($cart_total - $cart->get_discount_total(), wc_get_price_decimals());
        }
        foreach ($zone->get_shipping_methods(true) as $k => $method) {
            $min_amount = $method->get_option('min_amount');

            if ($rate->method_id == 'flat_rate' && $method->id == 'free_shipping' && !empty($min_amount) && $cart_total < $min_amount) {
                $min_amount = get_woocommerce_currency() === 'USD' ? $min_amount : round($min_amount);
                $remaining = $min_amount - $cart_total;
                printf(__('<div class="sz-free-shipping-msg-wrapper"><div class="sz-free-shipping-msg"><span>%s to free shipping!</span></div></div>', 'woocommerce'), wc_price($remaining));
            }
        }
    }

    public function hide_flat_when_free_shipping($rates, $package)
    {
        $new_rates = array();
        foreach ($rates as $rate_id => $rate) {
            if ('free_shipping' === $rate->method_id) {
                $new_rates[$rate_id] = $rate;
                break;
            }
        }

        if (!empty($new_rates)) {
            foreach ($rates as $rate_id => $rate) {
                if ($rate->label === 'Priority') {
                    $new_rates[$rate_id] = $rate;
                    break;
                }
            }
            return $new_rates;
        }

        return $rates;
    }

    public function set_max_address()
    {
        update_option('ocwma_max_adress', strval($this->max_address));
    }

    public function add_checkout_register_link($checkout)
    {
        if (is_user_logged_in()) {
            return;
        }

        echo '<p class="sz-guest">You must be logged in to checkout.</p>';

        wc_get_template('myaccount/form-login.php');
    }

    public function remove_default_checkout_login_msg($msg)
    {
        return;
    }

    public function display_tracking_number_field($post)
    {
        $id = $post->ID;
        $tracking_number = get_post_meta($id, 'tracking_number', true);

        $field = [
            'id' => 'sz-tracking-number',
            'type' => 'text',
            'value' => $tracking_number,
        ];
        woocommerce_wp_text_input($field);
    }

    public function add_order_tracking_number($post)
    {
        if ($post->post_type !== 'shop_order') {
            return;
        }

        add_meta_box(
            'sz-tracking-number',
            __('UPS Tracking Number', 'woocommerce'),
            [$this, 'display_tracking_number_field'],
            'shop_order',
            'side',
            'high'
        );
    }

    public function save_order_metabox($id, $post)
    {
        if ($post->post_type !== 'shop_order') {
            return;
        }

        $tracking_number = $_POST['sz-tracking-number'] ?: '';
        update_post_meta($id, 'tracking_number', $tracking_number);
    }

    public function display_tracking_number_myaccount($order)
    {
        $order_id = $order->get_id();
        $tracking_number = get_post_meta($order_id, 'tracking_number', true);

        $info = $tracking_number ?: 'No tracking number info.';
        $color = $tracking_number ? ' style="color: #000;"' : '';

        echo '<div style="margin: 3rem auto;">';
        echo '<h2>UPS Tracking Number</h2>';
        echo '<p' . $color . '>' . esc_html__($info, 'woocommerce') . '</p>';
        echo '</div>';
    }

    public function handle_subscription()
    {
        $email = sanitize_email($_POST['form_fields']['email']);

        global $wpdb;
        $is_subscribed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT email FROM {$wpdb->prefix}sgpb_subscribers
                WHERE email = %s AND unsubscribed = 0",
                $email
            )
        );

        if ($is_subscribed) {
            $res = [
                'data' => [
                    'data' => [],
                    'message' => 'You have already subscribed!',
                ],
                'success' => false,
            ];
            wp_send_json($res);
        }

        $date = date('Y-m-d', time());
        $add = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}sgpb_subscribers
                (email, subscriptionType, cDate, status, unsubscribed)
                VALUES (%s, %d, %s, 1, 0)",
                [$email, $this->popup, $date]
            )
        );

        if ($add) {
            $site_url = get_site_url(); // http://www.domain.com (no slash)
            $value = '["' . $site_url . '/index/"]';

            $cookie = [
                'data' => [
                    'data' => [],
                    'cookie' => [
                        'type' => 'subscription',
                        'name' => 'SGPBSubscription' . $this->popup,
                        'value' => $value,
                        'expire' => 365,
                    ],
                    'message' => 'Successfully subscribed!',
                ],
                'success' => true,
            ];
            wp_send_json($cookie);
        }
    }
}

new WC_Moditec();
