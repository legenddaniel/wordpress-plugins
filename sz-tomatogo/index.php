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

    private $url = 'https://578b5fc649f88d.localhost.run/api/v0/order';

    public function __construct()
    {
        // add_action('woocommerce_payment_complete', array($this, 'send_order'));
        add_action('woocommerce_order_status_completed', array($this, 'send_order'));
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

        $billing_info = $order->data['billing'];
        $info = [
            // 'paymentId' => '?',
            'createdBy' => '?',
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
            'from' => '?',
            'fromAddress1' => '?',
            'fromAddress2' => '?',
            'fromCity' => '?',
            'fromPostCode' => '?',
            'fromTel' => '?',
            'fromEmail' => '?',
            'to' => $this->format_general_str($billing_info['first_name'] . ' ' . $billing_info['last_name'], 30),
            'toAddress1' => $this->format_general_str($billing_info['address_1'], 50),
            'toAddress2' => $this->format_general_str($billing_info['address_2'], 50),
            'toCity' => $this->format_city($billing_info['city']),
            'toPostCode' => $this->format_postcode($billing_info['postcode']),
            'toTel' => $billing_info['phone'],
            'toEmail' => $billing_info['email'],
            // 'verified': false,
        ];

        $curl = curl_init($this->url);
        curl_setopt_array($curl, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($info),
        ]);
        $res = curl_exec($curl);

        die(var_dump($res));

        curl_close($curl);
    }
}

new SZ_TomatoGo();
