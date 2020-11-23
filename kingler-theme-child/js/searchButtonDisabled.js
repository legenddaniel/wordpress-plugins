jQuery(document).ready(function($){

 $('.search-field').on('change', function (){ 

var searchstring = $('.search-field');
searchstring.focus();


if (searchstring.val() == 0 ) {

$('.search-field').prop( "disabled", true );
}

else 

$('.search-field').prop( "disabled", false );
})
})