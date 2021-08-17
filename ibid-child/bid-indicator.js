window.addEventListener('load', function () {
    var field = document.getElementById('bid-max-field');
    if (!field) return;

    var max = field.querySelector('.input-text.qty');
    var enable = document.getElementById('max-bid-enable');
    field.addEventListener('click', function (e) {
        if (enable.checked) {
            if (e.target.value === '+' && (max.max === '' || +max.value < +max.max)) {
                max.value++;
            }
            if (e.target.value === '-' && +max.value > +max.min) {
                max.value--;
            }
        }
    })

    enable.addEventListener('change', function (e) {
        if (e.currentTarget.checked) {
            max.value = max.min;
            max.disabled = false;
            field.classList.remove('disabled');
        } else {
            max.value = '';
            max.disabled = true;
            field.classList.add('disabled');
        }
    })
})