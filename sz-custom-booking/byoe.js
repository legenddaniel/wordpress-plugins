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
    $('#wc-bookings-booking-form:last-child').attr('id', 'booking-cost');

    // Display different checkboxes for different types
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


})