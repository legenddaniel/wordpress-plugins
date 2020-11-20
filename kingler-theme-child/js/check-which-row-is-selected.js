jQuery(document).ready(function($) {
$("#addArchery").click(function() {

	var str = $("#quantityArchery").val();
	if(str !== 1) {
		$('#addArchery').attr('href','https://solelyana.com/?add-to-cart=70057' + '&quantity=' + str);
}

});

$("#addAirsoft").click(function() {

	var str = $("#quantityAirsoft").val();
	if(str !== 1) {
		$('#addAirsoft').attr('href','https://solelyana.com/?add-to-cart=70055' + '&quantity=' + str);
}
});


$("#addCombo").click(function() {

	var str = $("#quantityCombo").val();
	if(str !== 1) {
		$('#addCombo').attr('href','https://solelyana.com/?add-to-cart=70056' + '&quantity=' + str);

}

});

});

