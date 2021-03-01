jQuery(document).ready(function ($) {

    $(document).ajaxComplete(function (event, xhr, settings) {

        var resJSON = xhr.responseJSON;
        if (!resJSON) return;

        var data = resJSON.data;
        if (!data) return;

        var success = resJSON.success;
        if (!success) return;

        var cookie = data.cookie;
        if (!cookie || cookie.type !== 'subscription') return;

        var name = cookie.name;
        var value = cookie.value;
        var expire = cookie.expire;

        SGPBPopup.setCookie(name, value, expire);

    });

});