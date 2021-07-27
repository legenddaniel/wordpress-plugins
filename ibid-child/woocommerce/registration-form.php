<?php

defined('ABSPATH') || exit;

// Template from `form-login.php`.

?>
        <div class="woocommerce">
        <form id="register" method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag');?> >

<?php do_action('woocommerce_register_form_start');?>

<section>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Email Address
        <span class="required">*</span>
        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	<label>
        password
        <span class="required">*</span>
        <span class="password-input">
            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" required>
            <span class="show-password-input"></span>
        </span>
    </label>
</p>
    </section>
    <section>
    <h3>Credit/Debit Card Information</h3>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Card Type
        <span class="required">*</span>
        <select class="woocommerce-Input woocommerce-Input--text input-text" name="card-type" required>
            <option value="debit">Debit</option>
            <option value="visa">Credit - Visa</option>
            <option value="mastercard">Credit - MasterCard</option>
        </select>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Name On Card
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="card-name" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Card Number
        <span class="required">*</span>
        <input pattern="^\d{16}$" maxLength="16" class="woocommerce-Input woocommerce-Input--text input-text" name="card-number" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Card Expiry (mm/yy)
        <span class="required">*</span>
        <input pattern="^(0[1-9])|(1[012])$" maxLength="2" class="woocommerce-Input woocommerce-Input--text input-text" name="card-expiry-month" placeholder="mm" required />
        /
        <input pattern="^\d{2}$" maxLength="2" class="woocommerce-Input woocommerce-Input--text input-text" name="card-expiry-year" placeholder="yy" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Card Security Code
        <span class="required">*</span>
        <input pattern="^\d{3}$" maxLength="3" class="woocommerce-Input woocommerce-Input--text input-text" name="card-code" required />
    </label>
</p>
</section>

<section>
    <?php

?>
</section>

<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide sz-gdpr-row">
    <input type="checkbox" name="gdpr" required />
    <?php do_action('woocommerce_register_form');?>
    <span class="required">*</span>
</div>

<p class="woocommerce-form-row form-row">
    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce');?>
    <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce');?>"><?php esc_html_e('Register', 'woocommerce');?></button>
</p>

<?php do_action('woocommerce_register_form_end');?>

</form>
    </div>

<?php