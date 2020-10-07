<?php
/*
 * Plugin Name: Custom Booking
 * Version: 0.0.0
 * Plugin URI: null
 * Description: Custom booking
 * Author: Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: costom-booking
 */

 // Exit if accessed directly
defined('ABSPATH') or exit;

// Times remaining of the Promo Pass. Fetch from the database
// Type of pass will be in the future
$promo_count = 11;

function load_style()
{
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style('style', $plugin_url . '/style.css', array(), rand(111, 9999));
}
add_action('wp_enqueue_scripts', 'load_style');

/**
 * @desc Check if the current product is 'Singular Passes'
 * @return boolean
 */
function is_singular_pass()
{
    return get_the_ID() === 304;
}

/**
 * @desc Add BYOE checkbox in 'Singular Passes'
 * @return void
 */
function add_byoe_checkbox()
{
    is_singular_pass() and
    woocommerce_form_field('byoe', array(
        'type'        => 'checkbox',
        'title'       => 'Only apply to Archery',
        'label'       => 'Bring Your Own Equipment (apply to Archery)',
        'label_class' => array('sz-size-checkbox'),
    ));
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'add_byoe_checkbox');

/**
 * @desc Add 'Use Promo' checkbox in 'Singular Passes'
 * @return void
 */
function add_promo_checkbox()
{
    global $promo_count;
    is_singular_pass() and
    woocommerce_form_field('promo', array(
        'type'        => 'checkbox',
        'label'       => 'Use Promo' . " ($promo_count times left)",
        'label_class' => 'sz-size-checkbox',
    ));
}
// 'woocommerce_before_single_variation' not working, the calendar keeps loading
add_action('woocommerce_before_add_to_cart_button', 'add_promo_checkbox');

/**
 * @desc Remove '(optional)' text from the checkbox label in 'Singular Passes'
 * @param string $field
 * @param string $key
 * @param array $args
 * @param string? $value
 * @return void
 */
function remove_label_optional_text($field, $key, $args, $value)
{
    if (is_singular_pass()) {
        $optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
        $field = str_replace($optional, '', $field);
    }
    return $field;
}
add_filter('woocommerce_form_field', 'remove_label_optional_text', 10, 4);

/**
 * @desc Add html templates of access to 'Promo Passes' in 'Singular Passes'
 * @return void
 */
function add_promo_link()
{
    if (is_singular_pass()) {
        ?>
<div>
    <p><span class="sz-text-highlight">Do you know? </span>You can enjoy one FREE extra entry if you buy the promo?</p>
    <a href="<?php echo get_permalink(358)?>"><button>Take me to
            Promo!</button></a>
</div>
<?php
    }
}
add_action('woocommerce_single_product_summary', 'add_promo_link');


// require_once __DIR__ . '/coupon.php';
