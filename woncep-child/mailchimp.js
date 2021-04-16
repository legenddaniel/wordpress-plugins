window.addEventListener('load', function () {
    var iframe = document.querySelector('.mc-modal iframe');

    new MutationObserver(function (mutationsList, observer) {
        console.log(iframe);

        var mark = iframe.contentWindow.document.querySelector('.content__monkeyRewards');
        console.log(mark);
        mark.style.display = 'none';
    }).observe(document.querySelector('.mc-modal'), { attributes: true, childList: true, subtree: true });

})

