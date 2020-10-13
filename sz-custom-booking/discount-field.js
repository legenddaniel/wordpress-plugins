// Checkbox area before add_to_cart button layout and functionalities
jQuery(document).ready(function ($) {

    /**
     * @desc Toggle classes of the field
     * @param string field
     * @param bool display
     * @return void
     */
    var toggleField = function (field, display) {
        $(field).attr('class', display ? 'sz-discount-field' : 'sz-discount-field d-none');
    };

    // Add an anchor to the booking cost div as a ref
    $('.wc-bookings-booking-cost').eq(0).attr('id', 'booking-cost');

    // Display checkboxes based on types
    $('#wc_bookings_field_resource').on('change', function () {
        switch ($(this).val()) {
            case '291':
                toggleField('#byoe-archery-field', true);
                toggleField('#byoe-combo-field', false);
                break;
            case '292':
                toggleField('#byoe-archery-field', false);
                toggleField('#byoe-combo-field', false);
                break;
            case '293':
                toggleField('#byoe-archery-field', false);
                toggleField('#byoe-combo-field', true);
                break;
        }
    });

    // Change price displayed according to BYOE
    jQuery(document).ready(function ($) {
        $('#byoe-archery, #byoe-combo').on('change', function () {
            var $price = $(this).val();
            var $discountedPrice = $(this).attr('data-price');
            var $qty = $('#wc_bookings_field_persons').val();
            var $checked = $(this).is(':checked');
            var bdiHtml = '<span class="woocommerce-Price-currencySymbol">$</span>';

            $('#booking-cost bdi').html(
                bdiHtml +=
                $checked ?
                    ($price * $qty).toFixed(2) :
                    ($discountedPrice * $qty).toFixed(2)
            );
        })
    })

    // Display checkboxes based on stock
    try {
        var observedNode = document.getElementById('booking-cost');
        var targetNode = document.getElementById('sz-discount-fields');
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');

        var config = { attributes: true, childList: true };

        var mutationObserver = new MutationObserver(function (mutationsList, observer) {
            mutationsList.forEach(function (mutation) {

                // Uncheck all discount options when booking changes
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                })

                // Observe booking cost div display change
                if (mutation.type === 'attributes') {
                    targetNode.className =
                        mutation.target.style.getPropertyValue('display') === 'none' ?
                            'sz-discount-fields d-none' :
                            'sz-discount-fields';
                    return;
                }

                // Observe booking validity
                if (mutation.type === 'childList') {
                    targetNode.className =
                        mutation.addedNodes.length === 2 ?
                            'sz-discount-fields' :
                            'sz-discount-fields d-none';
                    return;
                }
            });
        });

        mutationObserver.observe(observedNode, config);
    } catch (error) {
        console.log(error);
    }

})