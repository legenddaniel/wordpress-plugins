// Checkbox area before add_to_cart button layout and functionalities
jQuery(document).ready(function ($) {

    /**
     * @desc Calculate discount for byoe and promo
     * @param {string} field - 'byoe' or 'promo'
     * @return {number} - Discount
     */
    var calculateDiscount = function calculateDiscount(field) {
        var $input = $("#".concat(field, "-enable"));
        var $select = $("#".concat(field, "-qty"));
        var $price = $input.length ? $input.attr('data-price') : 0;
        var $qty = $select.length ? $select.val() : 1;
        var $discount = $input.is(':checked') && $price * $qty;
        return $discount;
    };

    /**
     * @desc Recalculate and display cost
     * @return {undefined}
     */
    var toggleDisplayByOption = function toggleDisplayByOption() {
        var bdiHtml = '<span class="woocommerce-Price-currencySymbol">$</span>';
        var $qty = $('#wc-bookings-booking-form > p:visible input').val();
        var $price = $('#sz-discount-field').attr('data-price'); // Original base cost

        var discount = 0;

        for (var _i = 0, _arr = ['byoe', 'promo', 'guest']; _i < _arr.length; _i++) {
            var i = _arr[_i];
            discount += calculateDiscount(i);
        }

        var total = Math.max($qty * $price - discount, 0).toFixed(2);
        bdiHtml += total;
        $('#booking-cost bdi').html(bdiHtml);
    };

    /**
     * @desc Display checkboxes based on stock
     * @return {undefined}
     */
    var toggleDisplayByStock = function toggleDisplayByStock() {
        try {
            var observedNode = document.getElementById('booking-cost');
            var targetNode = document.getElementById('sz-discount-field');
            var checkboxes = document.querySelectorAll('#sz-discount-field input[name$="-enable"]');
            var selects = document.querySelectorAll('#sz-discount-field input[name$="-qty"]');
            var selectFields = document.querySelectorAll('.sz-select-field');
            var config = {
                attributes: true,
                childList: true
            };
            var mutationObserver = new MutationObserver(function (mutations, observer) {
                mutations.forEach(function (mutation) {
                    // Uncheck all discount options when booking changes, except for VIP
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                    });

                    // Reset all select values to the initial
                    selects.forEach(function (select) {
                        select.value = '';
                    });

                    // Hide all select fields
                    selectFields.forEach(function (selectField) {
                        selectField.style.display = 'none';
                    });

                    // Observe booking cost div display change
                    if (mutation.type === 'attributes') {
                        targetNode.className = mutation.target.style.getPropertyValue('display') === 'none' ? 'sz-discount-field d-none' : 'sz-discount-field';
                        return;
                    }

                    // Observe booking validity
                    if (mutation.type === 'childList') {
                        targetNode.className = mutation.addedNodes.length === 2 ? 'sz-discount-field' : 'sz-discount-field d-none';
                        return;
                    }
                });
            });
            mutationObserver.observe(observedNode, config);
        } catch (error) {
            console.log(error);
        }
    }; // Add an anchor to the booking cost div as a ref


    $('.wc-bookings-booking-cost').eq(0).attr('id', 'booking-cost'); // Change price displayed according the discount options

    $('input[name$="-enable"], select[name$="-qty"]').on('change', toggleDisplayByOption);

    toggleDisplayByStock();
});