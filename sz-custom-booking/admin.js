jQuery(document).ready(function($) {

    /**
     * @desc Toggle display of custom field
     * @param {object} jqObj - Field (jQuery Node Object)
     * @return {undefined}
     */
    /*
    const toggleDisplay = () => {
        const $checkboxes = $('.sz-admin-byoe-checkbox');
        const $textfield = $checkboxes.closest('td').find('.sz-admin-byoe-input');
        $textfield.toggle($checkboxes.is(':checked'));
    };

    // Check once page initialized
    toggleDisplay();*/

    // Check once checkbox status changes
    $('.sz-admin-byoe-checkbox').on('change', function() {
        const $textfield = $(this).closest('.form-field').next('.form-field');
        $textfield.toggle(this.checked);
    });
})