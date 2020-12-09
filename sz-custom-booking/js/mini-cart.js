jQuery(document).ready(function ($) {
    $('.widget_shopping_cart .hide_cart_widget_if_empty').on('click', '.remove_from_cart_button', function (e) {

        // Check if item has discount info
        var $itemInfo = $(e.target).siblings('.variation');
        var $itemWithDiscount = $itemInfo.children('dd.variation-DiscountInfo');

        if (!$itemWithDiscount) {
            return;
        }

        // Check if item used Promo/VIP/Guest
        var text = $itemWithDiscount.children('p').text();
        var matches = text.match(/Use Promo|Use VIP|Pay For Guests/g);

        if (!matches) {
            return;
        }

        var resource = $itemInfo.children('dt:last-of-type').text();
        var currentResource = $('#sz-resources option:selected').text();

        for (let i of matches) {
            var discount;
            switch (i) {
                case 'Use Promo':
                    discount = 'Promo';
                    break;
                case 'Use VIP':
                    discount = 'VIP';
                    break;
                case 'Pay For Guests':
                    discount = 'Guest Pass';
                    break;
            }

            // Check if an immediate change required. Not required if remove an item with Use Promo and not the current resource being viewed
            if (discount === 'Promo' && resource.indexOf(currentResource) < 0) {
                return;
            }

            var qty = text.match(new RegExp(i + '.*\\n.*\\* (\\d+)'))[1];

            // Replace current Use Promo / Pay For Guests label
            var field;
            switch (discount) {
                case 'Promo':
                    field = 'promo';
                    break;
                case 'VIP':
                    field = 'promo';
                    break;
                case 'Guest Pass':
                    field = 'guest';
                    break;
            }
            var $discountEnable = $("#".concat(field, "-enable"));
            var label = $discountEnable.next('label').text();

            // 2 Guest Passes | 1 Promo, 1 VIP | ...
            var discountTexts = label.match(/\) \((.*)being deducted in the cart\)$/)[1];

            // 1 Promo | 2 VIP | 3 Guest Passes | 1 Guest Pass | ...
            var discountText = discountTexts.match(new RegExp('\\d+ ' + discount + '(es)*'))[0];
            var newQty = parseInt(discountText) - qty;

            // 1 Promo | 2 VIP | 3 Guest Passes | 1 Guest Pass | ...
            var newDiscountText = newQty + ' ' + discount + (field === 'guest' && newQty > 1 ? 'es' : '');

            // 1 Guest Pass | 1 Promo, 0 VIP | ...
            var newDiscountTexts = discountTexts.replace(discountText, newDiscountText);

            // If no discount in the cart remove all cart info
            var newLabel = +newDiscountTexts.match(/\d+/g).join('') ?
                label.replace(discountTexts, newDiscountTexts) :
                label.match(/.*\(\d+ left(.*VIP discount)*\)/)[0];

            $discountEnable.prop('disabled', false);
            $discountEnable.next('label').text(newLabel);

            // Change data attribute (# of discount)
            var dataPasses = $discountEnable.attr("data-".concat(field));
            $discountEnable.attr("data-".concat(field), dataPasses + qty);
        }
    });
});