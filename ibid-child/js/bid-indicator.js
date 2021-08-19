window.addEventListener('load', function () {
    var container = document.getElementById('bid-max-field');
    if (!container) return;

    var enable = document.getElementById('bid-max-enable');
    if (!enable) return;

    // Max bid indicator
    container.addEventListener('click', function (e) {
        if (!enable.checked) return;

        var target = e.target;
        if (['+', '-'].indexOf(target.value) === -1) return;

        var realTarget = target.parentElement.querySelector('.input-text.qty');
        if (!realTarget) return;

        if (target.value === '+' && (realTarget.max === '' || +realTarget.value < +realTarget.max)) {
            realTarget.value++;
        }
        if (target.value === '-' && +realTarget.value > +realTarget.min) {
            realTarget.value--;
        }
    })

    // Toggle max field
    enable.addEventListener('change', function () {
        var inputs = container.getElementsByClassName('input-text qty');
        var l = inputs.length;
        if (!l) return;

        for (var i = 0; i < l; i++) {
            inputs[i].value = this.checked ? inputs[i].min : '';
            inputs[i].disabled = !this.checked;
        }
        if (this.checked) {
            container.classList.remove('hide');
        } else {
            container.classList.add('hide');
        }
    })
})