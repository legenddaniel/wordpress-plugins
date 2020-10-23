jQuery(document).ready(function($) {
    $('#wc_bookings_field_resource').on('change', function() {
 	var selected = $('#wc_bookings_field_resource').val();
 	console.log(selected);

   $("#log").empty().append(selected);


});



});
