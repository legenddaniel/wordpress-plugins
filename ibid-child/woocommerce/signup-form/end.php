<?php 

defined('ABSPATH') || exit;

?>

<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide sz-gdpr-row">
    <input type="checkbox" name="gdpr" required />
    <span class="required">*</span>
    <?php do_action('woocommerce_register_form');?>
</div>

<p class="woocommerce-form-row form-row">
    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce');?>
    <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce');?>"><?php esc_html_e('Register', 'woocommerce');?></button>
</p>

<?php
    do_action('woocommerce_register_form_end');
?>

</form>
</div>

<?php