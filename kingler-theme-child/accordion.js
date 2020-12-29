jQuery(document).ready(function ($) {
    $('.sz-discount-menu .sz-accordion-active').on('click', '.vc_tta-panel-heading', function (e) {
        $(e.delegateTarget).removeClass('sz-accordion-active');
    })
})

