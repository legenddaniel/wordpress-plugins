window.addEventListener('load', function () {
    document.getElementById('sz-products').addEventListener('change', function (e) {
        var target = e.target;
        if (target.className === 'sz-qty') {
            target.nextSibling.setAttribute('data-quantity', target.value);
        }
    })
})
