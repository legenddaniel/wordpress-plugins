<?php

defined('ABSPATH') || exit;

class Ibid_Auction
{
    private $key_verification = 'wc_authorize_net_cim_customer_profile_id';

    public function __construct()
    {
        add_shortcode('sz_signup_form', [$this, 'render_signup_form']);

        add_action('wp_enqueue_scripts', [$this, 'init_assets']);

        add_action('woocommerce_login_form_end', [$this, 'add_signup_link']);
        add_action('woocommerce_created_customer', [$this, 'verify_card_when_signup'], 10, 3);
        add_filter('wp_authenticate_user', [$this, 'verify_login'], 10, 2);

        add_action('publish_product', [$this, 'create_product_frontend'], 10, 2);

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
     * Verify the card info on third party payment platform when signup. User must be verified to log in.
     * @param int $customer_id
     * @param array $new_customer_data
     * @param bool $password_generated
     */
    public function verify_card_when_signup($customer_id, $new_customer_data, $password_generated)
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
                        'email' => sanitize_text_field($_POST['email']),
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
                    'userFields' => [
                        'userField' => [
                            [
                                'name' => 'woocommerce_id',
                                'value' => sanitize_text_field($customer_id),
                            ],
                        ],
                    ],
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

        if ($res) {
            if (strpos($res, '{') > 0) {
                $res = preg_replace('/^.*?{/', '{', $res);
            }
            $res = json_decode($res, true);
        }

        if (!$res || $res['transactionResponse']['errors']) {
            wc_add_notice(__('<strong>Error</strong>: You must verify your credit card first.'), 'error');
            wp_delete_user($customer_id);
        } else {
            update_user_meta($customer_id, $this->key_verification, $payment_profile_id);
            update_user_meta($customer_id, 'wc_authorize_net_cim_verification_id', $res['transactionResponse']['transId']);
            update_user_meta($customer_id, 'auction_point', DEFAULT_POINT);

            $billings = [
                'first_name' => sanitize_text_field($_POST['first-name']),
                'last_name' => sanitize_text_field($_POST['last-name']),
                'company' => sanitize_text_field($_POST['company'] || ''),
                'address_1' => sanitize_text_field($_POST['address1']),
                'address_2' => sanitize_text_field($_POST['address2']),
                'city' => sanitize_text_field($_POST['city']),
                'state' => sanitize_text_field($_POST['province']),
                'postcode' => sanitize_text_field($_POST['postcode']),
                'country' => sanitize_text_field($_POST['country']),
                'email' => sanitize_text_field($_POST['email']),
            ];
            foreach ($billings as $k => $v) {
                update_user_meta($customer_id, 'billing_' . $k, $v);
            }
        }
    }

    /**
     * Verify user when login. Users must have verified their credit card.
     * @param WP_User|WP_Error|null $user
     * @param string $pwd
     * @return WP_User|WP_Error|null
     */
    public function verify_login($user, $pwd)
    {
        if (!($user instanceof WP_User)) {
            return $user;
        }

        $id = $user->ID;
        $verified = get_user_meta($id, $this->key_verification, true);
        if ($verified) {
            return $user;
        }

        return new WP_Error(
            'payment_unverified',
            __('<strong>Error</strong>: You must verify your credit card first.')
        );
    }

    /**
     * Set the product as auction when creating product at frontend
     * @param int $id - Post id
     * @param WP_Post $post
     */
    public function create_product_frontend($id, $post)
    {
        if (
            check_ajax_referer('ns-apf-special-string', 'security', false) === -1 ||
            !isset($_POST['action']) ||
            sanitize_text_field($_POST['action']) !== 'save_simple_product'
        ) {
            return;
        }

        $data = $_POST['formdata'];
        if (!is_array($data)) {
            throw new Exception('Invalid Form Data!');
        }

        // Mark it as self service product
        update_post_meta($id, 'self_service', true);

        // Some other auction meta data
        if (isset($_POST['_auction_dates_from'])) {
            $date1 = new DateTime(sanitize_text_field($_POST['_auction_dates_from']));
            $date2 = new DateTime(current_time('mysql'));
            if ($date1 < $date2) {
                update_post_meta($id, '_auction_has_started', '1');
                delete_post_meta($id, '_auction_started');
                do_action('woocommerce_simple_auction_started', $id);
            } else {
                update_post_meta($id, '_auction_started', '0');
            }
        }
        if (isset($_POST['_auction_proxy'])) {
            update_post_meta($id, '_auction_proxy', stripslashes($_POST['_auction_proxy']));
        } else {
            update_post_meta($id, '_auction_proxy', '0');
        }

        // Change product type to 'auction'
        wp_remove_object_terms($id, 'simple', 'product_type');
        wp_set_object_terms($id, 'auction', 'product_type');

        // Add auction related post meta
        $auction_keys = ['_auction_item_condition', '_auction_type', '_auction_proxy', '_auction_start_price', '_auction_bid_increment', '_auction_reserved_price', '_auction_dates_from', '_auction_dates_to'];
        foreach ($auction_keys as $k) {
            $index = array_search($k, array_column($data, 'name'));
            $value = sanitize_text_field($data[$index]['value']);
            update_post_meta($id, $k, $value);
        }
    }
}
