window.addEventListener('load', function () {
    var form = document.querySelector('.auction_form');
    if (!form) return;

    var bid = form.querySelector('input[name=bid_value]'),
        max = form.querySelector('input[name=bid_max]'),
        increment = form.querySelector('input[name=bid_max_increment]');

    bid.required = true;

    form.addEventListener('change', function (e) {
        var target = e.target;

        // Toggle max field
        if (target.name === 'bid-max-enable') {
            var checked = target.checked;
            var container = document.getElementById('bid-max-field');
            var inputs = container.getElementsByClassName('input-text qty');

            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = checked ? inputs[i].min : '';
                inputs[i].disabled = !checked;
                inputs[i].required = checked;
            }

            if (checked) {
                container.classList.remove('hide');
            } else {
                container.classList.add('hide');
            }
        }
    })

    form.addEventListener('click', function (e) {
        var target = e.target;
        var parent = target.parentElement;
        var grandParentClass = parent.parentElement.className,
            thisClass = target.className;

        // Max bid and increment changing functionality
        if (grandParentClass.indexOf('bid-max-field') !== -1) {
            if (thisClass.indexOf('minus') !== -1) {
                var qty = parent.querySelector('.qty');
                qty.value > qty.min && qty.value--;
            }
            if (thisClass.indexOf('plus') !== -1) {
                var qty = parent.querySelector('.qty');
                qty.value++;
            }
        }

        // Merge onclick and oninput together in the next handler
        if (thisClass.indexOf('minus') !== -1 || thisClass.indexOf('plus') !== -1) {
            if (grandParentClass.indexOf('auction_form') !== -1) {
                setTimeout(function () {
                    bid.dispatchEvent(new InputEvent('input', {
                        view: window,
                        bubbles: true,
                        cancelable: true
                    }))
                });
            }
            // Since it is difficult to select element, we add the event listener to `bid_max`, too, but nothing would ever happen
            if (grandParentClass.indexOf('bid-max-field') !== -1) {
                setTimeout(function () {
                    increment.dispatchEvent(new InputEvent('input', {
                        view: window,
                        bubbles: true,
                        cancelable: true
                    }))
                });
            }
        }
    })

    form.addEventListener('input', function (e) {
        if (['bid_value', 'bid_max_increment'].indexOf(e.target.name) !== -1) {
            // Max bid min should always equals default bid + increment
            if (bid.value != max.min - increment.value) {
                max.min = bid.value - -increment.value;
            }
            // Max bid should not be less than default bid + increment
            if (max.value - bid.value - increment.value < 0) {
                max.value = bid.value - -increment.value;
            }
        }
    })
})