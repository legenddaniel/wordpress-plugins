<?php

defined('ABSPATH') || exit;

include_once 'config.php';

class SZ_Ibid
{

    public function __construct()
    {
        add_shortcode('sz_registration_form', [$this, 'render_registration_form']);

        add_action('wp_enqueue_scripts', [$this, 'init_assets']);

        add_action('woocommerce_register_post', [$this, 'verify_card_before_registration'], 10, 3);

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
        // wp_enqueue_script(
        //     'qty',
        //     get_stylesheet_directory_uri() . '/qty.js',
        //     array(),
        //     rand(111, 9999)
        // );
    }

    /**
     * Custom registration form.
     */
    public function render_registration_form()
    {
        // For password strength check
        wp_enqueue_script('wc-password-strength-meter');

        // Parts of registration form
        wc_get_template('registration-form/start.php');
        wc_get_template('registration-form/account.php');
        wc_get_template('registration-form/contact.php');
        wc_get_template('registration-form/card.php');
        wc_get_template('registration-form/end.php');
    }

    /**
     * Verify the card info on third party payment platform before registration
     */
    public function verify_card_before_registration($username, $email, $errors)
    {
        $tax_rates = WC_Tax::get_rates();
        $rate = $tax_rates[
            array_search(TAX_LABEL, array_column($tax_rates, 'label'))
        ]['rate'];

        $to = [
            'firstName' => sanitize_text_field($_POST['first-name']),
            'lastName' => sanitize_text_field($_POST['last-name']),
            'company' => sanitize_text_field($_POST['company'] || ''),
            'address' => sanitize_text_field($_POST['address1']) . ', ' . sanitize_text_field($_POST['address2']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['province']),
            'zip' => sanitize_text_field($_POST['postcode']),
            'country' => sanitize_text_field($_POST['coountry']),
        ];

        die(var_dump($tax_rates));
        $info = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name' => AUTHORIZE_NAME,
                    'transactionKey' => AUTHORIZE_KEY,
                ],
                'refId' => '123456',
                'transactionRequest' => [
                    'transactionType' => 'authOnlyTransaction',
                    'amount' => SIGNUP_FEE * (1 + sanitize_text_field($rate) / 100),
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => sanitize_text_field($_POST['card-number']),
                            'expirationDate' => sanitize_text_field($_POST['card-expiry-year']) . '-' . sanitize_text_field($_POST['card-expiry-month']),
                            'cardCode' => sanitize_text_field($_POST['card-code']),
                        ],
                    ],
                    'lineItems' => [
                        'lineItem' => [
                            'itemId' => '1',
                            'name' => 'vase',
                            'description' => 'Cannes logo',
                            'quantity' => '18',
                            'unitPrice' => '45.00',
                        ],
                    ],
                    'tax' => [
                        'amount' => '4.26',
                        'name' => 'level2 tax name',
                        'description' => 'level2 tax',
                    ],
                    'duty' => [
                        'amount' => '8.55',
                        'name' => 'duty name',
                        'description' => 'duty description',
                    ],
                    'shipping' => [
                        'amount' => '4.26',
                        'name' => 'level2 tax name',
                        'description' => 'level2 tax',
                    ],
                    'poNumber' => '456654',
                    'customer' => [
                        'id' => '99999456654',
                    ],
                    'billTo' => $to,
                    'shipTo' => $to,
                    'customerIP' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                    'userFields' => [
                        'userField' => [
                            0 => [
                                'name' => 'MerchantDefinedFieldName1',
                                'value' => 'MerchantDefinedFieldValue1',
                            ],
                            1 => [
                                'name' => 'favorite_color',
                                'value' => 'blue',
                            ],
                        ],
                    ],
                    'processingOptions' => [
                        'isSubsequentAuth' => 'true',
                    ],
                    'subsequentAuthInformation' => [
                        'originalNetworkTransId' => '123456789NNNH',
                        'originalAuthAmount' => '45.00',
                        'reason' => 'resubmission',
                    ],
                    'authorizationIndicatorType' => [
                        'authorizationIndicator' => 'pre',
                    ],
                ],
            ],
        ];
        $curl = curl_init(AUTHORIZE_ENDPOINT);
        curl_setopt_array($curl, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($info),
        ]);
        $res = json_decode(curl_exec($curl));

        die(var_dump($res));

        curl_close($curl);

    }
}

new SZ_Ibid();
