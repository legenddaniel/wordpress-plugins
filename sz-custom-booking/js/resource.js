jQuery(document).ready(function ($) {

    // var types = [2997, 2998, 2999]; // For test
    // var types = [70541, 70542, 70543]; // For real

    var $persons = $('#wc-bookings-booking-form > p');

    // Find types from the persons class names
    var types = [];
    $persons.each(function() {
        var type = $(this).attr('class').match(/\d+/)[0];
        types.push(type);
    })
    
    var $resourcesHtml = `
        <label for="sz-resources">Type:</label>
        <select id="sz-resources" name="sz-resources" class="form-field form-field-wide" data-persons="1">
            <option value="${types[0]}" selected>Archery</option>
            <option value="${types[1]}">Airsoft</option>
            <option value="${types[2]}">Combo</option>
        </select>
    `;

    // Add custom resource select
    $('fieldset.wc-bookings-date-picker').before($resourcesHtml);

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