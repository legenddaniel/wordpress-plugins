jQuery(document).ready(function ($) {

    var $miniCart = $('.widget_shopping_cart .hide_cart_widget_if_empty');
    $miniCart.on('mousedown', '.remove_from_cart_button', function (e) {

        // Check if item has discount info
        var $itemInfo = $(e.target).siblings('.variation');
        var $itemWithDiscount = $itemInfo.children('dd.variation-DiscountInfo');
        if (!$itemWithDiscount) {
            return;
        }

        // Check if item used Promo/VIP
        var text = $itemWithDiscount.children('p').text();
        var match = text.match(/(Use Promo|Use VIP).*\n.*\* (\d)$/g);
        if (!match) {
            return
        }

        // Check if an immediate change required
        var resource = $itemInfo.children('dt:last-of-type').text();
        var currentResource = $('#sz-resources option:selected').text();
        if (resource !== currentResource) {
            return;
        }

        var discount = match[1], qty = match[2];

        // Replace current Use Promo label
        var $promoEnable = $('#promo-enable');
        var label = $promoEnable.next('label').text();
        discount = discount.replace('Use ', '');
        label = label.replace(new RegExp(`${qty} ${discount}`), `${qty - 1} ${discount}`);

        // If no certain discount in the cart remove the string below
        var labelEnd = label.match(/\(.*being deducted in the cart\)$/)[0];
        if (!(+labelEnd.match(/\d+/g).join(''))) {
            label.replace(labelEnd, '');
        }

        // change data-passes even useless for now


    })
    /*
        var config = {
            childList: true
        };
    
        var mutationObserver = new MutationObserver(function (mutations, observer) {
            mutations.forEach(function (mutation) {
    
                var addedNodes = mutation.addedNodes;
    
                if (addedNodes.className !== 'widget_shopping_cart_content') {
                    continue;
                }
    
                addedNodes[0].getElementsByClassName('remove_from_cart_button').addEventListener('click', function () {
    
                })
    
                // targetNode.className = mutation.addedNodes.length === 2 ? 'sz-discount-field' : 'sz-discount-field d-none';
                // return;
    
            });
        });
        mutationObserver.observe($miniCart, config);*/
});