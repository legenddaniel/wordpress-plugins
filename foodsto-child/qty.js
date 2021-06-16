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

function setBtnTxt() {
    if (typeof window === 'undefined') return;

    var addtocart = document.getElementsByClassName('add_to_cart_button');
    var l = addtocart.length;
    if (window.innerWidth < 576) {
        for (var i = 0; i < l; i++) {
            addtocart[i].innerHTML = '<i class="fa fa-shopping-basket" aria-hidden="true"></i>';
        }
    } else {
        for (var i = 0; i < l; i++) {
            addtocart[i].innerHTML = 'Add to cart';
        }
    }
}

function setFilters() {
    if (typeof window === 'undefined') return;

    var cats = document.getElementsByClassName('yith-wcan-filters')[0];
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

    // Apply quantity
    document.getElementById('sz-products').addEventListener('change', function (e) {
        var target = e.target;
        if (target.className === 'sz-qty') {
            target.nextSibling.setAttribute('data-quantity', target.value);
        }
    });

    // Restore all listeners
    var observer = new MutationObserver(function (mutations, observer) {
        mutations.forEach(function (mutation) {
            setBtnTxt();
            setFilters();
        })
    });
    observer.observe(document.getElementsByClassName('site-content-contain')[0], { childList: true });

})
