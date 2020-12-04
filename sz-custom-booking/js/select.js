jQuery(document).ready(function ($) {
    /**
     * @desc Render select options based on the number of persons and/or passes remaining
     * @param {string} field - 'byoe' or 'promo'
     * @return {undefined}
     */
    var renderSelectOptions = function renderSelectOptions(field) {
        var $numOfPersons = +$('#wc-bookings-booking-form > p:visible input').val() || 1;
        var $select = $("#".concat(field, "-qty"));
        var $numOfOptions;

        switch (field) {
            case 'byoe':
                $numOfOptions = $numOfPersons;
                break;
            default:
                var numOfDiscount = +$("#".concat(field, "-enable")).attr("data-".concat(field));
                $numOfOptions = Math.min($numOfPersons, numOfDiscount);
                break;
        }

        var selectHtml = '<option selected></option>';
        for (var i = 1; i < $numOfOptions + 1; i++) {
            selectHtml += "<option>".concat(i, "</option>");
        }
        $select.html(selectHtml);
    };

    // For now only byoe and guest
    renderSelectOptions('byoe');
    renderSelectOptions('guest');

    $('#wc-bookings-booking-form > p, #sz-resources').on('change', renderSelectOptions.bind(this, 'byoe'));
    $('#wc-bookings-booking-form > p, #sz-resources').on('change', renderSelectOptions.bind(this, 'guest'));

    // Toggle select field
    $('#sz-discount-field').on('change', 'input[name$="-enable"]', function (e) {
        $(e.target).closest('div').next('.sz-select-field').toggle(e.target.checked);
    })
});