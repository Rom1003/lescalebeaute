

$(document).ready(function () {
    $(document).foundation();

    //Resize des blocs
    $("div[data-match]").each(function () {
        var elem = '.' + $(this).data("match");
        $("div[data-match]").matchHeight();
    });


    /* Gestion du menu */
    //Menu de base
    $('.menu-dropdown').on('mouseenter', function () {
        var submenu = $(this).find('.sub-menu');

        if (submenu.is(':hidden')) {
            $('.menu-dropdown').find(".sub-menu:visible").hide();
            submenu.slideDown('hide');
        }
    }).on('mouseleave', function () {
         $('.sub-menu').slideUp();
    });
    //Sous-menu
    $('.sub-dropdown').on('mouseenter', function () {
        var submenu = $(this).find('.sub-menu-2');

        if (submenu.is(':hidden')) {
            $('.menu-dropdown').find(".sub-menu-2:visible").hide();
            submenu.show();
        }
    });

    /****************/

    //Egaliser la hauteur des sous-menu
    $(function() {
        $('.sub-menu-2, .sub-menu').matchHeight();
    });
    $.fn.matchHeight._afterUpdate = function(event, groups) {
        $('.sub-menu').each(function () {
            $(this).hide()
        })
    };

    $('.mask-phone').mask('00 00 00 00 00');

    pageLoader(0);
});

function pageLoader(sec) {
    sec = sec || 1000;
    elem = $('.page-loader');

    //Faire disparaitre le loader avec un effet de fadeOut après x secondes
    setTimeout(function () {
        elem.fadeOut("slow");
        //Supprimer la div
        setTimeout(function () {
            elem.remove();
        }, 3000);
    }, sec);
}
