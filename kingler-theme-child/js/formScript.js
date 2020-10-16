jQuery(document).ready(function($) {

  $('#submit').click(function( event ) {
  event.preventDefault();

var username = $("#cars").val();

 console.log(username );


  $( "<div>" )
    .append(username )
    .appendTo( "#log" );
});   



   });
