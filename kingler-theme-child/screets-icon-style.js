// Add screets live chat icon style
document.addEventListener("DOMContentLoaded", function () {
    var config = {
        childList: true
    };
    var observedNode = document.body;
    var mutationObserver = new MutationObserver(function (mutations, observer) {
        mutations.forEach(function (mutation) {
            var id = mutation.addedNodes.length && mutation.addedNodes[0].id;
            if (id === 'lcx-widget') {
                var iframe = document.querySelector('#lcx-widget iframe');
                iframe.addEventListener('load', function () {
                    var debounce = function debounce(fn, delay, immediate) {
                        var timer;
                        return function () {
                            var that = this;
                            var args = arguments;
                            clearTimeout(timer);
                            if (immediate && !timer) fn.apply(that, args);
                            timer = setTimeout(function () {
                                timer = null;
                                fn.apply(that, args);
                            }, delay);
                        };
                    };

                    var doc = this.contentDocument;
                    var icon = doc.getElementById('lcx-starter');
                    var style = icon.style;

                    var checkWidth = function checkWidth () {                    
                        if (this.innerWidth >= 1136) {
                            icon.removeAttribute('style');
                        }

                        if (this.innerWidth < 1136 && this.innerWidth >= 768) {
                            style.width = '56px';
                            style.height = '56px';
                        }

                        if (this.innerWidth < 768) {
                            style.width = '51px';
                            style.height = '51px';
                        }
                    };

                    checkWidth();
                    window.addEventListener('resize', debounce(checkWidth, 1000));
                });
                observer.disconnect();
            }
        });
    });
    mutationObserver.observe(observedNode, config);
});