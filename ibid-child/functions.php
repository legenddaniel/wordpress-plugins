<?php

defined('ABSPATH') || exit;

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
        wp_enqueue_style(
            'child-style',
            get_stylesheet_uri(),
            array($parenthandle),
            rand(111, 9999)
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
        wp_enqueue_script('wc-password-strength-meter');
        wc_get_template('registration-form.php');
    }

    /**
     * Verify the card info on third party payment platform before registration
     */
    public function verify_card_before_registration($username, $email, $errors)
    {
        /**
         * send cURL to authorize.net here
         */
        $errors->add( 'bad_email_domain', '<strong>ERROR</strong>: This email domain is not allowed.' );
    }
}

new SZ_Ibid();
