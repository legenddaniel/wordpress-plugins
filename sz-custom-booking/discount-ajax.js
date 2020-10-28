jQuery(document).ready(function ($) {

    // $.post(
    //     my_ajax_obj.ajax_url,
    //     {
    //         _ajax_nonce: my_ajax_obj.vip_nonce,
    //         action: 'fetch_discount_prices',
    //         resource_id,
    //     },
    //     res => { // Must be arrow function
    //         const { resource, price, price_off } = res.data;

    //         if (resource_id !== resource) return;

    //         $('#sz-discount-field').attr('data-price', price);
    //         $('#byoe-enable').attr('data-price', price_off);
    //         $('#promo-enable').attr('data-price', price);
    //     }
    // ).fail(function (error) {
    //     console.log('Fetching discount info failed: ' + error);
    // });


    // Fetch discount data for various resources
    $('#wc_bookings_field_resource').on('change', function () {
        const resource_id = this.value;
        $.post(
            my_ajax_obj.ajax_url,
            {
                _ajax_nonce: my_ajax_obj.nonce,
                action: 'fetch_discount_prices',
                resource_id,
            },
            res => { // Must be arrow function
                const { resource, price, price_off } = res.data;

                if (resource_id !== resource) return;

                $('#sz-discount-field').attr('data-price', price);
                $('#byoe-enable').attr('data-price', price_off);
                $('#promo-enable').attr('data-price', price);
            }
        ).fail(function (error) {
            console.log('Fetching discount info failed: ' + error);
        });
    })
})