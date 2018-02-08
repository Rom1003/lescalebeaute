$(document).ready(function () {
    $(document).foundation();
    //TODO mettre le delai
//    pageLoader(1000);
})

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
