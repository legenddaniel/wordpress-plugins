<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'sz_init_assets_new_points');
function sz_init_assets_new_points()
{
    if (is_shop()) {
        wp_enqueue_script(
            'hide-breadcrumbs',
            get_stylesheet_directory_uri() . '/hide-breadcrumbs.js',
            ['jquery'],
            rand(111, 9999)
        );
    }
}

add_action('woocommerce_after_cart_totals', 'sz_relocate_coupon_field');
function sz_relocate_coupon_field()
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

add_action('woocommerce_cart_collaterals', 'sz_display_cart_ad');
function sz_display_cart_ad()
{
    ?>
        <div class="sz-cart-ad">
            <figure class="sz-cart-ad-img-wrapper">
                <a href=""><img src="/wp-content/uploads/2021/01/favicon-150x150-1.png" alt="img here"></a>
            </figure>
            <div class="sz-cart-ad-txt-wrapper">
                <p>Need Assistance?</p>
                <p class="sz-cart-ad-txt-contact-wrapper">
                    <span><a href="mailto:moditec.na@gmail.com" target="_blank">moditec.na@gmail.com</a></span>
                    <span><a href="tel:905-940-2120">905-940-2120</a></span>
                    <span>Or use our <a href="/contact">Contact Form</a></span>
                </p>
                <p>Free Return Shipping</p>
                <div>
                    We Accept<br>
                    <span>icon</span>
                    <span>icon</span>
                    <span>icon</span>
                </div>
            </div>
        </div>
    <?php
}

add_filter('woocommerce_general_settings', 'sz_admin_display_ad_settings');
function sz_admin_display_ad_settings($settings)
{
    $new_settings = [
        [
            'title' => __('Display Ad In Cart', 'woocommerce'),
            'type' => 'title',
        ],
        [
            'title' => __('Display Image and/or Text', 'woocommerce'),
            'desc' => __('Ad Image', 'woocommerce'),
            'id' => 'sz_display_cart_ad_img',
            'type' => 'checkbox',
            'checkboxgroup' => 'start',
        ],
        [
            'desc' => __('Ad Text', 'woocommerce'),
            'id' => 'sz_display_cart_ad_txt',
            'type' => 'checkbox',
            'checkboxgroup' => 'end',
        ],
        [
            'type' => 'sectionend',
            'id' => 'pricing_options',
        ],
    ];
    return array_merge($settings, $new_settings);
}
