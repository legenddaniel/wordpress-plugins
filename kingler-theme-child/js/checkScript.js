jQuery(document).ready(function ($) {
      $('[name="promo-enable"]').on('change', function () {
            const isChecked = this.checked;
            if (!isChecked) {
                  $(this).closest('.sz-discount-field').find('select[name="promo-qty"]').val('');
            }
            $(".txtAge").toggle(isChecked);
      });
})
