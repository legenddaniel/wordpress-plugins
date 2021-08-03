jQuery(document).ready(function ($) {

    // For now only archery has promo
    var archery = 70541;

    /**
     * @desc Fetch discount data for various resources
     * @param {interface} e - Event
     * @return {undefined}
     */
    var getDiscountPrices = function getDiscountPrices(e) {
        var resource_id = e.currentTarget.value;
        $.post(
            my_ajax_obj.ajax_url,
            {
                _ajax_nonce: my_ajax_obj.discount_nonce,
                action: 'fetch_discount_prices',
                resource_id: resource_id
            }, function (res) {
                var _res$data = res.data,
                    resource = _res$data.resource,
                    byoe_enable = _res$data.byoe_enable,
                    price = _res$data.price,
                    price_off = _res$data.price_off,
                    promo_label = _res$data.promo_label,
                    has_promo = _res$data.has_promo;

                if (resource_id !== resource) return;

                $('#promo-enable').prop('disabled', !has_promo);
                $('#promo-enable').next('label').text(promo_label);
                $('#promo-enable-field').toggle(resource == archery);
                $('#byoe-enable-field').toggle(byoe_enable);
                $('#sz-discount-field, #promo-enable, #guest-enable').attr('data-price', price);
                $('#byoe-enable').attr('data-price', price_off);

            }).fail(function (error) {
                console.log('Fetching discount info failed: ' + JSON.stringify(error));
            });
    };

    $('#sz-resources').on('change', getDiscountPrices);
});