jQuery(document).ready(function ($) {
    $('.widget_shopping_cart .hide_cart_widget_if_empty').on('click', '.remove_from_cart_button', function (e) {
        
        // Check if item has discount info
        var $itemInfo = $(e.target).siblings('.variation');
        var $itemWithDiscount = $itemInfo.children('dd.variation-DiscountInfo');

        if (!$itemWithDiscount) {
            return;
        }

        // Check if item used Promo/VIP
        var text = $itemWithDiscount.children('p').text();
        var match = text.match(/(Use Promo|Use VIP).*\n.*\* (\d)$/g)[0];

        if (!match) {
            return;
        }

        var discount = match.match(/Use (Promo|VIP)\n*/)[1]; 
        
        // Check if an immediate change required. Not required if remove an item with Use Promo and not the current resource being viewed
        var resource = $itemInfo.children('dt:last-of-type').text();
        var currentResource = $('#sz-resources option:selected').text();

        if (resource.indexOf(currentResource) < 0 && discount.indexOf('VIP') < 0) {
            return;
        }

        var qty = match.match(/\d+$/)[0]; 
        
        // Replace current Use Promo label
        var $promoEnable = $('#promo-enable');
        var label = $promoEnable.next('label').text();
        var labelEnd = label.match(/\(.*being deducted in the cart\)$/)[0];
        var discountText = labelEnd.match(new RegExp('\\d+ ' + discount, 'g'));
        var newDiscountText = parseInt(discountText) - qty + ' ' + discount;
        var newLabelEnd = labelEnd.replace(discountText, newDiscountText); 
        
        // If no discount in the cart remove all cart info
        if (!+newLabelEnd.match(/\d+/g).join('')) {
            label.replace(newLabelEnd, '');
        }

        var newLabel = label.replace(labelEnd, newLabelEnd);
        $promoEnable.prop('disabled', false);
        $promoEnable.next('label').text(newLabel); 
        
        // change data-passes even useless for now
        var dataPasses = $promoEnable.attr('data-passes');
        $promoEnable.attr('data-passes', dataPasses + qty);
    });
});