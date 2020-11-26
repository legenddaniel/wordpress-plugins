jQuery(document).ready(function ($) {
    var $resourcesHtml = `
        <label for="sz-resources">Type:</label>
        <select id="sz-resources" name="sz-resources" class="form-field form-field-wide" data-persons="1">
            <option value="70541" selected>Archery</option>
            <option value="70542">Airsoft</option>
            <option value="70543">Combo</option>
        </select>
    `;

    // Add custom resource select
    $('fieldset.wc-bookings-date-picker').before($resourcesHtml);

    var $persons = $('#wc-bookings-booking-form > p');
    var $resources = $('#sz-resources');

    // Set label text of persons to 'Persons'
    $persons.children('label').text('Persons:');

    // Set Archery persons to 1 and hide other inputs
    $persons.eq(0).children('input').val($resources.attr('data-persons'));
    $persons.slice(1, 3).toggle(false);

    // Store the current persons
    $persons.on('change', function () {
        $resources.attr('data-persons', $(this).children('input').val());
    });

    // Reset all persons according to their visibility and display the corresponding resource input
    $resources.on('change', function () {
        var that = this;
        $persons.each(function () {
            $(this).toggle($(this).hasClass(`wc_bookings_field_persons_${$(that).val()}`));
            var $person = $(this).is(':visible') ? $(that).attr('data-persons') : 0;
            $(this).children('input').val($person);
        });
    });
})