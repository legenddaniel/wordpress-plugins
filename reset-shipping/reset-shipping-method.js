jQuery(document).ready(function ($) {
    $('.woocommerce-billing-fields__field-wrapper').on(
        'keydown',
        '#billing_postcode, #billing_address_1, #billing_address_2, #billing_city, #select2-billing_state-container, #billing_postcode',
        function () {
            $('#shipping_method_0_local_pickup127').prop('checked', true);
        })
})