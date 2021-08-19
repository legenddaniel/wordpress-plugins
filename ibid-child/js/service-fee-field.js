window.addEventListener('load', function () {

    var type = document.getElementById('_auction_service_fee_delegation_buyer_type');
    var max = document.querySelector('._auction_service_fee_delegation_buyer_max_field');
    if (!type || !max) return;

    max.style.display = type.value === 'percentage_max' ? 'block' : 'none';

    type.addEventListener('change', function () {
        max.style.display = this.value === 'percentage_max' ? 'block' : 'none';
    })

})