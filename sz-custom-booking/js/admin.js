jQuery(document).ready(function ($) {
    $('.sz-admin-checkbox-enable').on('change', function () {
        const $textfield = $(this).closest('.form-field').next('.form-field');
        if (this.checked) {
            $textfield.removeClass('d-none');
        } else {
            $textfield.addClass('d-none');
        }
    });

    const $discountInfoLabel = $('#booking_data .booking_data_column_container .booking_data_column:first-of-type .form-field:nth-of-type(5) label');

    $discountInfoLabel.text('Discount Info: ').attr('class', 'sz-admin-booking-subtitle');

})