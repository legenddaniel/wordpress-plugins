// Checkbox area before add_to_cart button layout and functionalities
jQuery(document).ready(function ($) {

    // Should not hard coded
    const [archeryID, airsoftID, comboID] = ['68059', '68060', '68062'];
    // const [archeryID, airsoftID, comboID] = ['291', '292', '293'];

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
            case archeryID:
                toggleField('#archery-field', '#airsoft-field', '#combo-field');
                break;
            case airsoftID:
                toggleField('#airsoft-field', '#archery-field', '#combo-field');
                break;
            case comboID:
                toggleField('#combo-field', '#airsoft-field', '#archery-field');
                break;
        }
    });

    // Change price displayed according the discount options
    $('input[name="byoe-enable"], select[name="promo-qty"]').on('change', function () {
        const $field = $(this).closest('.sz-discount-field');
        const $byoeInput = $field.find('input[name="byoe-enable"]');
        const $promoInput = $field.find('input[name="promo-enable"]');
        const $promoSelect = $field.find('select[name="promo-qty"]');

        let bdiHtml = '<span class="woocommerce-Price-currencySymbol">$</span>';
        const $qty = $('#wc_bookings_field_persons').val();
        const $price = $field.attr('data-price'); // Original base cost

        const $byoeDiscount = $byoeInput.is(':checked') && $byoeInput.val() * 1; // So far byoe-qty always 1
        const $promoDiscount = $promoSelect.val() * $promoInput.val();

        let $total = $qty * $price;
        $total = $total - $byoeDiscount - $promoDiscount;
        $total = $total > 0 ? $total : 0;

        bdiHtml += $total.toFixed(2);
        $('#booking-cost bdi').html(bdiHtml);
    })

    // Display checkboxes based on stock
    try {
        const observedNode = document.getElementById('booking-cost');
        const targetNode = document.getElementById('sz-discount-fields');
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const selects = document.querySelectorAll('select[name="promo-qty"]');
        const selectFields = document.querySelectorAll('.txtAge');

        const config = { attributes: true, childList: true };

        const mutationObserver = new MutationObserver(function (mutations, observer) {
            mutations.forEach(function (mutation) {

                // Uncheck all discount options when booking changes
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                })

                // Reset all select values to the initial
                selects.forEach(function (select) {
                    select.value = '';
                })

                // Hide all select fields
                selectFields.forEach(function (selectField) {
                    selectField.style.display = 'none';
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