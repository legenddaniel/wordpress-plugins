jQuery(document).ready(function ($) {

    $('.search-field').on('change', function () {
        $(this).focus();

        if ($(this).val() == 0) {
            $('.search-field').prop("disabled", true);
        } else {
            $('.search-field').prop("disabled", false);
        }
    })
})