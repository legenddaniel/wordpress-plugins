jQuery(document).ready(function ($) {
    var $resourcesHtml = `
        <label for="sz-resources">Type:</label>
        <select id="sz-resources" name="sz-resources">
            <option value="70541" selected>Archery</option>
            <option value="70542">Airsoft</option>
            <option value="70543">Combo</option>
        </select>
    `;

    $('fieldset.wc-bookings-date-picker').before($resourcesHtml);

    var $persons = $('#wc-bookings-booking-form > p');

    $persons.slice(1, 3).toggle(false);

    $('#sz-resources').on('change', function () {
        var that = this;
        $persons.each(function () {
            $(this).children('input').val(0);
            $(this).toggle($(this).hasClass(`wc_bookings_field_persons_${$(that).val()}`));
        });
    })
})