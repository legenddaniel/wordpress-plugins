jQuery(document).ready(function ($) {
    /**
     * @desc Render select options based on the number of persons and/or passes remaining
     * @param {string} field - 'byoe' or 'promo'
     * @return {undefined}
     */
    var renderSelectOptions = function renderSelectOptions(field) {
        var $numOfPersons = +$('#wc-bookings-booking-form > p:visible input').val();
        var $select = $("#".concat(field, "-qty"));
        var $numOfOptions;

        if (field === 'byoe') {
            $numOfOptions = $numOfPersons;
        }

        if (field === 'promo') {
            var numOfPromo = +$select.attr('data-passes');
            $numOfOptions = Math.min($numOfPersons, numOfPromo);
        }

        var selectHtml = '<option selected></option>';

        for (var i = 1; i < $numOfOptions + 1; i++) {
            selectHtml += "<option>".concat(i, "</option>");
        }

        $select.html(selectHtml);
    }; // When page initialize


    renderSelectOptions('byoe'); // renderSelectOptions('promo');
    // When persons and/or resource type changes

    $('#wc-bookings-booking-form > p, #sz-resources').on('change', renderSelectOptions.bind(this, 'byoe')); // $('#wc_bookings_field_persons, #wc_bookings_field_resource').on('change', renderSelectOptions.bind(this, 'promo'));
});