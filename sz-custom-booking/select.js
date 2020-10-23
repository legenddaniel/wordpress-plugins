jQuery(document).ready(function ($) {

    /**
     * @desc Render select options based on the number of persons and passes remaining
     * @return {undefined}
     */
    const renderSelectOptions = function () {
        const numOfPersons = +$('#wc_bookings_field_persons').val();
        const currentSelect = $('.sz-discount-field:not(.d-none) select[name="promo-qty"]');
        const numOfPromo = +currentSelect.attr('data-passes');
        const numOfOptions = Math.min(numOfPersons, numOfPromo);

        let selectHtml = '<option selected></option>';
        for (let i = 1; i < numOfOptions + 1; i++) {
            selectHtml += `<option>${i}</option>`;
        }
        currentSelect.html(selectHtml);
    };

    // When page initialize
    renderSelectOptions();

    // When persons or resource type changes
    $('#wc_bookings_field_persons, #wc_bookings_field_resource').on('change', renderSelectOptions);
})