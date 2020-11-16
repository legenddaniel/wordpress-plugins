jQuery(document).ready(function ($) {

    // Fetch discount data for various resources
    $('#wc_bookings_field_resource').on('change', function () {
        const resource_id = this.value;
        $.post(
            my_ajax_obj.ajax_url,
            {
                _ajax_nonce: my_ajax_obj.discount_nonce,
                action: 'fetch_discount_prices',
                resource_id,
            },
            function (res) {
                const { resource, byoe_enable, price, price_off, promo_label } = res.data;

                if (resource_id !== resource) return;

                $('#sz-discount-field').attr('data-price', price);
                $('#byoe-enable').attr('data-price', price_off);
                $('#promo-enable').attr('data-price', price);
                $('#promo-enable').next('label').text(promo_label);
                $('#sz-discount-field div').slice(0, 2).toggle(byoe_enable);
            }
        ).fail(function (error) {
            console.log('Fetching discount info failed: ' + JSON.stringify(error));
        });
    })
})