jQuery(document).ready(function ($) {
    var parent = document.querySelector('.page-id-9311 .elementor-background-video-container');
    var sibling = document.querySelector('.page-id-9311 .elementor-html5-video');

    var video = sibling.cloneNode();

    sibling.style.display = 'none';

    video.style.backgroundColor = '#000';

    var attrs = [
        ['x5-video-player-type', 'h5'],
        ['x5-playsinline', ''],
        ['webkit-inline', ''],
        ['webkit-playsinline', ''],
        ['x5-video-player-fullscreen', ''],
        ['preload', 'auto']
    ];

    attrs.forEach(function (attr) {
        video.setAttribute(attr[0], attr[1]);
    });

    parent.appendChild(video);

    console.log(video);


    // var video = document.createElement('video');
    // var $video = $(video);

    // $video.css('background-color', '#000');

    // $video.attr('preload', '');
    // // $video.attr('x5-video-player-type', 'h5');
    // $video.attr('x5-video-player-type', 'h5-page');
    // $video.attr('x5-video-player-fullscreen', '');
    // $video.attr('loop', '');
    // $video.attr('autoplay', '');
    // $video.attr('playsinline', '');
    // $video.attr('x5-playsinline', '');
    // $video.attr('webkit-inline', '');
    // $video.attr('webkit-playsinline', '');
    // $video.attr('x-webkit-airplay', '');
    // $video.attr('src', 'https://www.youtube.com/watch?v=0yZcDeVsj_Y&ab_channel=AdamEschborn');


    // parent.appendChild(video);

    document.addEventListener("WeixinJSBridgeReady", function () {
        video.play();
    });

    // $(document).on("WeixinJSBridgeReady", function () {
    //     $video.play();
    // }, false);

});