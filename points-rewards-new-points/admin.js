jQuery(document).ready(function ($) {
    var $p = $('#woocommerce-order-notes .order_notes > .note.system-note p:contains("Points removed")');

    // Hide the points removed in admin single order page
    $p.parents('.note.system-note').toggle(false);
});