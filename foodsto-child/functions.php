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

        add_filter('yith_wcan_filter_get_title', [$this, 'change_filter_title']);

        add_action('woocommerce_before_shop_loop_item', [$this, 'render_custom_loop']);

        add_filter('vartable_header_text', [$this, 'remove_selectall_label']);
    }

    /**
     * Remove theme default layout of loop item
     */
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

    /**
     * Change category filter title
     * @param string $title - Original title
     */
    public function change_filter_title($title)
    {
        return 'Categories';
    }

    /**
     * Remove `select all` label in single product page
     * @param string $label - Original label
     */
    public function remove_selectall_label($label)
    {
        return $label === 'Select All' ? '' : $label;
    }

    /**
     * New loop item html
     */
    public function render_custom_loop()
    {
        global $product;
        global $wp;
        $id = $product->get_id();

        $url = home_url($wp->request) . '/?add-to-cart=' . $id . '&quantity=1';

        $variable = new WC_Product_Variable($id);
        if ($variations = $variable->get_available_variations()) {
            $default_var = $variations[0];

            $default_var_id = $default_var['variation_id'];
            $url .= '&variation_id=' . $default_var_id;

            $price = wc_price($default_var['display_price']);
            $max = $default_var['max_qty'];

            $unit_html = '';
            $unit_html_head = '<select value="' . $default_var_id . '"';
            foreach ($variations as $v) {
                $var_id = $v['variation_id'];
                $unit_html .= '<option value="' . $var_id . '" class="attached enabled">' . $v['attributes']['attribute_unit'] . '</option>';
                $unit_html_head .= ' data-price-' . $var_id . '="' . esc_attr(wc_price($v['display_price'])) . '"';
            }
            $unit_html = $unit_html_head . '">' . $unit_html . '</select>';
        } else {
            $price = $product->get_price_html();
            $max = $product->get_max_purchase_quantity();
            $unit_html = '<select><option value="Case" selected="selected" class="attached enabled">Case</option></select>';
        }

        $link = get_permalink($id);
        ?>
            <tr>
                <td><a href="<?=esc_attr($link)?>"><?=woocommerce_get_product_thumbnail()?></a></td>
                <td><a href="<?=esc_attr($link)?>"><?=esc_html($product->get_name())?></a></td>
                <td><span class="price"><?=$price?></span></td>
                <td><?=esc_html('variation' == $product->get_type() ? $product->get_description() : $product->get_short_description());
        ?></td>
                <td class="sz-td--2"><?=$unit_html?></td>
                <td class="sz-td--1"><div class="sz-addtocart"><input class="sz-qty" type="number" value="1" min="0" <?php
if ($max > 0) {
            echo "max='$max'";
        }
        ?> /><a href=<?=esc_url($url)?> class="button add_to_cart_button" rel="nofollow">Add to cart</a></div></td>
            </tr>
        <?php
}

}

new SZ_Foodsto();
