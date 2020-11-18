jQuery(document).ready(function ($) {

    // Only in product editing page
    if (!/post\.php\?post=\d+&action=edit/.test(window.location.href)) {
        return;
    }

    /**
     * @desc Toggle discount field in admin product editing
     * @param {interface} e - Event
     * @return {undefined}
     */
    const toggleDiscountField = function (e) {
        const that = e.currentTarget;
        const $textfield = $(that).closest('.form-field').next('.form-field');
        if (that.checked) {
            $textfield.removeClass('d-none');
        } else {
            $textfield.addClass('d-none');
        }
    }

    // Toggle the discount field in product editing page
    $('.sz-admin-checkbox-enable').on('change', toggleDiscountField);

    // Change the display and styling of discount field in booking details page
    const $discountInfoLabel = $('#booking_data .booking_data_column_container .booking_data_column:first-of-type .form-field:nth-of-type(5) label');

    $discountInfoLabel.text('Discount Info: ').attr('class', 'sz-admin-booking-subtitle');
})