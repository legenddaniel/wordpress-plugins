<?php

defined('ABSPATH') || exit;

?>

<section id="form-account">
<h3>Account Information</h3>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Email Address
        <span class="required">*</span>
        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	<label>
        Password
        <span class="required">*</span>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" required>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	<label>
        Confirm Password
        <span class="required">*</span>
        <span class="password-input">
            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="confirm-password" required>
        </span>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Security Question
        <span class="required">*</span>
        <select class="woocommerce-Input woocommerce-Input--text input-text" name="security-question" required>
            <option value="sq-maiden">What's your mother's maiden name?</option>
            <option value="sq-sport">What's your favorite sport?</option>
        </select>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Security Question Answer
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="security-answer" required />
    </label>
</p>
</section>

<?php