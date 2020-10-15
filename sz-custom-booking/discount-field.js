// Checkbox area before add_to_cart button layout and functionalities
jQuery(document).ready(function ($) {

    /**
     * @desc Toggle classes of the field
     * @param {string} fieldShowed - Field being displayed
     * @param {...string} fieldsHidden - Fields being hidden
     * @return {undefined}
     */
    const toggleField = function (fieldShowed, ...fieldsHidden) {
        $(fieldShowed).attr('class', 'sz-discount-field');
        fieldsHidden.forEach(function (field) {
            $(field).attr('class', 'sz-discount-field d-none');
        });
    };

    // Add an anchor to the booking cost div as a ref
    $('.wc-bookings-booking-cost').eq(0).attr('id', 'booking-cost');

    // Display checkboxes based on types
    $('#wc_bookings_field_resource').on('change', function () {
        switch ($(this).val()) {
            case '291':
                toggleField('#archery-field', '#airsoft-field', '#combo-field');
                break;
            case '292':
                toggleField('#airsoft-field', '#archery-field', '#combo-field');
                break;
            case '293':
                toggleField('#combo-field', '#airsoft-field', '#archery-field');
                break;
        }
    });

    // Change price displayed according the discount options
    $('#sz-discount-fields input').on('change', function () {
        let bdiHtml = '<span class="woocommerce-Price-currencySymbol">$</span>';
        const $qty = $('#wc_bookings_field_persons').val();
        const $price = $(this).closest('.sz-discount-field').attr('data-price');

        // Apply the discount with the lowest cost
        let $discounted_price = $price;
        $('#sz-discount-fields input').each(function () {
            if (
                $(this).is(':checked') &&
                +$(this).val() < +$discounted_price
            ) {
                $discounted_price = $(this).val();
            }
        })

        bdiHtml += ($discounted_price * $qty).toFixed(2);
        $('#booking-cost bdi').html(bdiHtml);
    })

    // Display checkboxes based on stock
    try {
        const observedNode = document.getElementById('booking-cost');
        const targetNode = document.getElementById('sz-discount-fields');
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');

        const config = { attributes: true, childList: true };

        const mutationObserver = new MutationObserver(function (mutations, observer) {
            mutations.forEach(function (mutation) {

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