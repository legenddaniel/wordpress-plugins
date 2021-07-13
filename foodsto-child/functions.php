<?php

if (!defined('ABSPATH')) {
    exit;
}
include 'config.php';

class SZ_Foodsto
{
    // cat_ID => yith_ID
    private $map = [
        30 => 4681,
        // 25 => 4681,
    ];

    public function __construct()
    {
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

        add_shortcode('dahu_order', [$this, 'render_order_dahu']);

        add_action('init', [$this, 'reset_theme']);
        // add_action('init', [$this, 'add_shortcode_checkout_dahu']);

        add_action('woocommerce_add_to_cart_redirect', [$this, 'prevent_duplicate_addtocart']);

        add_action('wp_enqueue_scripts', [$this, 'init_assets']);
        // add_action('woocommerce_after_shop_loop', [$this, 'add_yith_form']);

        add_filter('yith_wcan_filter_get_title', [$this, 'change_filter_title']);

        add_action('woocommerce_before_shop_loop_item', [$this, 'render_custom_loop']);

        add_filter('vartable_header_text', [$this, 'remove_selectall_label']);

        add_filter('get_terms', [$this, 'hide_dahu'], 10, 3);
        add_action('woocommerce_before_shop_loop_item', [$this, 'render_custom_loop_dahu']);
        add_filter('wc_add_to_cart_message_html', [$this, 'remove_addtocart_msg']);
        add_action('template_redirect', [$this, 'prevent_thankyou_redirect']);
        add_filter('woocommerce_order_item_name', [$this, 'remove_order_item_link_dahu']);

    }

    /**
     * Remove theme default layout of loop item
     */
    public function reset_theme()
    {
        if (!is_page(DAHU)) {
            remove_action('woocommerce_before_shop_loop_item_title', 'foodsto_template_loop_product_thumbnail', 10);
        }
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
        if (is_page(DAHU)) {
            return;
        }

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

    /**
     * Hide dahu category
     */
    public function hide_dahu($terms, $taxonomies, $args)
    {
        $new_terms = array();

        if (in_array('product_cat', $taxonomies) && is_page(CAT)) {
            foreach ($terms as $key => $term) {
                if (!in_array($term->term_id, array(DAHU_CAT))) {
                    $new_terms[] = $term;
                }}
            $terms = $new_terms;
        }
        return $terms;
    }

    /**
     * Loop item html for Dahu
     */
    public function render_custom_loop_dahu()
    {
        if (!is_page(DAHU)) {
            return;
        }

        global $product;
        $id = $product->get_id();
        $url = get_permalink(DAHU) . '?add-to-cart=' . $id . '&quantity=1';
        $max = $product->get_max_purchase_quantity();

        ?>
            <div>
                <?=woocommerce_get_product_thumbnail()?>
                <p><?=esc_html($product->get_description());?></p>
                <span class="price"><?=$product->get_price_html();?></span>
                <div class="sz-addtocart"><input class="sz-qty" type="number" value="1" min="0" <?php
if ($max > 0) {
            echo "max='$max'";
        }
        ?> /><a href=<?=esc_url($url)?> class="button add_to_cart_button" rel="nofollow">立刻下单</a></div>
            </div>
        <?php
}

    /**
     * Stay in checkout page after checkout
     */
    public function prevent_thankyou_redirect()
    {
        $from = wp_get_referer();
        if (is_page(THANKYOU) && strpos($from, '/' . DAHU_SLUG) !== false) {
            global $wp;
            $order_id = $wp->query_vars['order-received'];
            $key = get_post_meta($order_id, '_order_key', true);
            wp_redirect(
                add_query_arg([
                    'order_id' => $order_id,
                    'key' => $key,
                ], $from)
            );
        }
    }

    /**
     * Display order details
     */
    public function render_order_dahu()
    {
        $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
        $key = isset($_GET['key']) ? $_GET['key'] : null;

        if ($order_id && $key) {
            $order_id = sanitize_text_field($order_id);
            $key = sanitize_text_field($key);

            $order = wc_get_order($order_id);
            if ($order && get_post_meta($order_id, '_order_key', true) === $key) {
                wc_get_template('checkout/thankyou.php', ['order' => $order]);
            }
        }
    }

    /**
     * Remove order item link
     */
    public function remove_order_item_link_dahu($html)
    {
        return is_page(DAHU) ? preg_replace('/^<a href=(.*)>(.*)<\/a>$/', '$2', $html) : $html;
    }

    /**
     * Prevent duplicate order submission using url addtocart
     */
    public function prevent_duplicate_addtocart($url = false)
    {
        if (!empty($url)) {
            return $url;
        }
        return get_home_url() . add_query_arg([], remove_query_arg(['add-to-cart', 'quantity']));
    }

    /**
     * Hide addtocart message
     */
    public function remove_addtocart_msg($msg)
    {
        return '';
    }
}

new SZ_Foodsto();
