jQuery(document).ready(function ($) {
	$('.singlePassInput').on('input', function () {
		var val = this.value;
		if (val.length > this.maxLength) {
			val = this.value.slice(0, this.maxLength);
		}
		$(this).val(val);
		
		var href = $(this).next('.addToCart').attr('href').replace(/\d+$/, '');
		$(this).next('.addToCart').attr('href', href + val);
	})

});

