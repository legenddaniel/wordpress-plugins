window.addEventListener('load', function () {
    var field = document.getElementById('bid-max-field');
    if (!field) return;

    var max = field.querySelector('.input-text.qty');
    field.addEventListener('click', function (e) {
        if (e.target.value === '+' && (max.max === '' || +max.value < +max.max)) {
            max.value++;
        }
        if (e.target.value === '-' && +max.value > +max.min) {
            max.value--;
        }
    })
})