/**
 * Created by Денис on 23.12.2016.
 */

$(function () {
    $.material.init();

    $(window).scroll(function(){
        if ($(this).scrollTop() > 100) {
            $('.btn-scroll-up').addClass('btn-gone-down');
        } else {
            $('.btn-scroll-up').removeClass('btn-gone-down');

        }
    });

    $('.btn-scroll-up').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 600);
        return false;
    });
});