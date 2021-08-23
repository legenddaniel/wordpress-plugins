window.addEventListener('load', function () {
    var container = document.getElementById('bid-max-field');
    if (!container) return;

    var enable = document.getElementById('bid-max-enable');
    if (!enable) return;

    var defaultContainer = document.querySelector('.auction_form > .quantity');

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

    // Sync the min and value of max bid with default bid if max bid is less than default bid
    defaultContainer && defaultContainer.addEventListener('click', function (e) {
        var className = e.target.className;
        var minus = className.indexOf('minus') !== -1,
            plus = className.indexOf('plus') !== -1;
        if (!minus && !plus) return;

        var bid = this.querySelector('.input-text.qty'),
            max = container.querySelector('input[name=bid_max]');

        max.min = minus ? bid.value - bid.step : bid.value - -bid.step;
        if (max.value - max.min < 0) {
            max.value = max.min;
        }
    })
    defaultContainer && defaultContainer.addEventListener('input', function (e) {
        if (e.target.name !== 'bid_value') return;

        var bid = this.querySelector('.input-text.qty'),
            max = container.querySelector('input[name=bid_max]');

        max.min = bid.value;
        if (max.value - max.min < 0) {
            max.value = max.min;
        }
    })

})