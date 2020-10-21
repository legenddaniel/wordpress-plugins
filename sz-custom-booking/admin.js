jQuery(document).ready($ => {

    /**
     * @desc Toggle display of custom field
     * @param {object} jqObj - Field (jQuery Node Object)
     * @return {undefined}
     */
    const toggleDisplay = () => {
        const $checkboxes = $('.sz-admin-byoe-checkbox');
        const $textfield = $checkboxes.closest('td').find('.sz-admin-byoe-input');
        if ($checkboxes.is(':checked')) {
            $textfield.removeClass('d-none');
        } else {
            $textfield.addClass('d-none');
        }
    };

    // Check once page initialized
    toggleDisplay();

    // Check once checkbox status changes
    $BYOECheckboxes.on('change', toggleDisplay);
})