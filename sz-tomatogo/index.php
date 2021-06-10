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
    private $business_area = ['Markham', 'Richmond Hill', 'Vaughan', 'North York', 'Etobicoke', 'Toronto Downtown', 'Scarborough'];

    private $store_info;

    public function __construct()
    {
        $this->store_info = $this->get_store_info();

        add_action('woocommerce_payment_complete', [$this, 'send_order']);
        add_action('woocommerce_order_status_completed', [$this, 'send_order']);

        add_action('add_meta_boxes_shop_order', [$this, 'add_tomatogo_barcode']);
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
                case 'fromCity':
                case 'toCity':
                    /**
                     *
                     *
                     *
                     * What if city is not valid?
                     */
                    if (in_array($v, $this->business_area)) {
                        $result[$k] = $v;
                    } else {
                        $result[$k] = $v;
                    }
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
     * @return array Associate array of row object
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
                option_name = 'blogname'",
            OBJECT_K
        );
        return $store_info;
    }

    /**
     * Retrieve value from store info by key
     * @param string $key - option_name
     * @return mixed Value - option_value
     */
    private function get_store_info_value($key)
    {
        if ($this->store_info) {
            $row = $this->store_info[$key];
            return $row ? sanitize_text_field($row->option_value) : '';
        }
        return '';
    }

    /**
     * Sync order to Tomato Go and get the barcode
     * @param integer $order_id - Order id
     */
    public function send_order($order_id)
    {
        if (get_post_meta($order_id, 'barcode', true)) {
            return;
        }

        $order = wc_get_order($order_id);

        $description = [];
        foreach ($order->get_items() as $k => $item) {
            $product = $item->get_product_id();
            $description[] = get_the_excerpt($product);
        }
        $description = sanitize_text_field(implode("\n", $description));

        $billing_info = array_map(function ($v) {
            return sanitize_text_field($v);
        }, $order->get_data()['billing']);

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
            'fromTel' => STORE_TEL,
            'fromEmail' => STORE_EMAIL,
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
