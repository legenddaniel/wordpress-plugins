jQuery(document).ready(function($){


$('[name="promo-enable"]').change(function()
      {
            $(".txtAge").toggle( this.checked );

      });
})
  