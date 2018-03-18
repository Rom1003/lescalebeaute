

$(document).ready(function () {
    $(document).foundation();

    //Resize des blocs
    $("div[data-match]").each(function () {
        console.log($(this));
        var elem = '.' + $(this).data("match");
        $("div[data-match]").matchHeight();
    });

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
