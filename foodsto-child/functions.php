<?php

if (!defined('ABSPATH')) {
    exit;
}

class SZ_Foodsto
{
    // cat_ID => yith_ID
    private $map = [
        30 => 4681,
        // 25 => 4681,
    ];

    public function __construct()
    {
        add_action('init', [$this, 'reset_theme']);

        add_action('wp_enqueue_scripts', [$this, 'init_assets']);
        // add_action('woocommerce_after_shop_loop', [$this, 'add_yith_form']);

        add_action('woocommerce_before_shop_loop_item', [$this, 'render_custom_loop']);
    }

    public function reset_theme()
    {
        remove_action('woocommerce_before_shop_loop_item_title', 'foodsto_template_loop_product_thumbnail', 10);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    }

    public function init_assets()
    {
        $parenthandle = 'parent-style';
        $theme = wp_get_theme();
        wp_enqueue_style(
            $parenthandle,
            get_template_directory_uri() . '/style.css',
            array(),
            $theme->parent()->get('Version')
        );
        wp_enqueue_style(
            'child-style',
            get_stylesheet_uri(),
            array($parenthandle),
            rand(111, 9999)
        );
        wp_enqueue_script(
            'qty',
            get_stylesheet_directory_uri() . '/qty.js',
            array(),
            rand(111, 9999)
        );
    }

    public function add_yith_form()
    {
        global $wp_query;
        $cat_obj = $wp_query->get_queried_object();
        $cat_ID = $cat_obj->term_id;
        $form = $this->map[$cat_ID];
        if ($form) {
            yith_quick_order_form($form);
        }
    }

    public function render_custom_loop()
    {
        global $product;
        $link = get_permalink($product->get_id());
        ?>
            <tr>
                <td><a href="<?=esc_attr($link)?>"><?=woocommerce_get_product_thumbnail()?></a></td>
                <td><a href="<?=esc_attr($link)?>"><?=esc_html($product->get_name())?></a></td>
                <td><?php
if ($product->get_sale_price()) {
            ?>
                        <del>
                            <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?=esc_attr(get_woocommerce_currency_symbol());?></span><?=esc_html($product->get_regular_price());?></span>
                            </del>
                        <?php
}
        if ($product->get_sale_price()) {
            $price = $product->get_sale_price();
        } else {
            $price = $product->get_price();
        }
        ?>
                        <ins>
                            <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?=esc_attr(get_woocommerce_currency_symbol());?></span><?=esc_html($price);?></span>
                        </ins></td>
                <td><?=esc_html('variation' == $product->get_type() ? $product->get_description() : $product->get_short_description());
        ?></td>
                <td><div class="sz-addtocart"><input class="sz-qty" type="number" value="1" min="1" <?php
$max = $product->get_max_purchase_quantity();
        if ($max > 0) {
            echo "max='$max'";
        }
        ?> /><?=woocommerce_template_loop_add_to_cart();?></div></td>
            </tr>
        <?php
}

}

new SZ_Foodsto();
