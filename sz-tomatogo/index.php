<?php
/**
 * Plugin Name:       Tomato Go
 * Description:       Sync orders to Tomato Go
 * Version:           1.0.0
 * Author:            Daniel Siyuan Zuo
 * Text Domain:       sz-tomatogo
 */

defined('WPINC') || die;

include_once 'config.php';

class SZ_TomatoGo
{

    // From Tomato Go /lib/config.js
    private $business_area = ['Markham', 'Richmond Hill', 'Vaughan', 'Thornhill', 'North York', 'Toronto Downtown', 'Scarborough', 'Etobicoke', 'Mississauga', 'Oakville', 'Burlington', 'Hamilton', 'Stouffville', 'King City', 'Aurora', 'Newmarket'];

    private $store_info;

    public function __construct()
    {
        $this->store_info = $this->get_store_info();

        add_action('woocommerce_before_checkout_process', [$this, 'check_city']);

        add_action('woocommerce_payment_complete', [$this, 'send_order']);
        add_action('woocommerce_order_status_completed', [$this, 'send_order']);

        add_action('add_meta_boxes_shop_order', [$this, 'add_tomatogo_barcode']);
    }

    /**
     * Check if the city input is within business area
     */
    public function check_city()
    {
        if (!in_array(ucwords(sanitize_text_field($_POST['billing_city'])), $this->business_area)) {
            throw new Exception(__('Your city is not within our business area', 'woocommerce'));
        }
    }

    /**
     * Data formatter. Rules are from Tomato Go.
     * @param array $data Raw data
     * @return array Filtered data
     */
    private function format_fields($data)
    {
        if (!is_array($data)) {
            throw new Exception('Data must be an array');
        }

        $result = [];

        foreach ($data as $k => $v) {
            switch ($k) {
                case 'from':
                case 'to':
                    $result[$k] = substr($v, 0, 30);
                    break;
                case 'fromAddress1':
                case 'fromAddress2':
                case 'toAddress1':
                case 'toAddress2':
                case 'note':
                    $result[$k] = substr($v, 0, 50);
                    break;
                case 'fromPostCode':
                case 'toPostCode':
                    $result[$k] = str_replace(' ', '', $v);
                    break;
                default:
                    $result[$k] = $v;
                    break;
            }
        }

        return $result;
    }

    /**
     * Get Store info
     * @return array Associate array of key-value
     */
    private function get_store_info()
    {
        global $wpdb;
        $store_info = $wpdb->get_results(
            "SELECT option_name, option_value
            FROM {$wpdb->prefix}options
            WHERE
                option_name = 'woocommerce_store_address' OR
                option_name = 'woocommerce_store_address_2' OR
                option_name = 'woocommerce_store_city' OR
                option_name = 'woocommerce_store_postcode' OR
                option_name = 'blogname' OR
                option_name = 'foodsto_options'",
            OBJECT_K
        );

        $result = [];
        foreach ($store_info as $k => $info) {
            $value = $info ? $info->option_value : '';
            if ($k === 'foodsto_options') {
                $value = unserialize($value);
                $result['email'] = sanitize_text_field($value['email']);
                $result['phone'] = sanitize_text_field($value['phone']);
            } else {
                $result[$k] = sanitize_text_field($value);
            }
        }

        return $result;
    }

    /**
     * Retrieve value from store info by key
     * @param string $key
     * @return mixed
     */
    private function get_store_info_value($key)
    {
        $info = $this->store_info;
        if ($info) {
            $result = $info[$key];
            return $result ?: '';
        }
        return '';
    }

    /**
     * Sync order to Tomato Go and get the barcode
     * @param integer $order_id - Order id
     */
    public function send_order($order_id)
    {
        // Return if barcode exists
        if (get_post_meta($order_id, 'barcode', true)) {
            return;
        }

        $order = wc_get_order($order_id);

        // Return if the city is out of area
        $billing_info = $order->get_data()['billing'];
        if (!in_array(ucwords(sanitize_text_field($billing_info['city'])), $this->business_area)) {
            return;
        }

        $billing_info = array_map(function ($v) {
            return sanitize_text_field($v);
        }, $billing_info);

        // The short description of order items
        $description = [];
        foreach ($order->get_items() as $k => $item) {
            $product = $item->get_product_id();
            $description[] = get_the_excerpt($product);
        }
        $description = sanitize_text_field(implode("\n", $description));

        $info = $this->format_fields([
            'createdBy' => null,
            'type' => 'package',
            'status' => 'in_warehouse',
            'collectType' => 'pickup',
            'collectedBy' => null,
            'description' => $description,
            'note' => $order->get_customer_note(),
            'from' => $this->get_store_info_value('blogname'),
            'fromAddress1' => $this->get_store_info_value('woocommerce_store_address'),
            'fromAddress2' => $this->get_store_info_value('woocommerce_store_address_2'),
            'fromCity' => $this->get_store_info_value('woocommerce_store_city'),
            'fromPostCode' => $this->get_store_info_value('woocommerce_store_postcode'),
            'fromTel' => $this->get_store_info_value('phone'),
            'fromEmail' => $this->get_store_info_value('email'),
            'to' => $billing_info['first_name'] . ' ' . $billing_info['last_name'],
            'toAddress1' => $billing_info['address_1'],
            'toAddress2' => $billing_info['address_2'],
            'toCity' => $billing_info['city'],
            'toPostCode' => $billing_info['postcode'],
            'toTel' => $billing_info['phone'],
            'toEmail' => $billing_info['email'],
            'verified' => true,
            'tomatoProduceId' => $order_id,
        ]);

        $curl = curl_init(TOMATOGO_URL);
        curl_setopt_array($curl, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($info),
        ]);
        $res = json_decode(curl_exec($curl));

        curl_close($curl);

        $barcode = sanitize_text_field($res->barcode);
        if ($barcode) {
            update_post_meta($order_id, 'barcode', $barcode);
        }
    }

    /**
     * Template of the Tomato Go meta box
     * @param WP_Post $post
     */
    public function create_tomatogo_barcode_field($post)
    {
        $id = $post->ID;
        $barcode = sanitize_text_field(get_post_meta($id, 'barcode', true));

        echo '<p>' . $barcode . '</p>';
    }

    /**
     * Display the Tomato Go template
     * @param WP_Post $post
     */
    public function add_tomatogo_barcode($post)
    {
        if ($post->post_type !== 'shop_order') {
            return;
        }

        add_meta_box(
            'sz-tomatogo-barcode',
            __('Tomato Go Barcode', 'woocommerce'),
            [$this, 'create_tomatogo_barcode_field'],
            'shop_order',
            'side',
            'high'
        );
    }
}

new SZ_TomatoGo();
