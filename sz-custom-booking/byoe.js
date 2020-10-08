jQuery(document).ready(function($) {
    $('input').on('change', function() {
        console.log('checked');
        var checked = this.value;
        $.ajax({
            type: 'POST',
            url: 'sz-custom-booking.php',
            data: {
                checked
            }
        })
    })
})