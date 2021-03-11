jQuery(document).ready(function ($) {

    // For CAD only
    if (document.cookie.indexOf('wmc_current_currency=USD') !== -1) return;

    function replacePrice(element) {
        var $result = $('.product-item-search > .product-link > .product-content');
        if (!$result) return;

        var $price =
            element ?
                $result.children(element).children('.woocommerce-Price-amount') :
                $result.children('.woocommerce-Price-amount');

        if (!$price) return;

        var html = $price.html();
        if (!html) return;

        var matches = html.match(/(<\/span>)(.*)$/);
        if (!matches) return;

        $price.html(html.replace(matches[2], Math.ceil(matches[2])));
    }

    var target = document.querySelector('.ajax-search-result');
    if (!target) return;

    var observer = new MutationObserver(function () {
        if (target.style.display != 'none') {
            // Single price
            replacePrice();

            // Duo prices (sale)
            replacePrice('del');
            replacePrice('ins');
        }
    });
    observer.observe(target, { attributes: true, childList: true });

});