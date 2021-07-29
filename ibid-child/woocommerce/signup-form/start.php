<?php

defined('ABSPATH') || exit;

// Print registration notices above form
woocommerce_output_all_notices();

?>
    <div class="woocommerce">
    <form id="register" method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag');?> >

<?php do_action('woocommerce_register_form_start');?>