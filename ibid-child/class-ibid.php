<?php

defined('ABSPATH') || exit;

class Ibid_Auction
{

    public function __construct()
    {
        add_shortcode('sz_signup_form', [$this, 'render_signup_form']);

        add_action('wp_enqueue_scripts', [$this, 'init_assets']);

        add_action('woocommerce_login_form_end', [$this, 'add_signup_link']);
        add_action('woocommerce_register_post', [$this, 'verify_card_before_signup'], 10, 3);

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
     * Display the link to signup in login form
     */
    public function add_signup_link()
    {
        echo '<p class="sz-signup-link">Not a member? <a href="/' . SIGNUP_PAGE . '"><strong>Become a member NOW!</strong></a></p>';
    }

    /**
     * Custom signup form.
     */
    public function render_signup_form()
    {
        // For password strength check
        wp_enqueue_script('wc-password-strength-meter');

        // Parts of signup form
        wc_get_template('signup-form/start.php');
        wc_get_template('signup-form/account.php');
        wc_get_template('signup-form/contact.php');
        wc_get_template('signup-form/card.php');
        wc_get_template('signup-form/end.php');
    }

    /**
     * Verify the card info on third party payment platform before signup
     * @param string $username
     * @param string $email
     * @param WP_Error $errors
     */
    public function verify_card_before_signup($username, $email, $errors)
    {
        $payment_profile_id = time();

        $tax_rates = WC_Tax::get_rates();
        $rate = sanitize_text_field(
            $tax_rates[
                array_search(TAX_LABEL, array_column($tax_rates, 'label')) + 1
            ]['rate']
        );
        $amount = SIGNUP_FEE * (1 + $rate / 100);

        $to = [
            'firstName' => sanitize_text_field($_POST['first-name']),
            'lastName' => sanitize_text_field($_POST['last-name']),
            'company' => sanitize_text_field($_POST['company'] || ''),
            'address' => sanitize_text_field($_POST['address1']) . ', ' . sanitize_text_field($_POST['address2']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['province']),
            'zip' => sanitize_text_field($_POST['postcode']),
            'country' => sanitize_text_field($_POST['country']),
        ];

        $expiry = '20' . sanitize_text_field($_POST['card-expiry-year']) . '-' . sanitize_text_field($_POST['card-expiry-month']);

        $info = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name' => AUTHORIZE_NAME,
                    'transactionKey' => AUTHORIZE_KEY,
                ],
                'refId' => '123456',
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount' => $amount,
                    'currencyCode' => 'CAD',
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => sanitize_text_field($_POST['card-number']),
                            'expirationDate' => $expiry,
                            'cardCode' => sanitize_text_field($_POST['card-code']),
                        ],
                    ],
                    'profile' => [
                        'createProfile' => true,
                    ],
                    // 'poNumber' => '456654',
                    'customer' => [
                        // 'type' => 'individual',
                        'id' => $payment_profile_id,
                        'email' => $email,
                    ],
                    'billTo' => $to,
                    'shipTo' => $to,
                    'customerIP' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                    "transactionSettings" => [
                        "setting" => [
                            "settingName" => "emailCustomer",
                            "settingValue" => true,
                        ],
                    ],
                    // 'userFields' => [
                    //     'userField' => [
                    //         [
                    //             'name' => 'MerchantDefinedFieldName1',
                    //             'value' => 'MerchantDefinedFieldValue1',
                    //         ],
                    //         [
                    //             'name' => 'favorite_color',
                    //             'value' => 'blue',
                    //         ],
                    //     ],
                    // ],
                    'processingOptions' => [
                        'isFirstRecurringPayment' => false,
                        'isFirstSubsequentAuth' => true,
                        'isSubsequentAuth' => true,
                        'isStoredCredentials' => true,
                    ],
                    'subsequentAuthInformation' => [
                        'originalNetworkTransId' => '123456789NNNH',
                        'originalAuthAmount' => $amount,
                        'reason' => 'resubmission',
                    ],
                    'authorizationIndicatorType' => [
                        'authorizationIndicator' => 'final',
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
        $res = curl_exec($curl);
        curl_close($curl);

        if (strpos($res, '{') > 0) {
            $res = preg_replace('/^.*?{/', '{', $res);
        }
        $res = json_decode($res, true);

        if ($error = $res['transactionResponse']['errors']) {
            $errors->add('payment_error', sanitize_text_field($error[0]['errorText']));
        } else {
            // update_post_meta($user_id?????, 'wc_authorize_net_cim_customer_profile_id', $payment_profile_id);
            // update_post_meta($user_id?????, 'sz_wc_authorize_net_cim_transaction_id', $res['transactionResponse']['transId']);
        }
    }
}