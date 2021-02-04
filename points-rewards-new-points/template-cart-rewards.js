jQuery(document).ready(function ($) {
    $('#cr-clp-switch').on('click', function () {

        // Toggle collapse
        $(this).children('.cr-switch').toggleClass('cr-switch-off cr-switch-on');
        $('#cr-main').toggleClass('cr-active');

        // Remove borders
        $('#cr-head').toggleClass('cr-remove-border');
        $('#cr-main').toggleClass('cr-remove-border');

        // Enforce a resize for slider bug fix
        $(window).trigger('resize');
    });

    $('#cr-main .cr-tabs').on('click', 'li', function () {
        var index = $(this).index(); // All 0-based index
        var className = 'cr-active';
        if ($(this).hasClass(className)) {
            return;
        }

        // Toggle tabs
        $(this).siblings().removeClass(className);
        $(this).addClass(className);

        // Toggle sliders
        $('#cr-sliders > div').not(':eq(' + index + ')').removeClass(className);
        $('#cr-sliders > div').eq(index).addClass(className);

    });

    // Refresh the page after cart update for bug fix
    $('body').on('updated_cart_totals', function() {
        location.reload();
    });
    $('#cr-sliders .add_to_cart_button').on('click', function() {
        location.href = '/cart'; // Will remove winery in production
    })
});