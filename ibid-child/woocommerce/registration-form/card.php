<?php

defined('ABSPATH') || exit;

?>
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

<?php