<?php

defined('ABSPATH') || exit;

echo 'aaa';

?>
    <div class="woocommerce">
    <?php do_action( 'woocommerce_before_customer_login_form' ); ?>
    <form id="register" method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag');?> >

<?php do_action('woocommerce_register_form_start');?>