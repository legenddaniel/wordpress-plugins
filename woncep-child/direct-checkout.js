jQuery(document).ready(function ($) {
    var $link = $('#sz-custom-checkout');
    var href = $link.attr('href');

    $link.on('mousedown', function () {
        var id = $('input[name="variation_id"]').val() || $('input[name="add-to-cart"]').val();
        var qty = $('input[name="quantity"]').val();

        var newHref = href.replace('$ID', id).replace('$QTY', qty);

        $(this).attr('href', newHref);
    })
});