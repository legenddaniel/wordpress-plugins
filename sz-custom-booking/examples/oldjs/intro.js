jQuery(document).ready(function ($) {

    // Display login modal
    $('.a-question').on('click', function (e) {
        if (e.currentTarget.href) {
            return;
        }

        $('.menu_user_login .popup_login_link').trigger('click');
    })
})