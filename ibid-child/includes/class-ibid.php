<?php

defined('ABSPATH') || exit;

class Ibid_Auction
{
    private $key_verification = 'wc_authorize_net_cim_customer_profile_id';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'init_assets']);

        add_shortcode('sz_signup_form', [$this, 'render_signup_form']);
        add_action('woocommerce_login_form_end', [$this, 'add_signup_link']);
        add_action('woocommerce_created_customer', [$this, 'verify_card_when_signup'], 10, 3);
        add_filter('wp_authenticate_user', [$this, 'verify_login'], 10, 2);

        add_action('publish_product', [$this, 'create_product_frontend'], 10, 2);

        add_action('woocommerce_after_bid_button', [$this, 'add_max_bid_field']);
        add_action('woocommerce_after_add_to_cart_form', [$this, 'add_watchlist_button']);

        add_filter('woocommerce_simple_auctions_before_place_bid_filter', [$this, 'use_default_placebid'], 10, 2);
        add_action('woocommerce_simple_auctions_before_place_bid', [$this, 'place_bid'], 10, 3);

    }

    public function init_assets()
    {
        wp_enqueue_style(
            'parent-style',
            get_template_directory_uri() . '/style.css',
            array(),
            wp_get_theme()->parent()->get('Version')
        );

        if (is_page(SIGNUP_PAGE)) {
            wp_enqueue_script(
                'login-validation',
                get_stylesheet_directory_uri() . '/js/login.js'
            );
        }

        if ($this->is_auto_bidding_product_page()) {
            wp_enqueue_script(
                'bid-indicator',
                get_stylesheet_directory_uri() . '/js/bid-indicator.js'
            );
        }
    }

    /**
     * Check if is on single page of a auto bidding auction product
     * @return bool
     */
    private function is_auto_bidding_product_page()
    {
        return is_product() && get_post_meta(get_the_ID(), '_auction_proxy', true) === 'yes';
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
        /**
         * May add validation here
         */

        $tax_rates = WC_Tax::get_rates('');
        $rate = sanitize_text_field($tax_rates[STANDARD_RATE_ID]['rate']);
        $amount = number_format(SIGNUP_FEE * (1 + $rate / 100), 2, '.', '');

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
        $email = sanitize_text_field($_POST['email']);
        $account_type = sanitize_text_field($_POST['account-type']);

        $info = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name' => AUTHORIZE_NAME,
                    'transactionKey' => AUTHORIZE_KEY,
                ],
                // 'refId' => '123456',
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
                        'type' => strtolower($account_type) === 'business' ? 'business' : 'individual',
                        'id' => $customer_id,
                        'email' => $email,
                    ],
                    'billTo' => $to,
                    // 'shipTo' => $to,
                    // 'customerIP' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
                    // "transactionSettings" => [
                    //     "setting" => [
                    //         "settingName" => "emailCustomer",
                    //         "settingValue" => true,
                    //     ],
                    // ],
                    // 'userFields' => [
                    //     'userField' => [
                    //         [
                    //             'name' => 'woocommerce_id',
                    //             'value' => sanitize_text_field($customer_id),
                    //         ],
                    //     ],
                    // ],
                    // 'processingOptions' => [
                    //     'isFirstRecurringPayment' => false,
                    //     'isFirstSubsequentAuth' => true,
                    //     'isSubsequentAuth' => true,
                    //     'isStoredCredentials' => true,
                    // ],
                    // 'subsequentAuthInformation' => [
                    //     'originalNetworkTransId' => '123456789NNNH',
                    //     'originalAuthAmount' => $amount,
                    //     'reason' => 'resubmission',
                    // ],
                    // 'authorizationIndicatorType' => [
                    //     'authorizationIndicator' => 'final',
                    // ],
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

        // Remove BOM
        if ($res) {
            if (strpos($res, '{') > 0) {
                $res = preg_replace('/^.*?{/', '{', $res);
            }
            $res = json_decode($res, true);
        }

        $attachments = [];
        if (
            // cURL failed || payment failed || profile creation failed
            !$res ||
            $res['messages']['resultCode'] !== 'Ok' ||
            !$res['transactionResponse'] ||
            !$res['profileResponse'] ||
            $res['transactionResponse']['responseCode'] != '1' ||
            !$res['profileResponse']['customerProfileId']
        ) {
            wp_delete_user($customer_id);

            wc_add_notice(__('<strong>Error</strong>: Credit card verification failed. Please try again.'), 'error');

            if ($email) {
                $mailer = new Ibid_Auction_Email('signup_error');
                $mailer->send($email, $attachments);
            }
        } else {
            update_user_meta(
                $customer_id,
                $this->key_verification,
                sanitize_text_field($res['profileResponse']['customerProfileId'])
            );
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
                'email' => $email,
                'phone' => sanitize_text_field($_POST['phone']),
            ];
            foreach ($billings as $k => $v) {
                update_user_meta($customer_id, 'billing_' . $k, $v);
            }

            $other = [
                'first_name' => sanitize_text_field($_POST['first-name']),
                'last_name' => sanitize_text_field($_POST['last-name']),
                'account_type' => $account_type,
                'birthday' => sanitize_text_field($_POST['birthday']),
                'driver_license' => sanitize_text_field($_POST['driver-license']),
                // 'security_question' => sanitize_text_field($_POST['security-question']),
                // 'security_answer' => sanitize_text_field($_POST['security-answer']),
            ];
            foreach ($other as $k => $v) {
                update_user_meta($customer_id, $k, $v);
            }

            // Replace the default WordPress new user emails
            if ($email) {
                $mailer = new Ibid_Auction_Email('signup_ok');
                $mailer->send($email, $attachments);
                wp_send_new_user_notifications($customer_id, 'admin');
            }

            wp_signon([
                'user_login' => $email,
                'user_password' => sanitize_text_field($_POST['password']),
                'remember' => true,
            ]);
            wp_safe_redirect(wc_get_page_permalink('myaccount')) && exit;
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

        // Do not verify admin
        if (in_array('administrator', $user->roles)) {
            return $user;
        }

        $id = $user->ID;

        $freezed = get_user_meta($id, 'breach_count', true);
        if ($freezed && $freezed > BREACH_LIMIT) {
            return new WP_Error(
                'account_freezed',
                __('<strong>Error</strong>: Your account has been freezed. Please contact the store.')
            );
        }
        $verified = get_user_meta($id, $this->key_verification, true);
        if (!$verified) {
            return new WP_Error(
                'payment_unverified',
                __('<strong>Error</strong>: You must verify your credit card first.')
            );
        }

        return $user;
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
        update_post_meta($id, 'service_type', 'self_service');

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
        $auction_keys = ['_auction_item_condition', '_auction_type', '_auction_proxy', '_auction_start_price', '_auction_bid_increment', '_auction_reserved_price', '_auction_dates_from', '_auction_dates_to', '_auction_allow_faq'];
        foreach ($auction_keys as $k) {
            $index = array_search($k, array_column($data, 'name'));
            $value = sanitize_text_field($data[$index]['value']);
            update_post_meta($id, $k, $value);
        }
    }

    /**
     * Add max bid input in single product page if auto bidding enabled
     */
    public function add_max_bid_field()
    {
        if (!$this->is_auto_bidding_product_page()) {
            return;
        }

        global $product;
        $product_id = $product->get_id();

        ?>

        <label class="bid-max-label">
            <input type="checkbox" name="bid-max-enable" id="bid-max-enable" />
            Enable Auto Bidding
        </label>
        <div class="bid-max-field hide" id="bid-max-field">
        <?php if ($product->get_auction_type() == 'reverse'): ?>
            <div class="quantity buttons_added">
				<input type="button" value="+" class="plus" />
				<input type="number" name="bid_max" id="bid-max" data-auction-id="<?php echo esc_attr($product_id); ?>"  <?php if ($product->get_auction_sealed() != 'yes') {?> max="<?php echo $product->bid_value() ?>"  <?php }?> step="any" size="<?php echo strlen($product->get_curent_bid()) + 2 ?>" title="max bid"  class="input-text qty" disabled>
				<input type="button" value="-" class="minus" />
			</div>
        <?php else: ?>
            <div class="quantity buttons_added">
                <input type="button" value="+" class="plus" />
                <input type="number" name="bid_max" data-auction-id="<?php echo esc_attr($product_id); ?>" <?php if ($product->get_auction_sealed() != 'yes') {?> min="<?php echo $product->bid_value()+'1' ?>" <?php }?>  step="1" size="<?php echo strlen($product->get_curent_bid()) + 2 ?>" title="max bid" class="input-text qty" disabled>
                <input type="button" value="-" class="minus bid_max" />
            </div>
        <?php endif;?>
            <span class="bid-max-sublabel">Max Bid Limit</span>
            <div class="quantity buttons_added">
                <input type="button" value="+" class="plus" />
                <input type="number" name="bid_max_increment" data-auction-id="<?php echo esc_attr($product_id); ?>" <?php if ($product->get_auction_sealed() != 'yes') {?> min="1" <?php }?> step="1" title="bid increment" class="input-text qty" disabled>
                <input type="button" value="-" class="minus" />
            </div>
            <span class="bid-max-sublabel">Bid Increment</span>
        </div>
        <?php
}

    /**
     * Move the displaying position of watchlist button. This is originally rendered by WooCommerce Simple Auction.
     */
    public function add_watchlist_button()
    {
        global $product;

        if (!(method_exists($product, 'get_type') && $product->get_type() == 'auction')) {
            return;
        }
        $user_id = get_current_user_id();

        ?>
        <p class="wsawl-link">
            <?php if ($product->is_user_watching()): ?>
                <a href="#remove from watchlist" data-auction-id="<?php echo esc_attr($product->get_id()); ?>" class="remove-wsawl sa-watchlist-action"><?php _e('Remove from watchlist!', 'wc_simple_auctions')?></a>
            <?php else: ?>
                <a href="#add_to_watchlist" data-auction-id="<?php echo esc_attr($product->get_id()); ?>" class="add-wsawl sa-watchlist-action <?php if ($user_id == 0) {
            echo " no-action ";
        }
        ?> " title="<?=$user_id ? 'Add to watchlist!' : 'You must be logged in to use watchlist feature';?>"></a>
            <?php endif;?>
        </p>
        <!-- <p>Comment in and out to check if change applied</p> -->
        <?php
}

    /**
     * Copy of log bid function in `WC_Bid` in Woocommerce Simple Auction
     * @param int $product_id
     * @param float $bid
     * @param WP_User $current_user
     * @param int $proxy - Whether customer enabled auto bidding in single product page
     * @return int auto generated db id
     */
    private function log_bid($product_id, $bid, $current_user, $proxy = 0)
    {
        global $wpdb;
        $log_bid_id = false;

        $log_bid = $wpdb->insert($wpdb->prefix . 'simple_auction_log', array('userid' => $current_user->ID, 'auction_id' => $product_id, 'bid' => $bid, 'proxy' => $proxy, 'date' => current_time('mysql')), array('%d', '%d', '%f', '%d', '%s'));
        if ($log_bid) {
            $log_bid_id = $wpdb->insert_id;
        }
        do_action('woocommerce_simple_auctions_log_bid', $log_bid_id, $product_id, $bid, $current_user);
        return $log_bid_id;
    }

    /**
     * Whether use the default placebid functionalities or use the custom one below
     * @param WC_Product_Auction $product_data
     * @param float $bid - $_POST['bid_value']
     * @return bool - Use default if true, use custom if false
     */
    public function use_default_placebid($product_data, $bid)
    {
        return $product_data->get_auction_sealed() == 'yes' || $product_data->get_auction_type() != 'normal' || !$product_data->get_auction_proxy();
    }

    /**
     * Replace the default `placebid` function in `WC_Bid` in WooCommerce Simple Auction
     * @param int $product_id
     * @param float $bid - $_POST['bid_value']
     * @param WC_Product_Auction $product_data
     */
    public function place_bid($product_id, $bid, $product_data)
    {
        if ($this->use_default_placebid($product_data, $bid)) {
            return;
        }

        if (!is_user_logged_in()) {
            return wc_add_notice(sprintf(__('Sorry, you must be logged in to place a bid. <a href="%s" class="button">Login &rarr;</a>', 'wc_simple_auctions'), get_permalink(wc_get_page_id('myaccount'))), 'error');
        }

        if ($product_data->is_closed()) {
            return wc_add_notice(sprintf(__('Sorry, auction for &quot;%s&quot; is finished', 'wc_simple_auctions'), $product_data->get_title()), 'error');
        }
        if (!$product_data->is_started()) {
            return wc_add_notice(sprintf(__('Sorry, the auction for &quot;%s&quot; has not started yet', 'wc_simple_auctions'), $product_data->get_title()), 'error');
        }
        if (!$product_data->is_in_stock()) {
            return wc_add_notice(sprintf(__('You cannot place a bid for &quot;%s&quot; because the product is out of stock.', 'wc_simple_auctions'), $product_data->get_title()), 'error');
        }

        if ($bid <= 0) {
            return wc_add_notice(__('Bid must be greater than 0!', 'wc_simple_auctions'), 'error');
        }

        // Throw if the bid is not higher than current bid and `Allow on proxy auction change to smaller max bid value` is unchecked in WooCommerce->Settings->Auctions
        $current_bid = $product_data->get_curent_bid(); // Notice the method name is 'curent' instead of 'current'
        if ($bid <= $current_bid && get_option('simple_auctions_smaller_max_bid', 'no') === 'no') {
            return wc_add_notice(__('New bid must be greater than old bid!', 'wc_simple_auctions'), 'error');
        }

        // Throw if bid exceeds the limit which is configured `Max bid amount` in WooCommerce->Settings->Auctions
        $maximum_bid_amount = get_option('simple_auctions_max_bid_amount', '999999999999.99');
        $maximum_bid_amount = $maximum_bid_amount > 0 ? $maximum_bid_amount : '999999999999.99';
        if ($bid >= $maximum_bid_amount) {
            return wc_add_notice(sprintf(__('Bid must be lower than %s !', 'wc_simple_auctions'), wc_price($maximum_bid_amount)), 'error');
        }

        // Throw if the customized max values are invalid
        $auto_enable = isset($_POST['bid-max-enable']);
        if ($auto_enable) {
            $increment = sanitize_text_field($_POST['bid_max_increment']);
            if ($increment <= 0) {
                return wc_add_notice(__('Invalid max bid increment value!', 'wc_simple_auctions'), 'error');
            }
            $max = sanitize_text_field($_POST['bid_max']);
            if ($max < $bid + $increment) {
                return wc_add_notice(sprintf(__('Max bid must be at least %s for auto bidding', 'wc_simple_auctions'), wc_price($bid + $increment)), 'error');
            }
        }
        // Use default as max if auto bidding not enabled for further convenience
        $max = isset($max) ? $max : $bid;
        $increment = isset($increment) ? $increment : '0';

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $current_bider = $product_data->get_auction_current_bider();
        $current_max = $product_data->get_auction_max_bid();

        if ($user_id == $current_bider) {
            if (get_option('simple_auctions_curent_bidder_can_bid') === 'yes' && $auto_enable && $max > $current_max) {
                // If the highest bider is bidding and is trying to enable and update bid max, and `Allow highest bidder to outbid himself` is checked in WooCommerce->Settings->Auctions, update the max bid and max increment.
                $message = sprintf(__('Successfully updated your max bid for &quot;%s&quot; to %s!', 'wc_simple_auctions'), $product_data->get_title(), wc_price($max));

                update_post_meta($product_id, '_auction_max_bid', $max);
                update_post_meta($product_id, '_auction_max_bid_increment', $increment);
            } else {
                // Return if the highest bider is bidding but not updating the current max bid or `Allow highest bidder to outbid himself` is unchecked in WooCommerce->Settings->Auctions. Skip the `woocommerce_simple_auctions_place_bid` action below.
                return wc_add_notice(__('No need to bid. Your bid is winning! ', 'wc_simple_auctions'));
            }
        } else {
            if ($bid > $current_max) {
                // If another bider bids higher than current max bid, renew the bid info and outbid the current bider. If it's the first bid, create all info.
                $new_bid = $bid;
                $new_max = $max;
                $new_user = $user_id;
                $out_user = $current_bider;
                $message = sprintf(__('Successfully placed a bid for &quot;%s&quot;!', 'wc_simple_auctions'), $product_data->get_title());
                if ($auto_enable) {
                    $message .= sprintf(__('&nbsp;Your max bid is %s.', 'wc_simple_auctions'), wc_price($max));
                }
            } else {
                if ($max > $current_max) {
                    // If another bider bids not higher than current max bid but max bid higher than current max bid, renew the bid info and outbid the current bider. New bid is no greater than new max.
                    $new_bid = min($max, $current_max + $increment);
                    $new_max = $max;
                    $new_user = $user_id;
                    $out_user = $current_bider;
                    $message = $current_bider ?
                    sprintf(__('Successfully placed a bid for &quot;%s&quot; (originally %s)! Your max bid is %s.', 'wc_simple_auctions'), $product_data->get_title(), wc_price($bid), wc_price($max)) :
                    sprintf(__('Successfully placed a bid for &quot;%s&quot;! Your max bid is %s.', 'wc_simple_auctions'), $product_data->get_title(), wc_price($max));
                } else {
                    // If another bider bids not higher than either current max bid or max bid, renew the bid info and outbid the new bider. New bid is no greater than new max.
                    $current_increment = get_post_meta($product_id, '_auction_max_bid_increment', true);
                    $new_bid = min($current_max, $max + $current_increment);
                    $new_max = $current_max;
                    $new_user = $current_bider;
                    $out_user = $user_id;
                    $message = sprintf(__('Your bid was successful but you\'ve been outbid for &quot;%s&quot;!', 'wc_simple_auctions'), $product_data->get_title());
                }
            }

            $new_user != $current_bider && update_post_meta($product_id, '_auction_max_current_bider', $new_user);
            $new_user != $current_bider && update_post_meta($product_id, '_auction_current_bider', $new_user);
            $new_bid != $current_bid && update_post_meta($product_id, '_auction_current_bid', $new_bid);
            $new_max != $current_max && update_post_meta($product_id, '_auction_max_bid', $new_max);

            // As long as the new bider can bid, add one new bid record, regardless of auto bidding.
            update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));

            // 2 logs if outbidding the new bider or no current bider.
            if ($new_user == $current_bider) {
                $log_id = $this->log_bid($product_id, $bid, $current_user);
                $log_id = $this->log_bid($product_id, $new_bid, new WP_User($current_bider));
            } else {
                $log_id = $this->log_bid($product_id, $new_bid, $current_user);
            }

            // No outbid for the first bid
            $out_user && do_action('woocommerce_simple_auctions_outbid', array('product_id' => $product_id, 'outbiddeduser_id' => $out_user, 'log_id' => $log_id, 'auction_max_bid' => $new_max, 'auction_max_current_bider' => $new_user));

            do_action('woocommerce_simple_auctions_place_bid', array('product_id' => $product_id, 'is_proxy_bid' => false, 'log_id' => isset($log_id) ? $log_id : null));
        }

        // Replace `woocommerce__simple_auctions_place_bid_message`
        wc_add_notice($message);
    }

}
