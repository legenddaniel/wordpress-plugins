jQuery(document).ready(function ($) {
    $('#byoe_archery').on('change', function () {
        var $that = $(this);
        var $checked = $that.is(':checked');
        var bdiHtml = '<span class="woocommerce-Price-currencySymbol">$</span>';
        $.post(
            my_ajax_obj.ajax_url,
            {
                _ajax_nonce: my_ajax_obj.nonce,
                action: 'apply_byoe_archery_discount',
                checked: $checked,
            },
            function () {
                // $('#booking-cost bdi').html(
                //     $checked ?
                //         bdiHtml += $that.val() :
                //         bdiHtml += $('#byoe_archery_hidden').val()
                // );
            }
        ).fail(function () {
            console.log('byoe_archery_ajax failed');
        });
    })
})