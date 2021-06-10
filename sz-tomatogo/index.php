<?php
/**
 * Plugin Name:       Tomato Go
 * Description:       Sync orders to Tomato Go
 * Version:           1.0.0
 * Author:            Daniel Siyuan Zuo
 * Text Domain:       sz-tomatogo
 */

defined('WPINC') || die;

class SZ_TomatoGo
{

    // From Tomato Go /lib/config.js
    private $business_area = ['Markham', 'Richmond Hill', 'Vaughan', 'North York', 'Etobicoke', 'Toronto Downtown', 'Scarborough'];

    private $url = 'https://7ee22ba5d2a651.localhost.run/api/v0/order';

    public function __construct()
    {
        add_action('woocommerce_payment_complete', [$this, 'send_order']);
        add_action('woocommerce_order_status_completed', [$this, 'send_order']);

        add_action('add_meta_boxes_shop_order', [$this, 'add_tomatogo_barcode']);
    }

    /**
     * Format some general string info as per Tomato Go rules
     * @param string $str - String to validate
     * @param int $l - Max string length
     * @return string Formatted string
     */
    private function format_general_str($str, $l)
    {
        return substr($str, 0, $l);
    }

    /**
     * Check if the city is in business area
     * @param string $city
     * @return string City
     */
    private function format_city($city)
    {
        /**
         *
         *
         *
         * Need validation
         */
        if (in_array($city, $this->business_area)) {
            return $city;
        }
        return $city;
    }

    /**
     * Remove whitespace in the post code
     * @param string $pc - Postcode
     * @return string Formatted post code
     */
    private function format_postcode($pc)
    {
        return str_replace(' ', '', $pc);
    }

    /**
     * @param integer $order_id - Order id
     */
    public function send_order($order_id)
    {
        $order = wc_get_order($order_id);

        $billing_info = array_map(function ($v) {
            return sanitize_text_field($v);
        }, $order->data['billing']);

        $info = [
            // 'paymentId' => '?',
            'createdBy' => null,
            'type' => 'package',
            'status' => 'collecting',
            // 'statusChangedAt' => '?',
            'collectType' => 'pickup',
            // 'collectedBy' => '?',
            // 'deliveredBy' => '?',
            'dimensions' => '1*1*1',
            'weight' => 1,
            'note' => $this->format_general_str($order->get_customer_note(), 50),
            // 'subtotal' => $order->get_subtotal(),
            // 'tax' => $order->get_total_tax(),
            // 'total' => $order->get_total(),
            'from' => 'tomatoproduce',
            'fromAddress1' => '151 Esna Park Dr',
            'fromAddress2' => '',
            'fromCity' => 'Markham',
            'fromPostCode' => 'L3R3B1',
            'fromTel' => '9056043088',
            'fromEmail' => 'daniel@itcg.ca',
            'to' => $this->format_general_str($billing_info['first_name'] . ' ' . $billing_info['last_name'], 30),
            'toAddress1' => $this->format_general_str($billing_info['address_1'], 50),
            'toAddress2' => $this->format_general_str($billing_info['address_2'], 50),
            'toCity' => $this->format_city($billing_info['city']),
            'toPostCode' => $this->format_postcode($billing_info['postcode']),
            'toTel' => $billing_info['phone'],
            'toEmail' => $billing_info['email'],
            'verified' => true,
        ];

        $curl = curl_init($this->url);
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
