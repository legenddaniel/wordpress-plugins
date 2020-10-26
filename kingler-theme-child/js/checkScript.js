jQuery(document).ready(function ($) {
      $('[name="byoe-enable"]').on('change', function () {
            const isChecked = this.checked;
            if (!isChecked) {
                  $(this).closest('.sz-discount-field').find('select[name="byoe-qty"]').val('');
            }
            $('.sz-select-field').toggle(isChecked);  
      })

      // $('[name="promo-enable"]').on('change', function () {
      //       const isChecked = this.checked;
      //       if (!isChecked) {
      //             $(this).closest('.sz-discount-field').find('select[name="promo-qty"]').val('');
      //       }
      //       $(".txtAge").toggle(isChecked);
      // });
})
