jQuery(document).ready(function ($) {
    var $p = $('#woocommerce-order-notes .order-notes > .note.system-note p:contains("Points removed")');
    console.log('aa');

    $p.parents('.note.system-note').toggle(false);
})