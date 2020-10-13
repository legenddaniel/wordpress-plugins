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
                toggleField('#byoe_archery_field', true);
                toggleField('#byoe_combo_field', false);
                break;
            case '292':
                toggleField('#byoe_archery_field', false);
                toggleField('#byoe_combo_field', false);
                break;
            case '293':
                toggleField('#byoe_archery_field', false);
                toggleField('#byoe_combo_field', true);
                break;
        }
    });

    // Display checkboxes based on stock
    try {
        var observedNode = document.getElementById('booking-cost');
        var targetNode = document.getElementById('sz-discount-fields');

        var config = { attributes: true, childList: true };
        var mutationObserver = new MutationObserver(function (mutationsList, observer) {
            mutationsList.forEach(function (mutation) {

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