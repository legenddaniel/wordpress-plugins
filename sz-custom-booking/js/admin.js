jQuery(document).ready(function ($) {
    $('.sz-admin-byoe-enable').on('change', function () {
        const $textfield = $(this).closest('.form-field').next('.form-field');
        if (this.checked) {
            $textfield.removeClass('d-none');
        } else {
            $textfield.addClass('d-none');
        }
    });
})