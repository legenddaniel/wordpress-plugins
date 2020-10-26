jQuery(document).ready(function ($) {

    /**
     * @desc Render select options based on the number of persons and/or passes remaining
     * @param {string} field - 'byoe' or 'promo'
     * @return {undefined}
     */
    const renderSelectOptions = function (field) {
        const numOfPersons = +$('#wc_bookings_field_persons').val();
        const currentSelect = $(`.sz-discount-field:not(.d-none) select[name="${field}-qty"]`);

        let numOfOptions;
        if (field === 'byoe') {
            numOfOptions = numOfPersons;
        }
        if (field === 'promo') {
            const numOfPromo = +currentSelect.attr('data-passes');
            numOfOptions = Math.min(numOfPersons, numOfPromo);
        }

        let selectHtml = '<option selected></option>';
        for (let i = 1; i < numOfOptions + 1; i++) {
            selectHtml += `<option>${i}</option>`;
        }
        currentSelect.html(selectHtml);
    };

    // When page initialize
    renderSelectOptions('byoe');
    // renderSelectOptions('promo');

    // When persons and/or resource type changes
    $('#wc_bookings_field_persons, #wc_bookings_field_resource').on('change', renderSelectOptions.bind(this, 'byoe'));
    // $('#wc_bookings_field_persons, #wc_bookings_field_resource').on('change', renderSelectOptions.bind(this, 'promo'));
})