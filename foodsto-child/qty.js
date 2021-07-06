// Debounce function
function debounce(fn, delay) {
    var timer;
    return function () {
        var that = this;
        var args = arguments;

        clearTimeout(timer);

        timer = setTimeout(function () {
            timer = null;
            fn.apply(that, args);
        }, delay);
    };
};

// Set `add to cart` button text on various device widths
function setBtnTxt() {
    if (typeof window === 'undefined' || document.querySelector('body.page-id-4848')) return;

    var addtocart = document.getElementsByClassName('add_to_cart_button');
    var l = addtocart.length;
    if (window.innerWidth < 1200) {
        for (var i = 0; i < l; i++) {
            addtocart[i].innerHTML = '<i class="fa fa-shopping-basket" aria-hidden="true"></i>';
        }
    } else {
        for (var i = 0; i < l; i++) {
            addtocart[i].innerHTML = 'Add to cart';
        }
    }
}

// Filter sidebar toggling
function setFilters() {
    if (typeof window === 'undefined') return;

    var cats = document.getElementsByClassName('yith-wcan-filters')[0];
    if (!cats) return;

    cats.insertAdjacentHTML('beforeend', '<span class="sz-closecats">×</span>');
    document.getElementById('sz-cats').addEventListener('click', function () {
        cats.style.left = 0;
    })
    document.getElementsByClassName('sz-closecats')[0].addEventListener('click', function () {
        cats.style.left = '-200%';
    })
}

window.addEventListener('load', function () {

    // Set the `addtocart` button when window loaded and resized
    setBtnTxt();
    this.addEventListener('resize', debounce(setBtnTxt, 100));

    // Set filter as a sidebar with toggling on mobile
    setFilters();

    // Apply quantity & variation
    var products = document.getElementById('sz-products');
    if (products) {
        products.addEventListener('change', function (e) {
            var target = e.target;
            var value = target.value;

            var regex;
            var button;
            if (target.className === 'sz-qty') {
                regex = /(quantity=)(\d+)/;
                button = target.nextSibling;
            }
            if (target.tagName === 'SELECT') {
                regex = /(variation_id=)(\d+)/;

                var tr = target.parentElement.parentElement;
                button = tr.querySelector('.add_to_cart_button');
                tr.querySelector('.price').innerHTML = target.getAttribute('data-price-' + value);
            }

            var url = button.href.replace(regex, '$1' + value);
            button.setAttribute('href', url);
        });
    }

    // Restore all listeners
    var target = document.getElementsByClassName('site-content-contain')[0];
    if (target) {
        var observer = new MutationObserver(function (mutations, observer) {
            mutations.forEach(function (mutation) {
                setBtnTxt();
                setFilters();
            })
        });
        observer.observe(target, { childList: true });
    }
})
