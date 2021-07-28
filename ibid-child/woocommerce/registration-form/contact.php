<?php

defined('ABSPATH') || exit;

?>

<section id="form-contact">
<h3>Contact Information</h3>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        First Name
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="first-name" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Last Name
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="last-name" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Company
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="company" />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Address Line 1
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="address1" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Address Line 2
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="address2" />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        City
        <span class="required">*</span>
        <input class="woocommerce-Input woocommerce-Input--text input-text" name="city" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Province
        <span class="required">*</span>
        <select class="woocommerce-Input woocommerce-Input--text input-text" name="province" required>
            <option value="MN">MN</option>
        </select>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Country
        <span class="required">*</span>
        <select class="woocommerce-Input woocommerce-Input--text input-text" name="country" required>
            <option value="CA">Canada</option>
        </select>
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Postal Code
        <span class="required">*</span>
        <input pattern="^[a-zA-Z]\d[a-zA-Z]\s?\d[s-zA-Z]\d$" class="woocommerce-Input woocommerce-Input--text input-text" name="postcode" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Birthday (mm/dd/yyyy)
        <span class="required">*</span>
        <input pattern="^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/(19|20)\d{2}$" class="woocommerce-Input woocommerce-Input--text input-text" name="birthday" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Phone Number
        <span class="required">*</span>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="phone" required />
    </label>
</p>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label>
        Driver's License
        <span class="required">*</span>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="driver-license" required />
    </label>
</p>
</section>

<?php