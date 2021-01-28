<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Moditec
{
    private $cart_ad = 9116;

    public function __construct()
    {
        // Relocate the description box
        remove_action('woocommerce_after_single_product_summary', [$this, 'woocommerce_output_product_data_tabs'], 10);
        add_action('woocommerce_product_meta_start', [$this, 'custom_desc']);

        // Hide CAD/USD
        add_filter('woocommerce_currency_symbol', [$this, 'hide_currency_html'], 20, 2);

        // Display recommended products slider
        add_action('woocommerce_after_cart_table', [$this, 'display_recommended_products']);

        // Relocate coupon field
        add_action('woocommerce_after_cart_totals', [$this, 'relocate_coupon_field']);

        // Display cart ad
        add_action('woocommerce_cart_collaterals', [$this, 'display_cart_ad']);

        // Display cart ad settings in admin
        // add_filter('woocommerce_general_settings', [$this, 'admin_display_ad_settings']);

        // Display gap to free shipping label in cart/checkout
        add_action('woocommerce_after_shipping_rate', [$this, 'free_shipping_notice']);
    }

    public function custom_desc()
    {
        $content = the_content();
        if (!$content) {
            return;
        }
        ?>
        <div class="sz-desc-wrapper">
            <?php $content;?>
        </div>
    <?php
}

    public function hide_currency_html($currency_symbol, $currency)
    {
        return '$';
    }

    private function render_slider($cat_id)
    {
        $slider_shortcode = '[products_slider cats="%d" autoplay="false" dots="false" slide_to_show="4"]';

        return apply_filters('the_content', sprintf(__($slider_shortcode, 'woocommerce'), $cat_id));
    }

    public function display_recommended_products()
    {
        ?>
            <div>
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

    public function display_cart_ad()
    {
        the_ad($this->cart_ad);
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

    public function free_shipping_notice()
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

            if ($method->id == 'free_shipping' && !empty($min_amount) && $cart_total < $min_amount) {
                $remaining = $min_amount - $cart_total;
                printf(__('<div class="sz-free-shipping-msg-wrapper"><div class="sz-free-shipping-msg"><span>%s to free shipping!</span></div></div>', 'woocommerce'), wc_price($remaining));
            }
        }

    }
}

new WC_Moditec();
