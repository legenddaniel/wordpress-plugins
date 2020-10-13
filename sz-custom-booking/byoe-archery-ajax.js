jQuery(document).ready(function ($) {
    $('#byoe_archery').on('change', function () {
        $.post(
            my_ajax_obj.ajax_url,
            {
                _ajax_nonce: my_ajax_obj.nonce,
                action: 'apply_byoe_archery_discount',
                checked: $(this).is(':checked'),
            },
            function () {
                console.log('byoe_archery_ajax sent');
            }
        ).fail(function () {
            console.log('byoe_archery_ajax failed')
        });
    })
})